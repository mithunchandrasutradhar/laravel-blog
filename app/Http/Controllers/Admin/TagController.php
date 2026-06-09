<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTagRequest;
use App\Http\Requests\Admin\UpdateTagRequest;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TagController extends Controller
{
    /**
     * Admin tags per page.
     */
    private const PER_PAGE = 15;

    /**
     * Display a listing of tags.
     */
    public function index(Request $request): View
    {
        $query = Tag::withCount('posts');

        if ($request->filled('q')) {
            $query->where('name', 'LIKE', "%{$request->q}%");
        }

        $tags = $query->orderBy('name')->paginate(self::PER_PAGE)->withQueryString();

        return view('admin.tags.index', compact('tags'));
    }

    /**
     * Show the create tag form.
     */
    public function create(): RedirectResponse
    {
        return redirect()->route('admin.tags.index');
    }

    /**
     * Store a new tag.
     */
    public function store(StoreTagRequest $request): RedirectResponse
    {
        Tag::create($request->validated());

        return redirect()->route('admin.tags.index')
            ->with('success', 'Tag created successfully.');
    }

    /**
     * Show the edit form for a tag.
     */
    public function edit(Tag $tag): RedirectResponse
    {
        return redirect()->route('admin.tags.index');
    }

    /**
     * Update a tag.
     */
    public function update(UpdateTagRequest $request, Tag $tag): RedirectResponse
    {
        $tag->update($request->validated());

        return redirect()->route('admin.tags.index')
            ->with('success', 'Tag updated successfully.');
    }

    /**
     * Show a tag (admin view).
     */
    public function show(Tag $tag): RedirectResponse
    {
        return redirect()->route('admin.tags.index');
    }

    /**
     * Delete a tag.
     */
    public function destroy(Tag $tag): RedirectResponse
    {
        $tag->posts()->detach();
        $tag->delete();

        return redirect()->route('admin.tags.index')
            ->with('success', 'Tag deleted successfully.');
    }
}
