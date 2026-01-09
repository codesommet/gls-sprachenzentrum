<?php

namespace App\Http\Controllers\Frontoffice;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Models\Group;

class GroupController extends Controller
{
    public function show($slug)
    {
        if (in_array($slug, ['gls-online', 'online'], true)) {
            return redirect()->route('front.online-courses');
        }

        $site = Site::where('slug', $slug)->firstOrFail();

        $view = str_replace('gls-', '', $slug);

        $groups = Group::with('teacher')
            ->where('site_id', $site->id)
            ->orderBy('status')
            ->orderBy('level')
            ->get()
            ->groupBy('period_label');

        if (!view()->exists("frontoffice.sites.$view")) {
            abort(404);
        }

        return view("frontoffice.sites.$view", compact('site', 'groups'));
    }
}
