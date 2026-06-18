<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Video;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VideoController extends Controller
{
    public function index(): View
    {
        abort_if(! auth()->user()->hasPermissionTo('videos.viewAny'), 403);

        $videos = Video::with('category')->ordered()->paginate(20);

        return view('admin.videos.index', compact('videos'));
    }

    public function create(): View
    {
        abort_if(! auth()->user()->hasPermissionTo('videos.create'), 403);

        $categories = Category::orderBy('name')->get();

        return view('admin.videos.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_if(! auth()->user()->hasPermissionTo('videos.create'), 403);

        $data = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'title'       => 'required|string|max:255',
            'youtube_url' => 'required|url|max:500',
            'description' => 'nullable|string|max:1000',
            'is_active'   => 'boolean',
            'sort_order'  => 'integer|min:0|max:9999',
        ]);

        $data['is_active']  = $request->boolean('is_active', true);
        $data['sort_order'] = $request->input('sort_order', 0);

        $video = Video::create($data);
        ActivityLogger::log('video.created', "Video \"{$video->title}\" was created.", [], $video);

        return redirect()->route('admin.videos.index')
            ->with('success', 'Video added successfully.');
    }

    public function show(Video $video): RedirectResponse
    {
        abort_if(! auth()->user()->hasPermissionTo('videos.viewAny'), 403);

        return redirect()->route('admin.videos.edit', $video);
    }

    public function edit(Video $video): View
    {
        abort_if(! auth()->user()->hasPermissionTo('videos.update'), 403);

        $categories = Category::orderBy('name')->get();

        return view('admin.videos.edit', compact('video', 'categories'));
    }

    public function update(Request $request, Video $video): RedirectResponse
    {
        abort_if(! auth()->user()->hasPermissionTo('videos.update'), 403);

        $data = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'title'       => 'required|string|max:255',
            'youtube_url' => 'required|url|max:500',
            'description' => 'nullable|string|max:1000',
            'is_active'   => 'boolean',
            'sort_order'  => 'integer|min:0|max:9999',
        ]);

        $data['is_active']  = $request->boolean('is_active');
        $data['sort_order'] = $request->input('sort_order', 0);

        $video->update($data);
        ActivityLogger::log('video.updated', "Video \"{$video->title}\" was updated.", [], $video);

        return redirect()->route('admin.videos.index')
            ->with('success', 'Video updated successfully.');
    }

    public function destroy(Video $video): RedirectResponse
    {
        abort_if(! auth()->user()->hasPermissionTo('videos.delete'), 403);

        $videoTitle = $video->title;
        $video->delete();
        ActivityLogger::log('video.deleted', "Video \"{$videoTitle}\" was deleted.");

        return redirect()->route('admin.videos.index')
            ->with('success', 'Video deleted successfully.');
    }
}
