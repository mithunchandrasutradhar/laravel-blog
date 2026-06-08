<?php

namespace App\Services;

use App\Models\Post;

class SeoService
{
    /**
     * Name of the site (pulled from the settings helper at runtime).
     */
    private string $siteName;

    /**
     * Base URL of the site.
     */
    private string $siteUrl;

    public function __construct()
    {
        $this->siteName = setting('site_name', config('app.name', 'Blog'));
        $this->siteUrl  = rtrim(config('app.url', url('/')), '/');
    }

    // -------------------------------------------------------------------------
    // Meta tags
    // -------------------------------------------------------------------------

    /**
     * Return a key → value array of standard HTML <meta> tags for the given post.
     *
     * @return array<string, string>
     */
    public function generateMetaTags(Post $post): array
    {
        $title       = $post->meta_title ?: $post->title;
        $description = $post->meta_description ?: $post->short_description;
        $url         = $post->url;

        return [
            'title'       => $title . ' | ' . $this->siteName,
            'description' => $this->truncateDescription($description),
            'keywords'    => $post->tags->pluck('name')->implode(', '),
            'author'      => $post->author->name ?? $this->siteName,
            'robots'      => 'index, follow',
            'canonical'   => $post->canonical_url ?: $url,
        ];
    }

    // -------------------------------------------------------------------------
    // Open Graph
    // -------------------------------------------------------------------------

    /**
     * Return a key → value array of Open Graph meta properties for the given post.
     *
     * @return array<string, string>
     */
    public function generateOpenGraph(Post $post): array
    {
        $title       = $post->meta_title ?: $post->title;
        $description = $post->meta_description ?: $post->short_description;

        return [
            'og:type'        => 'article',
            'og:title'       => $title,
            'og:description' => $this->truncateDescription($description),
            'og:url'         => $post->url,
            'og:site_name'   => $this->siteName,
            'og:image'       => $post->featured_image_url ?? $this->defaultOgImage(),
            'og:image:alt'   => $title,
            'og:locale'      => str_replace('-', '_', app()->getLocale()),
            'article:published_time' => optional($post->published_at)->toIso8601String() ?? '',
            'article:modified_time'  => $post->updated_at->toIso8601String(),
            'article:author'         => $post->author->name ?? '',
            'article:section'        => $post->category->name ?? '',
        ];
    }

    // -------------------------------------------------------------------------
    // Twitter Card
    // -------------------------------------------------------------------------

    /**
     * Return a key → value array of Twitter Card meta properties for the given post.
     *
     * @return array<string, string>
     */
    public function generateTwitterCard(Post $post): array
    {
        $title       = $post->meta_title ?: $post->title;
        $description = $post->meta_description ?: $post->short_description;
        $image       = $post->featured_image_url ?? $this->defaultOgImage();

        return [
            'twitter:card'        => 'summary_large_image',
            'twitter:title'       => $title,
            'twitter:description' => $this->truncateDescription($description),
            'twitter:image'       => $image,
            'twitter:image:alt'   => $title,
            'twitter:site'        => setting('twitter_handle', ''),
            'twitter:creator'     => setting('twitter_handle', ''),
        ];
    }

    // -------------------------------------------------------------------------
    // JSON-LD Schema
    // -------------------------------------------------------------------------

    /**
     * Return a JSON-LD structured-data array (Article schema) for the given post.
     *
     * @return array<string, mixed>
     */
    public function generateSchema(Post $post): array
    {
        $title       = $post->meta_title ?: $post->title;
        $description = $post->meta_description ?: $post->short_description;
        $image       = $post->featured_image_url ?? $this->defaultOgImage();

        $schema = [
            '@context'         => 'https://schema.org',
            '@type'            => 'Article',
            'headline'         => $title,
            'description'      => $this->truncateDescription($description),
            'url'              => $post->url,
            'image'            => [
                '@type' => 'ImageObject',
                'url'   => $image,
            ],
            'datePublished'    => optional($post->published_at)->toIso8601String(),
            'dateModified'     => $post->updated_at->toIso8601String(),
            'author'           => [
                '@type' => 'Person',
                'name'  => $post->author->name ?? $this->siteName,
            ],
            'publisher'        => [
                '@type' => 'Organization',
                'name'  => $this->siteName,
                'logo'  => [
                    '@type' => 'ImageObject',
                    'url'   => $this->siteUrl . '/images/logo.png',
                ],
            ],
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id'   => $post->url,
            ],
        ];

        if ($post->tags->isNotEmpty()) {
            $schema['keywords'] = $post->tags->pluck('name')->implode(', ');
        }

        if ($post->category) {
            $schema['articleSection'] = $post->category->name;
        }

        return $schema;
    }

    /**
     * Convert the schema array to an inline <script> JSON-LD string.
     */
    public function schemaToScript(array $schema): string
    {
        return '<script type="application/ld+json">' .
            json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) .
            '</script>';
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function truncateDescription(?string $description, int $maxLength = 160): string
    {
        $description = strip_tags((string) $description);

        if (mb_strlen($description) <= $maxLength) {
            return $description;
        }

        return mb_substr($description, 0, $maxLength - 3) . '...';
    }

    private function defaultOgImage(): string
    {
        return setting('og_image', $this->siteUrl . '/images/og-default.jpg');
    }
}
