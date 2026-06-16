<?php

namespace App\Http\Controllers\Author;

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
    private const MAX_SIZE_KB = 5120;
    private const PER_PAGE    = 24;

    private function postsFolder(): MediaFolder
    {
        return MediaFolder::firstOrCreate(
            ['slug' => 'posts'],
            ['name' => 'Posts']
        );
    }

    public function index(Request $request): View
    {
        $folder = $this->postsFolder();
        $query  = Media::where('folder_id', $folder->id)->latest();

        if ($request->filled('type')) {
            $query->when(
                $request->type === 'image',
                fn ($q) => $q->where('mime_type', 'LIKE', 'image/%'),
                fn ($q) => $q->where('mime_type', 'NOT LIKE', 'image/%'),
            );
        }

        if ($request->filled('q')) {
            $query->where('name', 'LIKE', "%{$request->q}%");
        }

        $media    = $query->paginate(self::PER_PAGE)->withQueryString();
        $folderId = $folder->id;

        return view('author.media.index', compact('media', 'folder', 'folderId'));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'files'   => ['required', 'array'],
            'files.*' => ['required', 'file', 'mimes:jpg,jpeg,png,gif,webp,svg,pdf', 'max:' . self::MAX_SIZE_KB],
        ]);

        $folder   = $this->postsFolder();
        $uploaded = [];

        foreach ($request->file('files') as $file) {
            $originalName = $file->getClientOriginalName();
            $mimeType     = $file->getMimeType() ?? $file->getClientMimeType();
            $safeName     = Str::slug(pathinfo($originalName, PATHINFO_FILENAME))
                . '-' . uniqid()
                . '.' . $file->getClientOriginalExtension();

            $path  = $file->storeAs('media/posts', $safeName, 'public');

            $media = Media::create([
                'name'            => pathinfo($originalName, PATHINFO_FILENAME),
                'file_name'       => $path,
                'mime_type'       => $mimeType,
                'disk'            => 'public',
                'size'            => $file->getSize(),
                'collection_name' => 'posts',
                'folder_id'       => $folder->id,
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

        return response()->json(['success' => true, 'files' => $uploaded]);
    }

    public function list(Request $request): JsonResponse
    {
        $folder = $this->postsFolder();
        $query  = Media::where('folder_id', $folder->id)
                       ->where('mime_type', 'LIKE', 'image/%')
                       ->latest();

        if ($request->filled('q')) {
            $query->where('name', 'LIKE', "%{$request->q}%");
        }

        $paginated = $query->paginate(self::PER_PAGE, ['*'], 'page', $request->integer('page', 1));

        return response()->json([
            'data'     => $paginated->map(fn ($m) => [
                'id'        => $m->id,
                'name'      => $m->name,
                'url'       => $m->url,
                'file_name' => $m->file_name,
                'size'      => $m->human_size,
            ]),
            'has_more' => $paginated->hasMorePages(),
            'total'    => $paginated->total(),
        ]);
    }

    public function rename(Request $request, Media $media): JsonResponse
    {
        $this->authorizeMedia($media);
        $request->validate(['name' => ['required', 'string', 'max:255']]);

        $media->update(['name' => $request->name]);

        return response()->json(['success' => true, 'name' => $media->name]);
    }

    public function destroy(Media $media): JsonResponse|RedirectResponse
    {
        $this->authorizeMedia($media);

        Storage::disk($media->disk)->delete($media->file_name);
        $media->delete();

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'File deleted.');
    }

    public function bulkDestroy(Request $request): JsonResponse
    {
        $request->validate([
            'ids'   => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:media,id'],
        ]);

        $folder = $this->postsFolder();
        $count  = 0;

        foreach (Media::whereIn('id', $request->ids)->where('folder_id', $folder->id)->get() as $media) {
            Storage::disk($media->disk)->delete($media->file_name);
            $media->delete();
            $count++;
        }

        return response()->json(['success' => true, 'count' => $count]);
    }

    public function ckeditorUpload(Request $request): JsonResponse
    {
        $request->validate([
            'upload' => ['required', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:' . self::MAX_SIZE_KB],
        ]);

        $folder   = $this->postsFolder();
        $file     = $request->file('upload');
        $safeName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
            . '-' . uniqid()
            . '.' . $file->getClientOriginalExtension();

        $path = $file->storeAs('media/posts', $safeName, 'public');

        Media::create([
            'name'            => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'file_name'       => $path,
            'mime_type'       => $file->getMimeType(),
            'disk'            => 'public',
            'size'            => $file->getSize(),
            'collection_name' => 'posts',
            'folder_id'       => $folder->id,
        ]);

        return response()->json(['url' => asset('storage/' . $path)]);
    }

    private function authorizeMedia(Media $media): void
    {
        $folder = $this->postsFolder();
        abort_unless(
            $media->folder_id === $folder->id,
            403,
            'You may only manage files in the Posts folder.'
        );
    }
}
