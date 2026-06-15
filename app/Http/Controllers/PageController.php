<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Setting;
use Illuminate\View\View;

class PageController extends Controller
{
    /**
     * Display the About page (still settings-driven).
     */
    public function about(): View
    {
        $content  = Setting::get('about_content', '');
        $teamData = Setting::get('team_data', '[]');
        $team     = collect(json_decode($teamData, true) ?? []);

        return view('pages.about', compact('content', 'team'));
    }

    /**
     * Serve any dynamic page by slug.
     */
    public function show(string $slug): View
    {
        $page = Page::published()->where('slug', $slug)->firstOrFail();

        $siteName = settings('site_name', config('app.name'));
        $seo = [
            'title'       => ($page->meta_title ?: $page->title) . ' — ' . $siteName,
            'description' => $page->meta_description ?: '',
            'canonical'   => $page->canonical_url ?: route('blog.show', $slug),
            'og_title'    => $page->meta_title ?: $page->title,
            'og_type'     => 'website',
        ];

        return view('pages.dynamic', compact('page', 'seo'));
    }
}
