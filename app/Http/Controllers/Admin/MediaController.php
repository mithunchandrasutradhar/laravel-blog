<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\MediaFolder;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MediaController extends Controller
{
    private const ALLOWED_MIMES = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'image/svg+xml',
        'application/pdf',
    ];

    private const MAX_SIZE_KB = 5120;

    private const PER_PAGE = 24;

    public function index(Request $request): View
    {
        $folders       = MediaFolder::withCount('media')->orderBy('name')->get();
        $currentFolder = null;

        $query = Media::latest();

        if ($request->filled('folder')) {
            $currentFolder = MediaFolder::where('slug', $request->folder)->firstOrFail();
            $query->where('folder_id', $currentFolder->id);
        }

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

        return view('admin.media.index', compact('media', 'folders', 'currentFolder'));
    }

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

        $folder = null;
        if ($request->filled('folder_id')) {
            $folder = MediaFolder::find($request->folder_id);
        }

        $uploaded = [];

        foreach ($request->file('files') as $file) {
            $originalName = $file->getClientOriginalName();
            $safeName     = Str::slug(pathinfo($originalName, PATHINFO_FILENAME))
                . '-' . uniqid()
                . '.' . $file->getClientOriginalExtension();

            $subDir = $folder
                ? 'media/' . $folder->slug
                : 'media/' . now()->format('Y/m');

            $path = $file->storeAs($subDir, $safeName, 'public');

            $media = Media::create([
                'name'            => pathinfo($originalName, PATHINFO_FILENAME),
                'file_name'       => $path,
                'mime_type'       => $file->getMimeType(),
                'disk'            => 'public',
                'size'            => $file->getSize(),
                'collection_name' => $folder ? $folder->slug : 'default',
                'folder_id'       => $folder?->id,
            ]);

            $uploaded[] = [
                'id'        => $media->id,
                'name'      => $media->name,
                'file_name' => $media->file_name,
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

    public function list(Request $request): JsonResponse
    {
        $query = Media::where('mime_type', 'LIKE', 'image/%')->latest();

        if ($request->filled('q')) {
            $query->where('name', 'LIKE', "%{$request->q}%");
        }

        if ($request->filled('folder')) {
            $folder = MediaFolder::where('slug', $request->folder)->first();
            if ($folder) {
                $query->where('folder_id', $folder->id);
            }
        }

        $paginator = $query->paginate(self::PER_PAGE);

        return response()->json([
            'data'      => $paginator->getCollection()->map(fn ($m) => [
                'id'        => $m->id,
                'name'      => $m->name,
                'url'       => $m->url,
                'file_name' => $m->file_name,
                'size'      => $m->human_size,
            ]),
            'has_more'  => $paginator->hasMorePages(),
            'next_page' => $paginator->currentPage() + 1,
            'total'     => $paginator->total(),
        ]);
    }

    public function rename(Request $request, Media $media): JsonResponse
    {
        $request->validate(['name' => ['required', 'string', 'max:255']]);
        $media->update(['name' => $request->name]);

        return response()->json(['success' => true, 'name' => $media->name]);
    }

    public function move(Request $request, Media $media): JsonResponse
    {
        $request->validate([
            'folder_id' => ['nullable', 'integer', 'exists:media_folders,id'],
        ]);

        $folder = $request->folder_id ? MediaFolder::find($request->folder_id) : null;

        $media->update([
            'folder_id'       => $folder?->id,
            'collection_name' => $folder ? $folder->slug : 'default',
        ]);

        return response()->json(['success' => true]);
    }

    public function copy(Request $request, Media $media): JsonResponse
    {
        $request->validate([
            'folder_id' => ['nullable', 'integer', 'exists:media_folders,id'],
        ]);

        $folder   = $request->folder_id ? MediaFolder::find($request->folder_id) : null;
        $ext      = pathinfo($media->file_name, PATHINFO_EXTENSION);
        $safeName = Str::slug($media->name) . '-copy-' . uniqid() . '.' . $ext;
        $subDir   = $folder ? 'media/' . $folder->slug : 'media/' . now()->format('Y/m');
        $newPath  = $subDir . '/' . $safeName;

        if (Storage::disk($media->disk)->exists($media->file_name)) {
            Storage::disk($media->disk)->copy($media->file_name, $newPath);
        }

        $copy = Media::create([
            'name'            => $media->name . ' (copy)',
            'file_name'       => $newPath,
            'mime_type'       => $media->mime_type,
            'disk'            => $media->disk,
            'size'            => $media->size,
            'collection_name' => $folder ? $folder->slug : $media->collection_name,
            'folder_id'       => $folder?->id,
        ]);

        return response()->json([
            'success'    => true,
            'id'         => $copy->id,
            'name'       => $copy->name,
            'url'        => $copy->url,
            'human_size' => $copy->human_size,
        ]);
    }

    public function destroy(Media $media): JsonResponse|RedirectResponse
    {
        Storage::disk($media->disk)->delete($media->file_name);
        $media->delete();

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'File deleted successfully.');
    }

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
