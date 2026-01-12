<?php

namespace App\Http\Controllers\Frontoffice;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\GroupApplication;
use Illuminate\Http\Request;

class GroupApplicationController extends Controller
{
    public function store(Request $request, Group $group)
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'min:3', 'max:120'],
            'whatsapp_number' => ['required', 'string', 'min:6', 'max:30'],
            'birthday' => ['nullable', 'date'],
            'card_recto' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'card_verso' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ]);

        $application = GroupApplication::create([
            'group_id' => $group->id,
            'full_name' => $validated['full_name'],
            'whatsapp_number' => $validated['whatsapp_number'],
            'birthday' => $validated['birthday'] ?? null,
            'status' => 'pending',
        ]);

        // Attach media (Spatie Media Library)
        $application->addMediaFromRequest('card_recto')->toMediaCollection('card_recto');
        $application->addMediaFromRequest('card_verso')->toMediaCollection('card_verso');

        return back()->with('success', "Votre demande d'inscription a été envoyée. Nous vous contacterons sur WhatsApp.");
    }
}
