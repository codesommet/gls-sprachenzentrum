<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

use App\Models\BlogPost;
use App\Models\Studienkolleg;

class GenerateSitemap extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'gls:generate-sitemap {--with-quiz : Inclure /discover-your-level/quiz (facultatif)}';

    /**
     * The console command description.
     */
    protected $description = 'Génère le sitemap.xml du site GLS (routes front + contenus dynamiques)';

    public function handle(): int
    {
        $baseUrl = rtrim(config('app.url'), '/');

        if (empty($baseUrl)) {
            $this->error("APP_URL est vide. Mets APP_URL=https://gls-sprachzentrum.ma dans ton .env");
            return self::FAILURE;
        }

        $this->info("Generating sitemap for: {$baseUrl}");

        $sitemap = Sitemap::create();

        /*
        |----------------------------------------------------------------------
        | Static GET routes (from your routes file)
        |----------------------------------------------------------------------
        */
        $staticPaths = [
            '/',
            '/about',
            '/faq',
            '/contact',
            '/intensive-courses',
            '/online-courses',
            '/pricing',

            '/exams/gls',
            '/exams/osd',
            '/exams/goethe',

            '/blog',
            '/student-stories',
            '/certificate-check',

            '/niveaux/a1',
            '/niveaux/a2',
            '/niveaux/b1',
            '/niveaux/b2',

            '/studienkollegs',
            '/discover-your-level',

            '/terms',
            '/privacy',
            '/partners/fc-marokko',
        ];

        foreach ($staticPaths as $path) {
            $sitemap->add(
                Url::create($baseUrl . $path)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                    ->setPriority($path === '/' ? 1.0 : 0.7)
            );
        }

        // Optional: quiz page (dynamic, not cached)
        if ($this->option('with-quiz')) {
            $sitemap->add(
                Url::create($baseUrl . '/discover-your-level/quiz')
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                    ->setPriority(0.6)
            );
        }

        /*
        |----------------------------------------------------------------------
        | Blog posts (dynamic)
        |----------------------------------------------------------------------
        */
        if (class_exists(BlogPost::class)) {
            BlogPost::query()
                ->whereNotNull('slug')
                ->where('slug', '!=', '')
                ->latest()
                ->get(['slug', 'updated_at'])
                ->each(function ($post) use ($sitemap, $baseUrl) {
                    $url = $baseUrl . '/blog/' . ltrim($post->slug, '/');

                    $tag = Url::create($url)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                        ->setPriority(0.6);

                    if (!empty($post->updated_at)) {
                        $tag->setLastModificationDate(Carbon::parse($post->updated_at));
                    }

                    $sitemap->add($tag);
                });
        }

        /*
        |----------------------------------------------------------------------
        | Studienkollegs (dynamic)
        |----------------------------------------------------------------------
        */
        if (class_exists(Studienkolleg::class)) {
            Studienkolleg::query()
                ->whereNotNull('slug')
                ->where('slug', '!=', '')
                ->latest()
                ->get(['slug', 'updated_at'])
                ->each(function ($item) use ($sitemap, $baseUrl) {
                    $url = $baseUrl . '/studienkollegs/' . ltrim($item->slug, '/');

                    $tag = Url::create($url)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                        ->setPriority(0.6);

                    if (!empty($item->updated_at)) {
                        $tag->setLastModificationDate(Carbon::parse($item->updated_at));
                    }

                    $sitemap->add($tag);
                });
        }

        /*
        |----------------------------------------------------------------------
        | Write sitemap.xml
        |----------------------------------------------------------------------
        */
        $path = public_path('sitemap.xml');
        $sitemap->writeToFile($path);

        $this->info("✅ Sitemap generated successfully: {$path}");
        $this->info("✅ Public URL: {$baseUrl}/sitemap.xml");

        return self::SUCCESS;
    }
}
