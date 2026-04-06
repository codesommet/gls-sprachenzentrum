<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use App\Models\GlsInscription;
use App\Models\GroupApplication;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'consultations');
        $centreFilter = $request->get('centre', 'all');

        // Get all active sites for the filter dropdown
        $sites = Site::where('is_active', true)->orderBy('name')->get();

        // Build queries with optional centre filter
        $inscriptionsQuery = GlsInscription::with('site')->latest();
        $applicationsQuery = GroupApplication::with(['group.site'])->latest();

        if ($centreFilter !== 'all') {
            if ($centreFilter === 'online') {
                $inscriptionsQuery->where(function ($q) {
                    $q->where('centre', 0)->orWhere('type_cours', 'en_ligne');
                });
                // Applications don't have online, so return empty
                $applicationsQuery->whereRaw('1 = 0');
            } else {
                $inscriptionsQuery->where('centre', $centreFilter);
                $applicationsQuery->whereHas('group', function ($q) use ($centreFilter) {
                    $q->where('site_id', $centreFilter);
                });
            }
        }

        $consultations = Consultation::latest()->get();
        $inscriptions = $inscriptionsQuery->get();
        $applications = $applicationsQuery->get();

        // Counts per centre for inscriptions
        $centreCounts = GlsInscription::selectRaw('centre, COUNT(*) as total')
            ->groupBy('centre')
            ->pluck('total', 'centre');

        // Monthly stats for chart (last 6 months)
        $monthlyStats = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $month = $date->format('Y-m');
            $label = $date->translatedFormat('M Y');

            $inscQuery = GlsInscription::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month);
            $consQuery = Consultation::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month);
            $appQuery = GroupApplication::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month);

            if ($centreFilter !== 'all') {
                if ($centreFilter === 'online') {
                    $inscQuery->where(function ($q) {
                        $q->where('centre', 0)->orWhere('type_cours', 'en_ligne');
                    });
                    $appQuery->whereRaw('1 = 0');
                } else {
                    $inscQuery->where('centre', $centreFilter);
                    $appQuery->whereHas('group', function ($q) use ($centreFilter) {
                        $q->where('site_id', $centreFilter);
                    });
                }
            }

            $monthlyStats[] = [
                'label' => $label,
                'inscriptions' => $inscQuery->count(),
                'consultations' => $consQuery->count(),
                'applications' => $appQuery->count(),
            ];
        }

        return view('backoffice.leads.index', compact(
            'tab',
            'consultations',
            'inscriptions',
            'applications',
            'sites',
            'centreFilter',
            'centreCounts',
            'monthlyStats',
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
