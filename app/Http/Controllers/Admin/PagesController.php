<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PagesController extends Controller
{
    public function index(): View
    {
        $pages = Page::latest()->get();
        return view('admin.pages.index', compact('pages'));
    }

    public function create(): View
    {
        return view('admin.pages.create');
    }

    public function store(Request $request): RedirectResponse
    {
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

        Page::create($data);

        return redirect()->route('admin.pages.index')
            ->with('success', 'Page created successfully.');
    }

    public function edit(Page $page): View
    {
        return view('admin.pages.edit', compact('page'));
    }

    public function update(Request $request, Page $page): RedirectResponse
    {
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

        $page->update($data);

        return redirect()->route('admin.pages.index')
            ->with('success', 'Page updated successfully.');
    }

    public function destroy(Page $page): RedirectResponse
    {
        $page->delete();

        return redirect()->route('admin.pages.index')
            ->with('success', 'Page deleted.');
    }
}
