<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Generate the XML sitemap for the blog.
     *
     * Includes:
     *  - Static pages (home, about, contact, blog, search)
     *  - All published posts
     *  - All categories that have at least one published post
     *  - All tags that have at least one published post
     *  - All active author profile pages
     */
    public function index(): Response
    {
        $posts = Post::published()
            ->select(['slug', 'updated_at', 'published_at'])
            ->latestPublished()
            ->get();

        $categories = Category::withPublishedPosts()
            ->select(['slug', 'updated_at'])
            ->get();

        $tags = Tag::withPublishedPosts()
            ->select(['slug', 'updated_at'])
            ->get();

        $authors = User::active()
            ->whereHas('posts', fn ($q) => $q->where('status', 'published'))
            ->select(['name', 'updated_at'])
            ->get();

        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

        // Static pages
        $staticPages = [
            ['url' => route('home'),    'priority' => '1.0', 'changefreq' => 'daily'],
            ['url' => route('blog.index'), 'priority' => '0.9', 'changefreq' => 'daily'],
            ['url' => route('about'),   'priority' => '0.7', 'changefreq' => 'monthly'],
            ['url' => route('contact'), 'priority' => '0.6', 'changefreq' => 'yearly'],
            ['url' => route('search'),  'priority' => '0.5', 'changefreq' => 'never'],
        ];

        foreach ($staticPages as $page) {
            $xml .= $this->urlEntry($page['url'], now()->toAtomString(), $page['changefreq'], $page['priority']);
        }

        // Posts
        foreach ($posts as $post) {
            $xml .= $this->urlEntry(
                route('blog.show', $post->slug),
                $post->updated_at->toAtomString(),
                'weekly',
                '0.8'
            );
        }

        // Categories
        foreach ($categories as $category) {
            $xml .= $this->urlEntry(
                route('categories.show', $category->slug),
                $category->updated_at->toAtomString(),
                'weekly',
                '0.6'
            );
        }

        // Tags
        foreach ($tags as $tag) {
            $xml .= $this->urlEntry(
                route('tags.show', $tag->slug),
                $tag->updated_at->toAtomString(),
                'weekly',
                '0.5'
            );
        }

        // Authors
        foreach ($authors as $author) {
            $username = strtolower(str_replace(' ', '-', $author->name));
            $xml .= $this->urlEntry(
                route('authors.show', $username),
                $author->updated_at->toAtomString(),
                'weekly',
                '0.6'
            );
        }

        $xml .= '</urlset>' . PHP_EOL;

        return response($xml, 200, [
            'Content-Type'  => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * Return the robots.txt content.
     */
    public function robots(): Response
    {
        $sitemapUrl = route('sitemap');

        $content  = "User-agent: *\n";
        $content .= "Allow: /\n";
        $content .= "\n";
        $content .= "Disallow: /admin/\n";
        $content .= "Disallow: /author/dashboard\n";
        $content .= "Disallow: /profile\n";
        $content .= "\n";
        $content .= "Sitemap: {$sitemapUrl}\n";

        return response($content, 200, [
            'Content-Type' => 'text/plain',
        ]);
    }

    /**
     * Build a single <url> entry for the sitemap.
     */
    private function urlEntry(string $loc, string $lastmod, string $changefreq, string $priority): string
    {
        return "  <url>\n"
            . "    <loc>" . e($loc) . "</loc>\n"
            . "    <lastmod>{$lastmod}</lastmod>\n"
            . "    <changefreq>{$changefreq}</changefreq>\n"
            . "    <priority>{$priority}</priority>\n"
            . "  </url>\n";
    }
}
