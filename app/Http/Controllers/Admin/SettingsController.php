<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
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
