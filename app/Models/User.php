<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_image',
        'bio',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * The posts authored by this user.
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    /**
     * The comments left by this user.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /**
     * Scope a query to only include active users.
     */
    public function scopeActive(\Illuminate\Database\Eloquent\Builder $query): void
    {
        $query->where('status', 'active');
    }

    /**
     * Scope a query to only include verified users.
     */
    public function scopeVerified(\Illuminate\Database\Eloquent\Builder $query): void
    {
        $query->whereNotNull('email_verified_at');
    }

    // -------------------------------------------------------------------------
    // Accessors / Helpers
    // -------------------------------------------------------------------------

    /**
     * Virtual username derived from the user's display name.
     */
    public function getUsernameAttribute(): string
    {
        return Str::slug($this->name);
    }

    /**
     * Return the full URL to the user's profile image, falling back to a
     * generated avatar when none is set.
     */
    public function getProfileImageUrlAttribute(): string
    {
        if ($this->profile_image) {
            return asset('storage/' . $this->profile_image);
        }

        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=random';
    }

    /**
     * Determine whether the user's account is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Determine whether the user is an administrator.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Determine whether the user is an author.
     */
    public function isAuthor(): bool
    {
        return $this->hasRole('author');
    }
}
