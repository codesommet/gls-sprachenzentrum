<?php

namespace App\Http\Controllers\Frontoffice;

use App\Http\Controllers\Controller;
use App\Models\GlsInscription;
use App\Models\Site;
use Illuminate\Http\Request;
use App\Mail\GlsInscriptionMail;
use App\Mail\GlsInscriptionConfirmation;
use Illuminate\Support\Facades\Mail;

class GlsController extends Controller
{
    public function store(Request $request)
    {
        // Validate inputs
        $validated = $request->validate([
            'name'   => 'required|string|max:255',
            'email'  => 'required|email',
            'phone'  => 'required',
            'adresse' => 'required',
            'type_cours' => 'required|in:presentiel,en_ligne',
            'niveau' => 'nullable|string|max:10',
            'centre' => 'required_if:type_cours,presentiel|nullable|integer|exists:sites,id',
            'group_id' => 'required|integer|exists:groups,id',
        ]);

        // Get form source for tracking (modal or page)
        $formSource = $request->input('form_source', 'unknown');

        // Duplicate protection (check by email + group)
        $existsQuery = GlsInscription::where('email', $request->email)
            ->where('group_id', $request->group_id);

        if ($request->centre) {
            $existsQuery->where('centre', $request->centre);
        }

        if ($existsQuery->exists()) {
            return response()->json([
                'success' => false,
                'status'  => 'duplicate',
                'message' => 'Vous avez déjà fait une demande pour ce groupe.',
            ], 409);
        }

        // Save in DB with form_source for tracking
        $data = $request->all();
        $data['form_source'] = $formSource;
        $inscription = GlsInscription::create($data);

        // Load related objects
        $centre = Site::find($request->centre);
        $group  = \App\Models\Group::find($request->group_id);

        // Add course duration to data for email
        if ($centre) {
            $data['course_duration'] = $centre->getCourseDuration();
        }

        // Admin email
        Mail::to('rochdi.karouali1234@gmail.com')
            ->send(new GlsInscriptionMail($data, $centre, $group));

        // Student confirmation email
        Mail::to($request->email)
            ->send(new GlsInscriptionConfirmation($data, $centre, $group));

        // Return success with redirect URL
        return response()->json([
            'success' => true,
            'status'  => 'success',
            'message' => 'Inscription enregistrée. Email envoyé.',
            'form_source' => $formSource,
            'redirect_url' => \LaravelLocalization::localizeUrl(route('front.gls-inscription.success')),
        ]);
    }
}
