<?php

namespace App\Console\Commands;

use App\Models\Media;
use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImportPostImages extends Command
{
    protected $signature   = 'media:import-post-images';
    protected $description = 'Download all post featured images and add them to the media library';

    public function handle(): int
    {
        $posts = Post::whereNotNull('featured_image')->get();

        $this->info("Found {$posts->count()} posts with images.");

        $imported = 0;
        $skipped  = 0;

        foreach ($posts as $post) {
            $src = $post->featured_image;

            // Already tracked in media library
            if (Media::where('file_name', $src)->exists()) {
                $this->line("  SKIP  [{$post->id}] already in library");
                $skipped++;
                continue;
            }

            if (str_starts_with($src, 'http')) {
                $result = $this->downloadExternal($post, $src);
            } else {
                $result = $this->registerLocal($post, $src);
            }

            if ($result) {
                $imported++;
            } else {
                $skipped++;
            }
        }

        $this->info("Done. Imported: {$imported} | Skipped/failed: {$skipped}");

        return self::SUCCESS;
    }

    private function downloadExternal(Post $post, string $url): bool
    {
        try {
            $this->line("  GET   [{$post->id}] {$url}");

            $ctx = stream_context_create([
                'http' => [
                    'timeout'       => 15,
                    'user_agent'    => 'Mozilla/5.0 (compatible; BlogMediaImport/1.0)',
                    'ignore_errors' => true,
                ],
                'ssl'  => ['verify_peer' => false, 'verify_peer_name' => false],
            ]);

            $bytes = @file_get_contents($url, false, $ctx);

            if ($bytes === false || strlen($bytes) < 1000) {
                $this->warn("  FAIL  [{$post->id}] could not download");
                return false;
            }

            $finfo    = new \finfo(FILEINFO_MIME_TYPE);
            $mime     = $finfo->buffer($bytes);
            $ext      = match ($mime) {
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/gif'  => 'gif',
                'image/webp' => 'webp',
                default      => 'jpg',
            };

            $slug     = Str::slug(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_FILENAME));
            $safeName = $slug . '-' . uniqid() . '.' . $ext;
            $path     = 'media/' . now()->format('Y/m') . '/' . $safeName;

            Storage::disk('public')->put($path, $bytes);

            $humanName = $post->title ? Str::limit($post->title, 60, '') : $slug;

            Media::create([
                'name'            => $humanName,
                'file_name'       => $path,
                'mime_type'       => $mime,
                'disk'            => 'public',
                'size'            => strlen($bytes),
                'collection_name' => 'posts',
            ]);

            // Update post to point to local copy
            $post->update(['featured_image' => $path]);

            $this->info("  DONE  [{$post->id}] saved as {$path}");
            return true;

        } catch (\Throwable $e) {
            $this->warn("  ERR   [{$post->id}] " . $e->getMessage());
            return false;
        }
    }

    private function registerLocal(Post $post, string $path): bool
    {
        if (! Storage::disk('public')->exists($path)) {
            $this->warn("  MISS  [{$post->id}] file not found: {$path}");
            return false;
        }

        $content   = Storage::disk('public')->get($path);
        $finfo     = new \finfo(FILEINFO_MIME_TYPE);
        $mime      = $finfo->buffer($content);
        $humanName = $post->title
            ? Str::limit($post->title, 60, '')
            : pathinfo($path, PATHINFO_FILENAME);

        Media::create([
            'name'            => $humanName,
            'file_name'       => $path,
            'mime_type'       => $mime,
            'disk'            => 'public',
            'size'            => Storage::disk('public')->size($path),
            'collection_name' => 'posts',
        ]);

        $this->info("  REG   [{$post->id}] registered local file");
        return true;
    }
}
