<?php

namespace App\Services;

use App\Models\ActivityLog;

class ActivityLogger
{
    /**
     * Record an activity log entry.
     *
     * @param  string       $event       Dot-notation event, e.g. "post.created"
     * @param  string       $description Human-readable description
     * @param  array        $properties  Extra context stored as JSON
     * @param  object|null  $subject     Eloquent model that was affected
     */
    public static function log(
        string $event,
        string $description,
        array $properties = [],
        ?object $subject = null
    ): void {
        try {
            $user = auth()->user();

            ActivityLog::create([
                'causer_id'    => $user?->id,
                'causer_name'  => $user?->name ?? 'System',
                'event'        => $event,
                'subject_type' => $subject ? class_basename($subject) : null,
                'subject_id'   => $subject?->id ?? null,
                'description'  => $description,
                'properties'   => $properties ?: null,
                'ip_address'   => request()->ip(),
                'created_at'   => now(),
            ]);
        } catch (\Throwable) {
            // Never let logging break the main flow
        }
    }
}
