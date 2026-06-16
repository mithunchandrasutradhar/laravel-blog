<?php

namespace App\Http\Controllers\Author;

use App\Http\Controllers\Controller;
use App\Models\MediaFolder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MediaFolderController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:media_folders,name'],
        ]);

        MediaFolder::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);

        return redirect()->route('author.media.index')
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
        $mediaFolder->media()->update(['folder_id' => null, 'collection_name' => 'default']);
        $mediaFolder->delete();

        return redirect()->route('author.media.index')
            ->with('success', 'Folder deleted. Files moved to uncategorized.');
    }
}
