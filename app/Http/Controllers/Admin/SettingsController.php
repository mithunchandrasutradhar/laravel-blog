<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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
        $settings = [];

        foreach (self::GROUPS as $group) {
            $settings[$group] = Setting::group($group);
        }

        return view('admin.settings.index', compact('settings'));
    }

    /**
     * Update all settings from the form (flat list, all groups at once).
     */
    public function update(Request $request): RedirectResponse
    {
        $input = $request->except(['_token', '_method']);

        foreach ($input as $key => $value) {
            // Determine the group from a "group__key" naming convention used in
            // the view form fields, e.g. "general__site_name" → group=general, key=site_name
            if (str_contains($key, '__')) {
                [$group, $settingKey] = explode('__', $key, 2);
            } else {
                $group      = 'general';
                $settingKey = $key;
            }

            // Handle file uploads stored under the settings key
            if ($request->hasFile($key)) {
                $value = $request->file($key)->store('settings', 'public');
            }

            Setting::set($settingKey, $value, $group);
        }

        Setting::flushCache();

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

        foreach ($request->except(['_token', '_method']) as $key => $value) {
            // Handle logo / favicon uploads
            if ($request->hasFile($key)) {
                $existingPath = Setting::get($key);
                if ($existingPath) {
                    Storage::disk('public')->delete($existingPath);
                }

                $value = $request->file($key)->store('settings', 'public');
            }

            Setting::set($key, $value, $group);
        }

        Setting::flushCache();

        return redirect()->route('admin.settings.index')
            ->with('success', ucfirst($group) . ' settings saved successfully.');
    }
}
