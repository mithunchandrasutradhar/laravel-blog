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
    private const PER_PAGE = 15;

    public function index(Request $request): View
    {
        abort_if(! auth()->user()->hasPermissionTo('tags.viewAny'), 403);

        $query = Tag::withCount('posts');

        if ($request->filled('q')) {
            $query->where('name', 'LIKE', "%{$request->q}%");
        }

        $tags = $query->orderBy('name')->paginate(self::PER_PAGE)->withQueryString();

        return view('admin.tags.index', compact('tags'));
    }

    public function create(): RedirectResponse
    {
        abort_if(! auth()->user()->hasPermissionTo('tags.create'), 403);

        return redirect()->route('admin.tags.index');
    }

    public function store(StoreTagRequest $request): RedirectResponse
    {
        abort_if(! auth()->user()->hasPermissionTo('tags.create'), 403);

        Tag::create($request->validated());

        return redirect()->route('admin.tags.index')
            ->with('success', 'Tag created successfully.');
    }

    public function edit(Tag $tag): RedirectResponse
    {
        abort_if(! auth()->user()->hasPermissionTo('tags.update'), 403);

        return redirect()->route('admin.tags.index');
    }

    public function update(UpdateTagRequest $request, Tag $tag): RedirectResponse
    {
        abort_if(! auth()->user()->hasPermissionTo('tags.update'), 403);

        $tag->update($request->validated());

        return redirect()->route('admin.tags.index')
            ->with('success', 'Tag updated successfully.');
    }

    public function show(Tag $tag): RedirectResponse
    {
        abort_if(! auth()->user()->hasPermissionTo('tags.viewAny'), 403);

        return redirect()->route('admin.tags.index');
    }

    public function destroy(Tag $tag): RedirectResponse
    {
        abort_if(! auth()->user()->hasPermissionTo('tags.delete'), 403);

        $tag->posts()->detach();
        $tag->delete();

        return redirect()->route('admin.tags.index')
            ->with('success', 'Tag deleted successfully.');
    }
}
