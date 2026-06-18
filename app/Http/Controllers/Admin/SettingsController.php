<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\ActivityLogger;
use App\Services\SettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingsController extends Controller
{
    private const GROUPS = [
        'general',
        'seo',
        'social',
        'mail',
        'appearance',
        'integrations',
        'advanced',
    ];

    public function index(): View
    {
        abort_if(! auth()->user()->hasPermissionTo('settings.view'), 403);

        $settings = Setting::pluck('value', 'key')->toArray();

        return view('admin.settings.index', compact('settings'));
    }

    private const ALLOWED_KEYS = [
        // General
        'site_name', 'site_tagline', 'site_description', 'site_email', 'site_phone',
        'site_address', 'posts_per_page', 'date_format', 'time_format', 'timezone',
        'language', 'maintenance_mode', 'logo', 'favicon', 'logo_height', 'logo_width',
        'contact_email', 'contact_phone', 'contact_address', 'terms_page', 'privacy_policy_page',
        // SEO
        'default_meta_title', 'default_meta_description', 'meta_title', 'meta_description',
        'meta_keywords', 'seo_keywords', 'google_analytics_id', 'ga4_measurement_id',
        'google_tag_manager_id', 'google_search_console', 'robots', 'robots_txt',
        'canonical_url', 'og_image', 'og_default_image', 'twitter_card', 'twitter_site',
        'structured_data',
        // Social
        'facebook_url', 'twitter_url', 'instagram_url', 'linkedin_url',
        'youtube_url', 'github_url', 'pinterest_url',
        // Mail
        'mail_mailer', 'mail_host', 'mail_port', 'mail_username', 'mail_password',
        'mail_encryption', 'mail_from_address', 'mail_from_name', 'newsletter_from_name',
        // Appearance
        'theme', 'primary_color', 'secondary_color', 'font_family',
        'custom_css', 'custom_js', 'header_scripts', 'footer_scripts',
        // Comments
        'comments_enabled', 'comments_moderation', 'comments_guest_allowed',
        'comments_per_page', 'recaptcha_enabled', 'recaptcha_site_key', 'recaptcha_secret_key',
        'close_comments_after', 'max_comment_length', 'notify_on_comment',
        // About
        'about_title', 'about_content', 'about_mission', 'about_values', 'about_image',
        // Integrations / Advanced
        'social_login_enabled', 'maintenance_message',
    ];

    public function update(Request $request): RedirectResponse
    {
        abort_if(! auth()->user()->hasPermissionTo('settings.update'), 403);

        $service = app(SettingService::class);

        $fileOnlyFields = ['logo', 'favicon', 'og_image', 'about_image'];
        $skipIfBlank    = ['mail_password'];

        $input = $request->except(['_token', '_method', 'section']);

        foreach ($input as $key => $value) {
            if (str_contains($key, '__')) {
                [$group, $settingKey] = explode('__', $key, 2);
            } else {
                $group      = 'general';
                $settingKey = $key;
            }

            // Only persist known, whitelisted setting keys
            if (! in_array($settingKey, self::ALLOWED_KEYS, true)) {
                continue;
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

        $section = $request->input('section', 'general');
        ActivityLogger::log('settings.updated', "Updated settings ({$section} section)", ['section' => $section]);

        return redirect()->route('admin.settings.index')
            ->with('success', 'Settings saved successfully.');
    }

    public function sendTestEmail(Request $request): JsonResponse
    {
        abort_if(! auth()->user()->hasPermissionTo('settings.update'), 403);

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

    public function updateGroup(Request $request, string $group): RedirectResponse
    {
        abort_if(! auth()->user()->hasPermissionTo('settings.update'), 403);

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

    private function applyMailConfig(): void
    {
        $s = app(SettingService::class);

        $host = $s->get('mail_host');
        if (! $host) {
            return;
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
}
