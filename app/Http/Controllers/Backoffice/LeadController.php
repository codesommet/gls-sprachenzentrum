<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use App\Models\GlsInscription;
use App\Models\GroupApplication;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'consultations');

        $consultations = Consultation::latest()->get();
        $inscriptions = GlsInscription::with('site')->latest()->get();
        $applications = GroupApplication::with(['group.site'])->latest()->get();

        return view('backoffice.leads.index', compact(
            'tab',
            'consultations',
            'inscriptions',
            'applications',
        ));
    }

    public function destroyConsultation(Consultation $consultation)
    {
        $consultation->delete();

        return redirect()
            ->route('backoffice.leads.index', ['tab' => 'consultations'])
            ->with('success', 'Consultation supprimée avec succès.');
    }

    public function destroyInscription(GlsInscription $inscription)
    {
        $inscription->delete();

        return redirect()
            ->route('backoffice.leads.index', ['tab' => 'inscriptions'])
            ->with('success', 'Inscription supprimée avec succès.');
    }

    public function destroyApplication(GroupApplication $application)
    {
        $application->delete();

        return redirect()
            ->route('backoffice.leads.index', ['tab' => 'applications'])
            ->with('success', 'Application supprimée avec succès.');
    }
}
