<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    |
    | Control how many items appear per page on the public blog and the
    | admin panel respectively.
    |
    */

    'posts_per_page' => (int) env('BLOG_POSTS_PER_PAGE', 12),

    'admin_per_page' => (int) env('BLOG_ADMIN_PER_PAGE', 15),

    /*
    |--------------------------------------------------------------------------
    | Excerpt
    |--------------------------------------------------------------------------
    |
    | Default character length used when auto-generating a post excerpt.
    |
    */

    'excerpt_length' => (int) env('BLOG_EXCERPT_LENGTH', 160),

    /*
    |--------------------------------------------------------------------------
    | Media Uploads
    |--------------------------------------------------------------------------
    |
    | Permitted MIME extensions for image and document uploads, plus the
    | maximum single-file size expressed in kilobytes.
    |
    */

    'allowed_image_types' => ['jpg', 'jpeg', 'png', 'webp', 'svg', 'gif'],

    'allowed_doc_types' => ['pdf'],

    'max_upload_size' => (int) env('BLOG_MAX_UPLOAD_SIZE', 10240), // KB

    /*
    |--------------------------------------------------------------------------
    | Google reCAPTCHA v3
    |--------------------------------------------------------------------------
    |
    | Keys are sourced from the .env file so they are never committed to VCS.
    |
    */

    'recaptcha_site_key' => env('RECAPTCHA_SITE_KEY', ''),

    'recaptcha_secret_key' => env('RECAPTCHA_SECRET_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    |
    | Default TTL (in seconds) for blog-related cache entries such as settings,
    | featured posts, and navigation data.
    |
    */

    'cache_ttl' => (int) env('BLOG_CACHE_TTL', 3600), // 1 hour

    /*
    |--------------------------------------------------------------------------
    | Reading Speed
    |--------------------------------------------------------------------------
    |
    | Average words-per-minute used when calculating a post's estimated
    | reading time.
    |
    */

    'reading_speed' => (int) env('BLOG_READING_SPEED', 200), // words per minute

];
