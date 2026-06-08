<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Support\Facades\Storage;
use SimpleXMLElement;

class SitemapService
{
    /**
     * Base URL of the application (no trailing slash).
     */
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('app.url', url('/')), '/');
    }

    // -------------------------------------------------------------------------
    // Generation
    // -------------------------------------------------------------------------

    /**
     * Generate a sitemap XML string, write it to `public/sitemap.xml`, and
     * return the full contents of the file.
     *
     * The sitemap includes:
     *   - Static pages (home, contact, about, etc.)
     *   - Published posts
     *   - Category index pages
     *   - Tag index pages
     */
    public function generate(): string
    {
        $xml = new SimpleXMLElement(
            '<?xml version="1.0" encoding="UTF-8"?>'
            . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"'
            . ' xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"/>'
        );

        // ---- Static pages ------------------------------------------------
        $this->addUrl($xml, $this->baseUrl . '/', 'daily', '1.0');
        $this->addUrl($xml, $this->baseUrl . '/about', 'monthly', '0.5');
        $this->addUrl($xml, $this->baseUrl . '/contact', 'monthly', '0.5');

        // ---- Published posts ---------------------------------------------
        Post::published()
            ->with(['category', 'author'])
            ->select(['id', 'slug', 'updated_at', 'featured_image', 'title'])
            ->orderByDesc('published_at')
            ->chunk(200, function ($posts) use ($xml) {
                foreach ($posts as $post) {
                    $urlNode = $xml->addChild('url');
                    $urlNode->addChild('loc', htmlspecialchars(route('posts.show', $post->slug), ENT_XML1));
                    $urlNode->addChild('lastmod', $post->updated_at->toAtomString());
                    $urlNode->addChild('changefreq', 'weekly');
                    $urlNode->addChild('priority', '0.8');

                    // Image sitemap extension.
                    if ($post->featured_image) {
                        $imageNode = $urlNode->addChild('image:image', null, 'http://www.google.com/schemas/sitemap-image/1.1');
                        $imageNode->addChild(
                            'image:loc',
                            htmlspecialchars(asset('storage/' . $post->featured_image), ENT_XML1),
                            'http://www.google.com/schemas/sitemap-image/1.1'
                        );
                        $imageNode->addChild(
                            'image:title',
                            htmlspecialchars($post->title, ENT_XML1),
                            'http://www.google.com/schemas/sitemap-image/1.1'
                        );
                    }
                }
            });

        // ---- Category pages ---------------------------------------------
        Category::orderBy('name')
            ->chunk(100, function ($categories) use ($xml) {
                foreach ($categories as $category) {
                    $this->addUrl(
                        $xml,
                        route('categories.show', $category->slug),
                        'weekly',
                        '0.6'
                    );
                }
            });

        // ---- Tag pages --------------------------------------------------
        Tag::orderBy('name')
            ->chunk(200, function ($tags) use ($xml) {
                foreach ($tags as $tag) {
                    $this->addUrl(
                        $xml,
                        route('tags.show', $tag->slug),
                        'monthly',
                        '0.4'
                    );
                }
            });

        $contents = $xml->asXML();

        // Write to public directory so it is accessible via HTTP.
        Storage::disk('public')->put('sitemap.xml', $contents);

        return $contents;
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Append a <url> node to the sitemap.
     */
    private function addUrl(
        SimpleXMLElement $xml,
        string $loc,
        string $changefreq = 'monthly',
        string $priority = '0.5',
        ?string $lastmod = null
    ): void {
        $urlNode = $xml->addChild('url');
        $urlNode->addChild('loc', htmlspecialchars($loc, ENT_XML1));
        $urlNode->addChild('lastmod', $lastmod ?? now()->toAtomString());
        $urlNode->addChild('changefreq', $changefreq);
        $urlNode->addChild('priority', $priority);
    }
}
