<?php

namespace App\Http\Controllers\Backoffice\Encaissement;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Services\Encaissement\EncaissementAnalyticsService;
use Illuminate\Http\Request;

class EncaissementDashboardController extends Controller
{
    public function __construct(
        private EncaissementAnalyticsService $analytics
    ) {}

    /**
     * Main encaissement dashboard.
     */
    public function index(Request $request)
    {
        $month = $request->get('month') ?: now()->format('Y-m');
        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
            $month = now()->format('Y-m');
        }
        $siteId = $request->get('site_id');

        $sid = $siteId ? (int) $siteId : null;
        $data = $this->analytics->getDashboardData($sid, $month);
        $sites = Site::where('is_active', true)->orderBy('name')->get();

        // Chart data
        $monthlyEvolution = $this->analytics->getMonthlyEvolution($sid, 12);
        $methodEvolution = $this->analytics->getMethodEvolution($sid, 6);

        return view('backoffice.encaissements.dashboard', compact(
            'data', 'sites', 'month', 'siteId', 'monthlyEvolution', 'methodEvolution'
        ));
    }

    /**
     * Rentability dashboard.
     */
    public function rentabilite(Request $request)
    {
        $month = $request->get('month') ?: now()->format('Y-m');
        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
            $month = now()->format('Y-m');
        }
        $siteId = $request->get('site_id');
        $sites = Site::where('is_active', true)->orderBy('name')->get();

        $rentabilite = null;
        $history = [];
        $comparison = [];

        $monthlyEvolution = [];
        if ($siteId) {
            $rentabilite = $this->analytics->getRentabilite((int) $siteId, $month);
            $history = $this->analytics->getRentabiliteHistory((int) $siteId, 6);
            $monthlyEvolution = $this->analytics->getMonthlyEvolution((int) $siteId, 12);
        }

        $comparison = $this->analytics->compareSites($month);

        return view('backoffice.encaissements.rentabilite', compact(
            'sites', 'month', 'siteId', 'rentabilite', 'history', 'comparison', 'monthlyEvolution'
        ));
    }

    /**
     * Operator performance page.
     */
    public function operators(Request $request)
    {
        $month = $request->get('month') ?: now()->format('Y-m');
        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
            $month = now()->format('Y-m');
        }
        $siteId = $request->get('site_id');
        $sites = Site::where('is_active', true)->orderBy('name')->get();

        $operators = $this->analytics->getOperatorPerformance(
            $siteId ? (int) $siteId : null,
            $month
        );

        return view('backoffice.encaissements.operators', compact('sites', 'month', 'siteId', 'operators'));
    }
}
