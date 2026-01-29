<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;

class HelpController extends Controller
{
    /**
     * Display the documentation / help page
     *
     * @return \Illuminate\View\View
     */
    public function documentation()
    {
        return view('backoffice.help.documentation');
    }
}
