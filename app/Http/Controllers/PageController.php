<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\View\View;

class PageController extends Controller
{
    /**
     * Display the About page.
     *
     * Content is pulled from the settings table (key = "about_content") so the
     * admin can update it without a deployment.
     */
    public function about(): View
    {
        $content  = Setting::get('about_content', '');
        $teamData = Setting::get('team_data', '[]');

        // team_data is stored as JSON
        $team = collect(json_decode($teamData, true) ?? []);

        return view('pages.about', compact('content', 'team'));
    }

    /**
     * Display the Terms of Service page.
     */
    public function terms(): View
    {
        $content      = Setting::get('terms_content', '');
        $lastUpdated  = Setting::get('terms_last_updated');

        return view('pages.terms', compact('content', 'lastUpdated'));
    }

    /**
     * Display the Privacy Policy page.
     */
    public function privacy(): View
    {
        $content      = Setting::get('privacy_content', '');
        $lastUpdated  = Setting::get('privacy_last_updated');

        return view('pages.privacy', compact('content', 'lastUpdated'));
    }
}
