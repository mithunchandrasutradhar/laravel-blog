<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Support\Facades\Storage;
use SimpleXMLElement;

class RssService
{
    /**
     * Base URL of the application (no trailing slash).
     */
    private string $baseUrl;

    /**
     * Site name pulled from the settings helper.
     */
    private string $siteName;

    /**
     * Site description / tagline.
     */
    private string $siteDescription;

    public function __construct()
    {
        $this->baseUrl         = rtrim(config('app.url', url('/')), '/');
        $this->siteName        = setting('site_name', config('app.name', 'Blog'));
        $this->siteDescription = setting('site_description', 'A modern blog platform');
    }

    // -------------------------------------------------------------------------
    // Generation
    // -------------------------------------------------------------------------

    /**
     * Generate the RSS 2.0 feed, persist it as `public/feed.xml`, and return
     * the raw XML string.
     *
     * The feed contains the 50 most recently published posts.
     */
    public function generate(): string
    {
        $posts = Post::published()
            ->with(['category', 'author', 'tags'])
            ->latestPublished()
            ->limit(50)
            ->get();

        $xml = new SimpleXMLElement(
            '<?xml version="1.0" encoding="UTF-8"?><rss version="2.0" '
            . 'xmlns:content="http://purl.org/rss/1.0/modules/content/" '
            . 'xmlns:atom="http://www.w3.org/2005/Atom" '
            . 'xmlns:dc="http://purl.org/dc/elements/1.1/"/>'
        );

        $channel = $xml->addChild('channel');

        // ---- Channel metadata -------------------------------------------
        $channel->addChild('title', htmlspecialchars($this->siteName, ENT_XML1));
        $channel->addChild('link', htmlspecialchars($this->baseUrl, ENT_XML1));
        $channel->addChild('description', htmlspecialchars($this->siteDescription, ENT_XML1));
        $channel->addChild('language', str_replace('_', '-', app()->getLocale()));
        $channel->addChild('lastBuildDate', now()->toRfc822String());
        $channel->addChild('generator', 'Laravel Blog Platform');
        $channel->addChild('docs', 'https://www.rssboard.org/rss-specification');

        // Self-referencing atom:link.
        $selfLink = $channel->addChild('atom:link', null, 'http://www.w3.org/2005/Atom');
        $selfLink->addAttribute('href', htmlspecialchars($this->baseUrl . '/feed.xml', ENT_XML1));
        $selfLink->addAttribute('rel', 'self');
        $selfLink->addAttribute('type', 'application/rss+xml');

        // ---- Items -------------------------------------------------------
        foreach ($posts as $post) {
            $item = $channel->addChild('item');

            $item->addChild('title', htmlspecialchars($post->title, ENT_XML1));
            $item->addChild('link', htmlspecialchars(route('blog.show', $post->slug), ENT_XML1));
            $item->addChild('guid', htmlspecialchars(route('blog.show', $post->slug), ENT_XML1));
            $item->addChild('pubDate', $post->published_at->toRfc822String());

            // dc:creator
            $creator = $item->addChild('dc:creator', null, 'http://purl.org/dc/elements/1.1/');
            $creator[0] = $post->author->name ?? $this->siteName;

            // category
            if ($post->category) {
                $item->addChild('category', htmlspecialchars($post->category->name, ENT_XML1));
            }

            // description (short excerpt)
            $excerpt = $post->short_description
                ?: truncate(strip_tags($post->content ?? ''), config('blog.excerpt_length', 160));
            $item->addChild('description', htmlspecialchars($excerpt, ENT_XML1));

            // content:encoded (full HTML body)
            $contentEncoded = $item->addChild(
                'content:encoded',
                null,
                'http://purl.org/rss/1.0/modules/content/'
            );
            $contentEncoded[0] = '<![CDATA[' . $post->content . ']]>';

            // Enclosure for featured image
            if ($post->featured_image_url) {
                $enclosure = $item->addChild('enclosure');
                $enclosure->addAttribute('url', $post->featured_image_url);
                $enclosure->addAttribute('type', 'image/jpeg');
                $enclosure->addAttribute('length', '0');
            }
        }

        $contents = $xml->asXML();

        Storage::disk('public')->put('feed.xml', $contents);

        return $contents;
    }
}
