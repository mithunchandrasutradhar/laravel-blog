<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;   // still used for index() pluck and updateGroup file lookup
use App\Services\SettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingsController extends Controller
{
    /**
     * All recognised setting groups.
     *
     * @var array<string>
     */
    private const GROUPS = [
        'general',
        'seo',
        'social',
        'mail',
        'appearance',
        'integrations',
        'advanced',
    ];

    /**
     * Display the settings management page.
     *
     * Loads all settings grouped so the view can render them in tabs.
     */
    public function index(): View
    {
        // Flat key→value map so the view can do $settings['site_name'] directly
        $settings = Setting::pluck('value', 'key')->toArray();

        return view('admin.settings.index', compact('settings'));
    }

    /**
     * Update all settings from the form (flat list, all groups at once).
     */
    public function update(Request $request): RedirectResponse
    {
        $service = app(SettingService::class);

        // Fields that are purely file uploads — skip when no file is provided
        // so existing stored paths are never overwritten with null.
        $fileOnlyFields = ['logo', 'favicon', 'og_image', 'about_image'];

        // Skip blank password so the existing stored password is never cleared.
        $skipIfBlank = ['mail_password'];

        $input = $request->except(['_token', '_method', 'section']);

        foreach ($input as $key => $value) {
            if (str_contains($key, '__')) {
                [$group, $settingKey] = explode('__', $key, 2);
            } else {
                $group      = 'general';
                $settingKey = $key;
            }

            if ($request->hasFile($key)) {
                $value = $request->file($key)->store('settings', 'public');
            } elseif (in_array($key, $fileOnlyFields)) {
                continue;
            } elseif (in_array($key, $skipIfBlank) && blank($value)) {
                continue;
            }

            $service->set($settingKey, $value, $group);
        }

        $service->clearCache();

        return redirect()->route('admin.settings.index')
            ->with('success', 'Settings saved successfully.');
    }

    /**
     * Send a test email using the current DB mail settings.
     */
    public function sendTestEmail(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);

        $s    = app(SettingService::class);
        $host = $s->get('mail_host');

        if (! $host) {
            return response()->json([
                'success' => false,
                'message' => 'Mail settings not saved yet. Click "Save Mail Settings" first.',
            ], 422);
        }

        $this->applyMailConfig();

        // Purge any cached mailer so a fresh SMTP transport is created with the new config.
        app('mail.manager')->purge('smtp');

        try {
            $toEmail  = $request->email;
            $fromName = config('mail.from.name');

            Mail::mailer('smtp')->raw(
                "This is a test email from {$fromName}.\n\nYour SMTP settings are working correctly.\n\nServer: {$host}:{$s->get('mail_port')}",
                fn ($msg) => $msg->to($toEmail)->subject("Test Email — {$fromName}")
            );

            return response()->json([
                'success' => true,
                'message' => "Test email sent to {$toEmail} via {$host}",
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Apply mail settings from the database to the runtime config so that
     * all mail operations (including test sends) use the admin-configured values.
     */
    private function applyMailConfig(): void
    {
        $s = app(SettingService::class);

        $host = $s->get('mail_host');
        if (! $host) {
            return; // No DB settings yet — keep .env defaults
        }

        config([
            'mail.default'                 => $s->get('mail_mailer', 'smtp'),
            'mail.mailers.smtp.host'       => $host,
            'mail.mailers.smtp.port'       => (int) $s->get('mail_port', 587),
            'mail.mailers.smtp.encryption' => $s->get('mail_encryption', 'tls') ?: null,
            'mail.mailers.smtp.username'   => $s->get('mail_username'),
            'mail.mailers.smtp.password'   => $s->get('mail_password'),
            'mail.from.address'            => $s->get('mail_from_address', config('mail.from.address')),
            'mail.from.name'               => $s->get('mail_from_name', config('mail.from.name')),
        ]);
    }

    /**
     * Update settings for a specific group only.
     */
    public function updateGroup(Request $request, string $group): RedirectResponse
    {
        if (! in_array($group, self::GROUPS)) {
            abort(404, 'Unknown settings group.');
        }

        $service = app(SettingService::class);

        foreach ($request->except(['_token', '_method']) as $key => $value) {
            if ($request->hasFile($key)) {
                $existingPath = Setting::where('key', $key)->value('value');
                if ($existingPath) {
                    Storage::disk('public')->delete($existingPath);
                }
                $value = $request->file($key)->store('settings', 'public');
            }

            $service->set($key, $value, $group);
        }

        $service->clearCache();

        return redirect()->route('admin.settings.index')
            ->with('success', ucfirst($group) . ' settings saved successfully.');
    }
}
