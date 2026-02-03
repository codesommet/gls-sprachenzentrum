<?php

namespace App\Http\Controllers\Frontoffice;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BlogController extends Controller
{
    /**
     * BLOG INDEX PAGE
     */
    public function index(Request $request): View
    {
        $q = trim((string) $request->get('q'));
        $categorySlug = $request->get('category');

        // ✅ Featured post
        $featured = BlogPost::query()
            ->with('category')
            ->where('status', 'published')
            ->where('featured', true)
            ->latest()
            ->first();

        // ✅ Categories sidebar
        $categories = BlogCategory::query()
            ->where('is_active', 1)
            ->orderBy('name_fr')
            ->get();

        // ✅ Popular posts (most viewed)
        $popular = BlogPost::query()
            ->with('category')
            ->where('status', 'published')
            ->orderByDesc('views')
            ->take(5)
            ->get();

        // ✅ Posts list (PAGINATED) + search + category filter
        $posts = BlogPost::query()
            ->with('category')
            ->where('status', 'published')
            ->when($categorySlug, function ($query) use ($categorySlug) {
                $query->whereHas('category', function ($q) use ($categorySlug) {
                    $q->where('slug', $categorySlug);
                });
            })
            ->when($q, function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('title_fr', 'like', "%{$q}%")
                       ->orWhere('title_en', 'like', "%{$q}%")
                       ->orWhere('content_fr', 'like', "%{$q}%")
                       ->orWhere('content_en', 'like', "%{$q}%");
                });
            })
            ->latest()
            ->paginate(9)
            ->withQueryString(); // keep ?q= & ?category=

        return view('frontoffice.blog.blog', compact(
            'posts',
            'featured',
            'categories',
            'popular'
        ));
    }

    /**
     * BLOG DETAILS PAGE
     */
    public function details(string $slug): View
    {
        // ✅ Get requested article + category
        $post = BlogPost::query()
            ->with('category')
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        // ✅ Increment view counter
        $post->increment('views');

        // ✅ Recent posts sidebar
        $recentPosts = BlogPost::query()
            ->with('category')
            ->where('status', 'published')
            ->latest()
            ->take(5)
            ->get();

        // ✅ Categories sidebar
        $categories = BlogCategory::query()
            ->where('is_active', 1)
            ->orderBy('name_fr')
            ->get();

        // ✅ Related posts (same category)
        $relatedPosts = BlogPost::query()
            ->with('category')
            ->where('category_id', $post->category_id)
            ->where('id', '!=', $post->id)
            ->where('status', 'published')
            ->latest()
            ->take(3)
            ->get();

        return view('frontoffice.blog.blog-details', [
            'post'         => $post,
            'recentPosts'  => $recentPosts,
            'categories'   => $categories,
            'relatedPosts' => $relatedPosts,
        ]);
    }
}
