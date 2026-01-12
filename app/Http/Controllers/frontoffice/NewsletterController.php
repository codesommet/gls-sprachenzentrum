<?php

namespace App\Http\Controllers\Frontoffice;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function subscribe(Request $request)
    {
        $validated = $request->validate([
            'email'  => ['required', 'email:rfc,dns', 'max:255'],
            'source' => ['nullable', 'string', 'max:50'],
        ]);

        $email  = mb_strtolower(trim($validated['email']));
        $locale = app()->getLocale();

        // Create or get subscriber (avoid duplicates)
        $subscriber = NewsletterSubscriber::firstOrCreate(
            ['email' => $email],
            [
                'locale'        => $locale,
                'source'        => $validated['source'] ?? 'footer',
                'subscribed_at' => now(),
            ]
        );

        // If already exists
        if (!$subscriber->wasRecentlyCreated) {
            return response()->json([
                'ok' => true,
                'status' => 'already',
                'message' => __('footer.newsletter.already'),
            ]);
        }

        // New subscription
        return response()->json([
            'ok' => true,
            'status' => 'subscribed',
            'message' => __('footer.newsletter.success'),
        ]);
    }
}
