<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Services\ActivityLogger;
use App\Services\HtmlSanitizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PagesController extends Controller
{
    public function index(): View
    {
        abort_if(! auth()->user()->hasPermissionTo('panel.admin'), 403);

        $pages = Page::latest()->get();
        return view('admin.pages.index', compact('pages'));
    }

    public function create(): View
    {
        abort_if(! auth()->user()->hasPermissionTo('panel.admin'), 403);

        return view('admin.pages.create');
    }

    public function store(Request $request): RedirectResponse
    {
        abort_if(! auth()->user()->hasPermissionTo('panel.admin'), 403);

        $data = $request->validate([
            'title'            => 'required|string|max:200',
            'slug'             => 'nullable|string|max:200|unique:pages,slug',
            'content'          => 'nullable|string',
            'meta_title'       => 'nullable|string|max:70',
            'meta_description' => 'nullable|string|max:160',
            'canonical_url'    => 'nullable|url|max:500',
            'status'           => 'required|in:draft,published',
            'show_in_footer'   => 'nullable|boolean',
        ]);

        $data['slug']           = Str::slug($data['slug'] ?: $data['title']);
        $data['show_in_footer'] = $request->boolean('show_in_footer');
        $data['content']        = HtmlSanitizer::clean($data['content'] ?? '');

        $page = Page::create($data);
        ActivityLogger::log('page.created', "Page \"{$page->title}\" was created.", [], $page);

        return redirect()->route('admin.pages.index')
            ->with('success', 'Page created successfully.');
    }

    public function edit(Page $page): View
    {
        abort_if(! auth()->user()->hasPermissionTo('panel.admin'), 403);

        return view('admin.pages.edit', compact('page'));
    }

    public function update(Request $request, Page $page): RedirectResponse
    {
        abort_if(! auth()->user()->hasPermissionTo('panel.admin'), 403);

        $data = $request->validate([
            'title'            => 'required|string|max:200',
            'slug'             => 'nullable|string|max:200|unique:pages,slug,' . $page->id,
            'content'          => 'nullable|string',
            'meta_title'       => 'nullable|string|max:70',
            'meta_description' => 'nullable|string|max:160',
            'canonical_url'    => 'nullable|url|max:500',
            'status'           => 'required|in:draft,published',
            'show_in_footer'   => 'nullable|boolean',
        ]);

        $data['slug']           = Str::slug($data['slug'] ?: $data['title']);
        $data['show_in_footer'] = $request->boolean('show_in_footer');
        $data['content']        = HtmlSanitizer::clean($data['content'] ?? '');

        $page->update($data);
        ActivityLogger::log('page.updated', "Page \"{$page->title}\" was updated.", [], $page);

        return redirect()->route('admin.pages.index')
            ->with('success', 'Page updated successfully.');
    }

    public function show(Page $page): RedirectResponse
    {
        abort_if(! auth()->user()->hasPermissionTo('panel.admin'), 403);

        return redirect()->route('admin.pages.edit', $page);
    }

    public function destroy(Page $page): RedirectResponse
    {
        abort_if(! auth()->user()->hasPermissionTo('panel.admin'), 403);

        $pageTitle = $page->title;
        $page->delete();
        ActivityLogger::log('page.deleted', "Page \"{$pageTitle}\" was deleted.");

        return redirect()->route('admin.pages.index')
            ->with('success', 'Page deleted.');
    }
}
