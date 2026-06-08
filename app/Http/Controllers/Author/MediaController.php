<?php

namespace App\Http\Controllers\Author;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MediaController extends Controller
{
    /**
     * Media items per page.
     */
    private const PER_PAGE = 24;

    /**
     * Max upload size in kilobytes (5 MB).
     */
    private const MAX_SIZE_KB = 5120;

    /**
     * Display the author's media library (only their own uploads).
     */
    public function index(Request $request): View
    {
        $query = Media::where(function ($q) {
            // Media scoped to posts owned by this author
            $q->where('model_type', \App\Models\Post::class)
              ->whereIn('model_id', \App\Models\Post::where('user_id', auth()->id())->pluck('id'));
        })->orWhere(function ($q) {
            // Or media without a parent model (standalone uploads by this author)
            $q->whereNull('model_type');
        })->latest();

        if ($request->filled('type') && $request->type === 'image') {
            $query->where('mime_type', 'LIKE', 'image/%');
        }

        $media = $query->paginate(self::PER_PAGE)->withQueryString();

        return view('author.media.index', compact('media'));
    }

    /**
     * Upload a media file.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'files'   => ['required', 'array'],
            'files.*' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,gif,webp,svg,pdf',
                'max:' . self::MAX_SIZE_KB,
            ],
        ]);

        $uploaded = [];

        foreach ($request->file('files') as $file) {
            $originalName = $file->getClientOriginalName();
            $safeName     = Str::slug(pathinfo($originalName, PATHINFO_FILENAME))
                . '-' . uniqid()
                . '.' . $file->getClientOriginalExtension();

            $path = $file->storeAs('media/' . now()->format('Y/m'), $safeName, 'public');

            $media = Media::create([
                'name'            => pathinfo($originalName, PATHINFO_FILENAME),
                'file_name'       => $path,
                'mime_type'       => $file->getMimeType(),
                'disk'            => 'public',
                'size'            => $file->getSize(),
                'collection_name' => 'author-uploads',
            ]);

            $uploaded[] = [
                'id'   => $media->id,
                'name' => $media->name,
                'url'  => $media->url,
            ];
        }

        return response()->json(['success' => true, 'files' => $uploaded]);
    }

    /**
     * Delete a media file (author may only delete their own files).
     */
    public function destroy(Media $media): RedirectResponse|JsonResponse
    {
        // Ensure the author owns this media item
        $ownsMedia = $media->model_type === null
            || (
                $media->model_type === \App\Models\Post::class
                && \App\Models\Post::where('id', $media->model_id)
                    ->where('user_id', auth()->id())
                    ->exists()
            );

        abort_unless($ownsMedia, 403, 'You do not have permission to delete this file.');

        \Illuminate\Support\Facades\Storage::disk($media->disk)->delete($media->file_name);
        $media->delete();

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'File deleted successfully.');
    }
}
