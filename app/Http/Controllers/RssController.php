<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Setting;
use Illuminate\Http\Response;

class RssController extends Controller
{
    /**
     * Number of posts included in the RSS feed.
     */
    private const FEED_SIZE = 20;

    /**
     * Return an RSS 2.0 XML feed of the latest published posts.
     */
    public function feed(): Response
    {
        $posts = Post::published()
            ->with(['category', 'author'])
            ->latestPublished()
            ->limit(self::FEED_SIZE)
            ->get();

        $siteName    = Setting::get('site_name', config('app.name'));
        $siteUrl     = config('app.url');
        $description = Setting::get('site_description', 'Latest blog posts');

        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xml .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:content="http://purl.org/rss/1.0/modules/content/">' . PHP_EOL;
        $xml .= '  <channel>' . PHP_EOL;
        $xml .= '    <title>' . e($siteName) . '</title>' . PHP_EOL;
        $xml .= '    <link>' . e($siteUrl) . '</link>' . PHP_EOL;
        $xml .= '    <description>' . e($description) . '</description>' . PHP_EOL;
        $xml .= '    <language>en-us</language>' . PHP_EOL;
        $xml .= '    <lastBuildDate>' . now()->toRssString() . '</lastBuildDate>' . PHP_EOL;
        $xml .= '    <atom:link href="' . route('rss.feed') . '" rel="self" type="application/rss+xml" />' . PHP_EOL;

        foreach ($posts as $post) {
            $postUrl = route('blog.show', $post->slug);
            $xml .= '    <item>' . PHP_EOL;
            $xml .= '      <title>' . e($post->title) . '</title>' . PHP_EOL;
            $xml .= '      <link>' . e($postUrl) . '</link>' . PHP_EOL;
            $xml .= '      <guid isPermaLink="true">' . e($postUrl) . '</guid>' . PHP_EOL;
            $xml .= '      <pubDate>' . $post->published_at->toRssString() . '</pubDate>' . PHP_EOL;
            $xml .= '      <author>' . e($post->author->email) . ' (' . e($post->author->name) . ')</author>' . PHP_EOL;

            if ($post->category) {
                $xml .= '      <category>' . e($post->category->name) . '</category>' . PHP_EOL;
            }

            if ($post->short_description) {
                $xml .= '      <description><![CDATA[' . $post->short_description . ']]></description>' . PHP_EOL;
            }

            $xml .= '      <content:encoded><![CDATA[' . $post->content . ']]></content:encoded>' . PHP_EOL;

            if ($post->featured_image_url) {
                $xml .= '      <enclosure url="' . e($post->featured_image_url) . '" type="image/jpeg" />' . PHP_EOL;
            }

            $xml .= '    </item>' . PHP_EOL;
        }

        $xml .= '  </channel>' . PHP_EOL;
        $xml .= '</rss>' . PHP_EOL;

        return response($xml, 200, [
            'Content-Type'  => 'application/rss+xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
