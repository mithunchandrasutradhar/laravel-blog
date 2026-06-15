<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MediaFolder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MediaFolderController extends Controller
{
    public function index(): JsonResponse
    {
        $folders = MediaFolder::withCount('media')->orderBy('name')->get();

        return response()->json($folders->map(fn ($f) => [
            'id'    => $f->id,
            'name'  => $f->name,
            'slug'  => $f->slug,
            'count' => $f->media_count,
        ]));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:media_folders,name'],
        ]);

        MediaFolder::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);

        return redirect()->route('admin.media.index')
            ->with('success', 'Folder "' . $request->name . '" created.');
    }

    public function update(Request $request, MediaFolder $mediaFolder): JsonResponse
    {
        $request->validate([
            'name' => [
                'required', 'string', 'max:100',
                Rule::unique('media_folders', 'name')->ignore($mediaFolder->id),
            ],
        ]);

        $mediaFolder->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);

        return response()->json(['success' => true, 'name' => $mediaFolder->name, 'slug' => $mediaFolder->slug]);
    }

    public function destroy(MediaFolder $mediaFolder): RedirectResponse
    {
        // Move all files in this folder to uncategorized (folder_id = null)
        $mediaFolder->media()->update(['folder_id' => null]);
        $mediaFolder->delete();

        return redirect()->route('admin.media.index')
            ->with('success', 'Folder deleted. Files moved to uncategorized.');
    }
}
