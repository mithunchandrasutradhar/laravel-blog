<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class MediaService
{
    /**
     * Allowed MIME extensions for image uploads.
     *
     * @var array<string>
     */
    private array $allowedImageTypes;

    /**
     * Maximum upload size in kilobytes.
     */
    private int $maxUploadSize;

    public function __construct()
    {
        $this->allowedImageTypes = config('blog.allowed_image_types', ['jpg', 'jpeg', 'png', 'webp', 'gif']);
        $this->maxUploadSize     = config('blog.max_upload_size', 10240);
    }

    // -------------------------------------------------------------------------
    // Upload
    // -------------------------------------------------------------------------

    /**
     * Upload an image file to the given disk/directory, optionally optimizing
     * it before storage.
     *
     * @param  UploadedFile  $file
     * @param  string        $directory   Storage sub-directory (e.g. "posts").
     * @param  string        $disk        Laravel filesystem disk name.
     * @param  bool          $optimize    Whether to run Intervention Image optimization.
     * @return array{path: string, url: string, media: Media}
     *
     * @throws \InvalidArgumentException
     */
    public function uploadImage(
        UploadedFile $file,
        string $directory = 'uploads',
        string $disk = 'public',
        bool $optimize = true
    ): array {
        $this->validateFile($file);

        $extension = strtolower($file->getClientOriginalExtension());
        $filename  = Str::uuid() . '.' . $extension;
        $path      = $directory . '/' . $filename;

        // Run through Intervention Image to strip EXIF and re-encode when optimize flag is set.
        if ($optimize && in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            $image   = Image::read($file->getRealPath());
            $encoded = $image->toWebp(85);
            $path    = $directory . '/' . Str::uuid() . '.webp';
            Storage::disk($disk)->put($path, (string) $encoded);
        } else {
            Storage::disk($disk)->putFileAs($directory, $file, $filename);
        }

        $media = Media::create([
            'collection_name' => $directory,
            'name'            => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'file_name'       => $path,
            'mime_type'       => $file->getMimeType(),
            'disk'            => $disk,
            'size'            => $file->getSize(),
        ]);

        return [
            'path'  => $path,
            'url'   => Storage::disk($disk)->url($path),
            'media' => $media,
        ];
    }

    // -------------------------------------------------------------------------
    // Delete
    // -------------------------------------------------------------------------

    /**
     * Delete a media record and remove its file from storage.
     */
    public function deleteMedia(Media $media): bool
    {
        Storage::disk($media->disk)->delete($media->file_name);

        // Also attempt to delete the thumbnail if one was generated.
        $thumbPath = $this->thumbnailPath($media->file_name);
        if (Storage::disk($media->disk)->exists($thumbPath)) {
            Storage::disk($media->disk)->delete($thumbPath);
        }

        return $media->delete();
    }

    /**
     * Delete a file directly by its storage path without requiring a Media record.
     */
    public function deleteByPath(string $path, string $disk = 'public'): bool
    {
        return Storage::disk($disk)->delete($path);
    }

    // -------------------------------------------------------------------------
    // Optimization
    // -------------------------------------------------------------------------

    /**
     * Re-encode an existing stored image at the given quality, returning the
     * updated path.
     */
    public function optimizeImage(string $storagePath, int $quality = 85, string $disk = 'public'): string
    {
        $contents = Storage::disk($disk)->get($storagePath);

        if ($contents === null) {
            throw new \RuntimeException("File not found on disk [{$disk}]: {$storagePath}");
        }

        $image   = Image::read($contents);
        $encoded = $image->toWebp($quality);

        $optimizedPath = preg_replace('/\.[a-zA-Z]+$/', '.webp', $storagePath);
        Storage::disk($disk)->put($optimizedPath, (string) $encoded);

        return $optimizedPath;
    }

    // -------------------------------------------------------------------------
    // Thumbnails
    // -------------------------------------------------------------------------

    /**
     * Generate a resized thumbnail from an existing stored image.
     *
     * @return string  The storage path of the generated thumbnail.
     */
    public function generateThumbnail(
        string $storagePath,
        int $width = 400,
        int $height = 300,
        string $disk = 'public'
    ): string {
        $contents = Storage::disk($disk)->get($storagePath);

        if ($contents === null) {
            throw new \RuntimeException("File not found on disk [{$disk}]: {$storagePath}");
        }

        $image = Image::read($contents)
            ->cover($width, $height);

        $thumbPath = $this->thumbnailPath($storagePath);
        Storage::disk($disk)->put($thumbPath, (string) $image->toWebp(80));

        return $thumbPath;
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Validate that the uploaded file meets size and type constraints.
     *
     * @throws \InvalidArgumentException
     */
    private function validateFile(UploadedFile $file): void
    {
        $extension = strtolower($file->getClientOriginalExtension());

        if (! in_array($extension, $this->allowedImageTypes, true)) {
            throw new \InvalidArgumentException(
                "File type [{$extension}] is not allowed. Allowed types: " .
                implode(', ', $this->allowedImageTypes)
            );
        }

        $sizeInKb = $file->getSize() / 1024;

        if ($sizeInKb > $this->maxUploadSize) {
            throw new \InvalidArgumentException(
                "File size ({$sizeInKb} KB) exceeds the maximum allowed size ({$this->maxUploadSize} KB)."
            );
        }
    }

    /**
     * Derive the thumbnail storage path from a given original path.
     */
    private function thumbnailPath(string $originalPath): string
    {
        $dir      = dirname($originalPath);
        $basename = pathinfo($originalPath, PATHINFO_FILENAME);

        return $dir . '/thumbs/' . $basename . '_thumb.webp';
    }
}
