<?php

namespace App\Http\Controllers\Frontoffice;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontoffice\GlsInscriptionStoreRequest;
use App\Models\GlsInscription;
use App\Models\Site;
use App\Mail\GlsInscriptionMail;
use App\Mail\GlsInscriptionConfirmation;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class GlsController extends Controller
{
    public function store(GlsInscriptionStoreRequest $request)
    {
        $validated = $request->validated();

        $formSource = $request->input('form_source', 'unknown');
        $validated['form_source'] = $formSource;

        if ($validated['type_cours'] === 'en_ligne' && (empty($validated['centre']) || !isset($validated['centre']))) {
            $validated['centre'] = 0;
        }

        // Create inscription
        try {
            $inscription = GlsInscription::create($validated);
        } catch (QueryException $e) {
            $isDuplicate = $e->getCode() == 23000
                || (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1062);

            if ($isDuplicate) {
                Log::warning('Duplicate GLS inscription attempt', ['email' => $validated['email']]);

                $errorMsg = 'Une inscription avec cet email existe déjà. Merci de vérifier vos informations.';

                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'status'  => 'duplicate',
                        'message' => $errorMsg,
                        'errors'  => ['email' => [$errorMsg]],
                    ], 422);
                }

                return back()->withInput()->withErrors(['email' => $errorMsg]);
            }

            Log::error('GLS inscription error', [
                'message'    => $e->getMessage(),
                'email'      => $validated['email'] ?? null,
                'group_id'   => $validated['group_id'] ?? null,
                'ip'         => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            $genericMsg = 'Une erreur est survenue. Merci de réessayer.';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'status'  => 'error',
                    'message' => $genericMsg,
                ], 500);
            }

            return back()->withInput()->withErrors(['error' => $genericMsg]);
        }

        // Load related objects for email
        $centre = null;
        if (isset($validated['centre']) && $validated['centre']) {
            $centre = Site::find($validated['centre']);
        }

        $group = null;
        if (isset($validated['group_id']) && $validated['group_id']) {
            $groupId = (int) $validated['group_id'];
            $staticName = config('google-sheets.group_names')[$groupId] ?? null;
            $groupDisplay = $staticName ?? ('Groupe ' . $groupId);

            $group = (object)[
                'id' => $groupId,
                'name' => $groupDisplay,
                'display_name' => $groupDisplay,
            ];
        }

        // Prepare data for emails
        $emailData = $validated;
        if ($centre) {
            $emailData['course_duration'] = $centre->getCourseDuration();
        }

        // Send emails (wrapped so email failure doesn't break the flow)
        try {
            Mail::to('rochdi.karouali1234@gmail.com')
                ->send(new GlsInscriptionMail($emailData, $centre, $group));

            Mail::to($validated['email'])
                ->send(new GlsInscriptionConfirmation($emailData, $centre, $group));
        } catch (\Throwable $e) {
            Log::error('GLS inscription email error: ' . $e->getMessage(), [
                'inscription_id' => $inscription->id,
            ]);
        }

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
