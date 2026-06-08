<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Subscriber extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'verified_at',
        'token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
        ];
    }

    // -------------------------------------------------------------------------
    // Boot
    // -------------------------------------------------------------------------

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Subscriber $subscriber) {
            if (empty($subscriber->token)) {
                $subscriber->token = Str::random(64);
            }
        });
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /**
     * Only verified subscribers.
     */
    public function scopeVerified(Builder $query): void
    {
        $query->whereNotNull('verified_at');
    }

    /**
     * Only unverified (pending) subscribers.
     */
    public function scopeUnverified(Builder $query): void
    {
        $query->whereNull('verified_at');
    }

    // -------------------------------------------------------------------------
    // Accessors / Helpers
    // -------------------------------------------------------------------------

    /**
     * Determine whether this subscriber has verified their email.
     */
    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }

    /**
     * Mark this subscriber as verified.
     */
    public function verify(): bool
    {
        return $this->update(['verified_at' => now()]);
    }

    /**
     * Regenerate the unsubscribe / verification token.
     */
    public function regenerateToken(): bool
    {
        return $this->update(['token' => Str::random(64)]);
    }
}
