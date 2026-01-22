<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HelpController extends Controller
{
    /**
     * Show the help center landing page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('help.index');
    }

    /**
     * Show the jobseeker guide.
     *
     * @return \Illuminate\View\View
     */
    public function jobseeker()
    {
        return view('help.jobseeker');
    }

    /**
     * Show the employer guide.
     *
     * @return \Illuminate\View\View
     */
    public function employer()
    {
        return view('help.employer');
    }
}
