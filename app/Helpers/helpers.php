<?php

use App\Models\Advertisement;
use App\Services\SettingService;
use Illuminate\Support\Facades\Auth;

if (! function_exists('setting')) {
    /**
     * Retrieve a blog setting value by key.
     *
     * This is a convenience wrapper around SettingService::get() that also
     * works before the service container is fully booted by falling back to
     * the Setting model's static helper.
     *
     * @param  string  $key
     * @param  mixed   $default  Value returned when the key is not found.
     * @return mixed
     */
    function setting(string $key, mixed $default = null): mixed
    {
        try {
            return app(SettingService::class)->get($key, $default);
        } catch (\Throwable) {
            return \App\Models\Setting::get($key, $default);
        }
    }
}

if (! function_exists('active_ad')) {
    /**
     * Return the first active Advertisement model for the given position, or
     * null when none is configured.
     *
     * Usage in Blade:
     *   @php($ad = active_ad('header'))
     *   @if($ad) ... @endif
     */
    function active_ad(string $position): ?Advertisement
    {
        try {
            return app(\App\Services\AdvertisementService::class)->getForPosition($position);
        } catch (\Throwable) {
            return Advertisement::forPosition($position);
        }
    }
}

if (! function_exists('format_date')) {
    /**
     * Format a date/datetime value using the given format string.
     *
     * Accepts any value parseable by Carbon (string, int timestamp,
     * DateTimeInterface, etc.).
     *
     * @param  mixed        $date    The date value to format.
     * @param  string       $format  A PHP date() format string.  Defaults to
     *                               the app's configured date format or a
     *                               human-friendly default.
     * @return string
     */
    function format_date(mixed $date, string $format = 'M d, Y'): string
    {
        if ($date === null || $date === '') {
            return '';
        }

        try {
            return \Carbon\Carbon::parse($date)->format($format);
        } catch (\Throwable) {
            return (string) $date;
        }
    }
}

if (! function_exists('reading_time')) {
    /**
     * Calculate the estimated reading time for a block of content.
     *
     * @param  string  $content  Raw HTML or plain-text content.
     * @return int  Reading time in minutes (minimum 1).
     */
    function reading_time(string $content): int
    {
        $wordsPerMinute = config('blog.reading_speed', 200);
        $wordCount      = str_word_count(strip_tags($content));
        $minutes        = (int) ceil($wordCount / $wordsPerMinute);

        return max(1, $minutes);
    }
}

if (! function_exists('truncate')) {
    /**
     * Truncate a string to the specified character length, appending an
     * ellipsis when the text is cut.
     *
     * HTML tags are stripped before truncation to avoid broken markup.
     *
     * @param  string|null  $text    The text to truncate.
     * @param  int          $length  Maximum number of characters.
     * @param  string       $end     The suffix appended when truncated.
     * @return string
     */
    function truncate(?string $text, int $length = 160, string $end = '...'): string
    {
        if ($text === null || $text === '') {
            return '';
        }

        $text = strip_tags($text);

        if (mb_strlen($text) <= $length) {
            return $text;
        }

        // Break at the nearest whole word boundary.
        $truncated = mb_substr($text, 0, $length);
        $lastSpace = mb_strrpos($truncated, ' ');

        if ($lastSpace !== false) {
            $truncated = mb_substr($truncated, 0, $lastSpace);
        }

        return rtrim($truncated) . $end;
    }
}

if (! function_exists('is_admin')) {
    /**
     * Determine whether the currently authenticated user has the `admin` role.
     *
     * Returns false when no user is authenticated.
     *
     * @return bool
     */
    function is_admin(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        return $user !== null && $user->hasRole('admin');
    }
}

if (! function_exists('is_author')) {
    /**
     * Determine whether the currently authenticated user has the `author` role
     * (or higher, i.e. also returns true for admins).
     *
     * Returns false when no user is authenticated.
     *
     * @return bool
     */
    function is_author(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        return $user !== null && ($user->hasRole('author') || $user->hasRole('admin'));
    }
}
