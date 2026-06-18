<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAdvertisementRequest;
use App\Http\Requests\Admin\UpdateAdvertisementRequest;
use App\Models\Advertisement;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdvertisementController extends Controller
{
    private const PER_PAGE = 15;

    private const POSITIONS = [
        'header',
        'sidebar',
        'in-article',
        'footer',
    ];

    public function index(Request $request): View
    {
        abort_if(! auth()->user()->hasPermissionTo('advertisements.viewAny'), 403);

        $query = Advertisement::query();

        if ($request->filled('position')) {
            $query->atPosition($request->position);
        }

        if ($request->filled('type')) {
            $query->ofType($request->type);
        }

        if ($request->filled('status')) {
            $request->status === 'active' ? $query->active() : $query->where('is_active', false);
        }

        $advertisements = $query->latest()->paginate(self::PER_PAGE)->withQueryString();
        $positions      = self::POSITIONS;

        return view('admin.advertisements.index', compact('advertisements', 'positions'));
    }

    public function create(): View
    {
        abort_if(! auth()->user()->hasPermissionTo('advertisements.create'), 403);

        $positions = self::POSITIONS;

        return view('admin.advertisements.create', compact('positions'));
    }

    public function store(StoreAdvertisementRequest $request): RedirectResponse
    {
        abort_if(! auth()->user()->hasPermissionTo('advertisements.create'), 403);

        $data = $request->validated();
        unset($data['image']);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('advertisements', 'public');
        } elseif ($request->filled('media_path')) {
            $data['image'] = $request->input('media_path');
        }

        $ad = Advertisement::create($data);
        ActivityLogger::log('advertisement.created', "Advertisement \"{$ad->name}\" was created.", [], $ad);

        return redirect()->route('admin.advertisements.index')
            ->with('success', 'Advertisement created successfully.');
    }

    public function show(Advertisement $advertisement): RedirectResponse
    {
        abort_if(! auth()->user()->hasPermissionTo('advertisements.viewAny'), 403);

        return redirect()->route('admin.advertisements.edit', $advertisement);
    }

    public function edit(Advertisement $advertisement): View
    {
        abort_if(! auth()->user()->hasPermissionTo('advertisements.update'), 403);

        $positions = self::POSITIONS;

        return view('admin.advertisements.edit', compact('advertisement', 'positions'));
    }

    public function update(UpdateAdvertisementRequest $request, Advertisement $advertisement): RedirectResponse
    {
        abort_if(! auth()->user()->hasPermissionTo('advertisements.update'), 403);

        $data = $request->validated();
        unset($data['image']);

        if ($request->hasFile('image')) {
            if ($advertisement->image) {
                Storage::disk('public')->delete($advertisement->image);
            }
            $data['image'] = $request->file('image')->store('advertisements', 'public');
        } elseif ($request->filled('media_path')) {
            $data['image'] = $request->input('media_path');
        } elseif ($request->boolean('remove_image')) {
            if ($advertisement->image) {
                Storage::disk('public')->delete($advertisement->image);
            }
            $data['image'] = null;
        }

        $advertisement->update($data);
        ActivityLogger::log('advertisement.updated', "Advertisement \"{$advertisement->name}\" was updated.", [], $advertisement);

        return redirect()->route('admin.advertisements.index')
            ->with('success', 'Advertisement updated successfully.');
    }

    public function toggle(Advertisement $advertisement): RedirectResponse
    {
        abort_if(! auth()->user()->hasPermissionTo('advertisements.update'), 403);

        $advertisement->update(['is_active' => ! $advertisement->is_active]);
        $status = $advertisement->is_active ? 'activated' : 'deactivated';
        ActivityLogger::log('advertisement.toggled', "Advertisement \"{$advertisement->name}\" was {$status}.", [], $advertisement);

        return back()->with('success', 'Advertisement status updated.');
    }

    public function destroy(Advertisement $advertisement): RedirectResponse
    {
        abort_if(! auth()->user()->hasPermissionTo('advertisements.delete'), 403);

        $adName = $advertisement->name;
        if ($advertisement->image) {
            Storage::disk('public')->delete($advertisement->image);
        }
        $advertisement->delete();
        ActivityLogger::log('advertisement.deleted', "Advertisement \"{$adName}\" was deleted.");

        return redirect()->route('admin.advertisements.index')
            ->with('success', 'Advertisement deleted successfully.');
    }
}
