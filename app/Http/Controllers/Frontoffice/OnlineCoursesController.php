<?php

namespace App\Http\Controllers\Frontoffice;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Models\Group;

class OnlineCoursesController extends Controller
{
    public function index()
    {
        // Get ONLINE site (slug MUST be 'online')
        $site = Site::where('slug', 'online')->firstOrFail();

        // Get ONLINE groups
        $groups = Group::where('site_id', $site->id)
            ->whereDate('date_fin', '>=', now()) // optional but recommended
            ->orderBy('status')
            ->orderBy('level')
            ->get()
            ->groupBy('period_label');

        return view('frontoffice.online-courses', compact('site', 'groups'));
    }
}
