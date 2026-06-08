<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MediaController extends Controller
{
    /**
     * Allowed upload MIME types.
     *
     * @var array<string>
     */
    private const ALLOWED_MIMES = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'image/svg+xml',
        'application/pdf',
    ];

    /**
     * Max upload size in kilobytes (5 MB).
     */
    private const MAX_SIZE_KB = 5120;

    /**
     * Media per page.
     */
    private const PER_PAGE = 24;

    /**
     * Display the media library.
     */
    public function index(Request $request): View
    {
        $query = Media::latest();

        if ($request->filled('type')) {
            if ($request->type === 'image') {
                $query->where('mime_type', 'LIKE', 'image/%');
            } else {
                $query->where('mime_type', 'NOT LIKE', 'image/%');
            }
        }

        if ($request->filled('q')) {
            $query->where('name', 'LIKE', "%{$request->q}%");
        }

        $media = $query->paginate(self::PER_PAGE)->withQueryString();

        return view('admin.media.index', compact('media'));
    }

    /**
     * Handle multiple file uploads.
     *
     * Returns a JSON response so it can be used by JavaScript uploaders.
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
                'collection_name' => 'default',
            ]);

            $uploaded[] = [
                'id'        => $media->id,
                'name'      => $media->name,
                'url'       => $media->url,
                'mime_type' => $media->mime_type,
                'size'      => $media->human_size,
            ];
        }

        return response()->json([
            'success' => true,
            'files'   => $uploaded,
        ]);
    }

    /**
     * Delete a media file from disk and the database.
     */
    public function destroy(Media $media): JsonResponse|RedirectResponse
    {
        Storage::disk($media->disk)->delete($media->file_name);
        $media->delete();

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'File deleted successfully.');
    }

    /**
     * Browse endpoint for CKEditor's image browser plugin.
     *
     * Returns a JSON array of image media items.
     */
    public function browse(Request $request): JsonResponse
    {
        $images = Media::where('mime_type', 'LIKE', 'image/%')
            ->latest()
            ->limit(100)
            ->get(['id', 'name', 'file_name', 'mime_type', 'size'])
            ->map(fn ($m) => [
                'id'    => $m->id,
                'title' => $m->name,
                'url'   => $m->url,
                'size'  => $m->human_size,
            ]);

        return response()->json($images);
    }
}
