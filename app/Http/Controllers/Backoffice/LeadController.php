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

        // Get all active sites for the filter dropdown (exclude "Online" site — handled separately as "En ligne")
        $sites = Site::where('is_active', true)
            ->where('slug', 'not like', '%online%')
            ->orderBy('name')->get();

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

        return view('backoffice.leads.index', compact(
            'tab',
            'consultations',
            'inscriptions',
            'applications',
            'sites',
            'centreFilter',
            'centreCounts',
        ));
    }

    public function stats(Request $request)
    {
        $centreFilter = $request->get('centre', 'all');
        $sites = Site::where('is_active', true)
            ->where('slug', 'not like', '%online%')
            ->orderBy('name')->get();

        // Counts per centre
        $centreCounts = GlsInscription::selectRaw('centre, COUNT(*) as total')
            ->groupBy('centre')
            ->pluck('total', 'centre');

        // Monthly stats (last 12 months)
        $monthlyStats = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
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

        // Per-centre breakdown (exclude the "Online" site to avoid duplicate with "En ligne")
        $centreStats = [];
        $centreStats[] = [
            'name' => 'En ligne',
            'total' => $centreCounts[0] ?? 0,
        ];
        foreach ($sites as $site) {
            if (str_contains(strtolower($site->slug ?? $site->name), 'online')) {
                continue;
            }
            $centreStats[] = [
                'name' => $site->name,
                'total' => $centreCounts[$site->id] ?? 0,
            ];
        }

        // Totals
        $totalInscriptions = GlsInscription::count();
        $totalConsultations = Consultation::count();
        $totalApplications = GroupApplication::count();

        return view('backoffice.leads.stats', compact(
            'sites',
            'centreFilter',
            'centreCounts',
            'monthlyStats',
            'centreStats',
            'totalInscriptions',
            'totalConsultations',
            'totalApplications',
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
