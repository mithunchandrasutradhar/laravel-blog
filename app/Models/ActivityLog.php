<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'causer_id',
        'causer_name',
        'event',
        'subject_type',
        'subject_id',
        'description',
        'properties',
        'ip_address',
        'created_at',
    ];

    protected $casts = [
        'properties' => 'array',
        'created_at' => 'datetime',
    ];

    public function causer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'causer_id');
    }

    // ── Event group (first segment before the dot) ─────────────────────────
    public function getModuleAttribute(): string
    {
        return explode('.', $this->event)[0];
    }

    public function getActionAttribute(): string
    {
        return explode('.', $this->event, 2)[1] ?? $this->event;
    }

    // ── Colour / icon helpers for the view ─────────────────────────────────
    public function getEventColorAttribute(): string
    {
        return match ($this->action) {
            'created'      => '#10b981',
            'updated'      => '#3b82f6',
            'deleted', 'bulk_deleted' => '#ef4444',
            'approved'     => '#10b981',
            'rejected'     => '#f59e0b',
            'logged_in'    => '#6366f1',
            'logged_out'   => '#6b7280',
            'published'    => '#8b5cf6',
            'uploaded'     => '#0ea5e9',
            default        => '#6b7280',
        };
    }

    public function getEventIconAttribute(): string
    {
        return match ($this->action) {
            'created'      => 'fa-plus-circle',
            'updated'      => 'fa-edit',
            'deleted', 'bulk_deleted' => 'fa-trash',
            'approved'     => 'fa-check-circle',
            'rejected'     => 'fa-times-circle',
            'logged_in'    => 'fa-sign-in-alt',
            'logged_out'   => 'fa-sign-out-alt',
            'published'    => 'fa-globe',
            'uploaded'     => 'fa-upload',
            'updated_group' => 'fa-cog',
            default        => 'fa-bolt',
        };
    }
}
