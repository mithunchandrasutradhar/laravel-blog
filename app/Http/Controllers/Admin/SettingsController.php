<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;   // still used for index() pluck and updateGroup file lookup
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
                // No new file uploaded — preserve whatever is already stored.
                continue;
            }

            // Use SettingService so the correct cache prefix ('setting.') is busted.
            $service->set($settingKey, $value, $group);
        }

        $service->clearCache();

        return redirect()->route('admin.settings.index')
            ->with('success', 'Settings saved successfully.');
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
