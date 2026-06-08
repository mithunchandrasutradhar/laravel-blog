<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'post_id',
        'user_id',
        'parent_id',
        'name',
        'email',
        'body',
        'status',
        'ip_address',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * The post this comment belongs to.
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * The authenticated user who wrote this comment (nullable for guests).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The parent comment (for threaded replies).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    /**
     * Direct child replies to this comment.
     */
    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id')
                    ->where('status', 'approved')
                    ->oldest();
    }

    /**
     * All replies (regardless of status).
     */
    public function allReplies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /**
     * Only approved comments.
     */
    public function scopeApproved(Builder $query): void
    {
        $query->where('status', 'approved');
    }

    /**
     * Only pending comments.
     */
    public function scopePending(Builder $query): void
    {
        $query->where('status', 'pending');
    }

    /**
     * Only top-level comments (not replies).
     */
    public function scopeTopLevel(Builder $query): void
    {
        $query->whereNull('parent_id');
    }

    // -------------------------------------------------------------------------
    // Accessors / Helpers
    // -------------------------------------------------------------------------

    /**
     * Return the commenter's display name, preferring the linked user account.
     */
    public function getCommenterNameAttribute(): string
    {
        return $this->user?->name ?? $this->name ?? 'Anonymous';
    }

    /**
     * Return the commenter's email, preferring the linked user account.
     */
    public function getCommenterEmailAttribute(): ?string
    {
        return $this->user?->email ?? $this->email;
    }

    /**
     * Determine whether this comment is a reply to another comment.
     */
    public function isReply(): bool
    {
        return $this->parent_id !== null;
    }

    /**
     * Mark this comment as approved.
     */
    public function approve(): bool
    {
        return $this->update(['status' => 'approved']);
    }

    /**
     * Mark this comment as rejected.
     */
    public function reject(): bool
    {
        return $this->update(['status' => 'rejected']);
    }
}
