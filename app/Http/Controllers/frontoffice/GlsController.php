<?php

namespace App\Http\Controllers\Frontoffice;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontoffice\GlsInscriptionStoreRequest;
use App\Models\GlsInscription;
use App\Models\Site;
use App\Mail\GlsInscriptionMail;
use App\Mail\GlsInscriptionConfirmation;
use Illuminate\Support\Facades\Mail;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class GlsController extends Controller
{
    public function store(GlsInscriptionStoreRequest $request)
    {
        // Get validated data
        $validated = $request->validated();

        // Get form source for tracking (modal or page)
        $formSource = $request->input('form_source', 'unknown');
        $validated['form_source'] = $formSource;

        // For en_ligne courses, provide a default centre value if not set
        if ($validated['type_cours'] === 'en_ligne' && (empty($validated['centre']) || !isset($validated['centre']))) {
            $validated['centre'] = 0; // Use 0 as placeholder for online courses
        }

        // Duplicate protection disabled for development with static groups
        // TODO: Re-enable once dynamic groups are implemented and tested
        /*
        $query = GlsInscription::where('email', $validated['email'])
            ->where('group_id', $validated['group_id']);

        if (isset($validated['centre']) && $validated['centre']) {
            $query->where('centre', $validated['centre']);
        }

        if ($query->exists()) {
            return response()->json([
                'success' => false,
                'status'  => 'duplicate',
                'message' => 'Vous avez déjà fait une demande pour ce groupe.',
            ], 409);
        }
        */

        // Create inscription
        $inscription = GlsInscription::create($validated);

        // Load related objects for email
        $centre = null;
        if (isset($validated['centre']) && $validated['centre']) {
            $centre = Site::find($validated['centre']);
        }

        $group = null;
        if (isset($validated['group_id']) && $validated['group_id']) {
            $group = \App\Models\Group::find($validated['group_id']);
            // If group not found in DB, create a fallback object with at least the ID
            if (!$group) {
                // Extract the group display from horaire_prefere if available
                $groupDisplay = 'Groupe ' . $validated['group_id'];
                if (isset($validated['horaire_prefere']) && $validated['horaire_prefere']) {
                    $groupDisplay = 'Groupe ' . $validated['horaire_prefere'];
                }
                $group = (object)[
                    'id' => $validated['group_id'],
                    'name' => $groupDisplay,
                    'display_name' => $groupDisplay
                ];
            }
        }

        // Prepare data for emails
        $emailData = $validated;
        if ($centre) {
            $emailData['course_duration'] = $centre->getCourseDuration();
        }

        // Send admin notification
        Mail::to('rochdi.karouali1234@gmail.com')
            ->send(new GlsInscriptionMail($emailData, $centre, $group));

        // Send student confirmation
        Mail::to($validated['email'])
            ->send(new GlsInscriptionConfirmation($emailData, $centre, $group));

        // Return JSON success
        return response()->json([
            'success' => true,
            'status'  => 'success',
            'message' => 'Inscription enregistrée. Email envoyé.',
            'form_source' => $formSource,
            'redirect_url' => LaravelLocalization::localizeUrl(route('front.gls-inscription.success')),
        ], 200);
    }
}
