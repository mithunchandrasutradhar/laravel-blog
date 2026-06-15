<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Page extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'content',
        'meta_title',
        'meta_description',
        'og_image',
        'canonical_url',
        'status',
        'show_in_footer',
    ];

    protected $casts = [
        'show_in_footer' => 'boolean',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Page $page) {
            if (empty($page->slug)) {
                $page->slug = Str::slug($page->title);
            }
        });
    }

    public function scopePublished(Builder $query): void
    {
        $query->where('status', 'published');
    }

    public function scopeFooter(Builder $query): void
    {
        $query->where('show_in_footer', true)->where('status', 'published');
    }

    public function getUrlAttribute(): string
    {
        return route('blog.show', $this->slug);
    }
}
