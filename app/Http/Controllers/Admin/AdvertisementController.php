<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAdvertisementRequest;
use App\Http\Requests\Admin\UpdateAdvertisementRequest;
use App\Models\Advertisement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdvertisementController extends Controller
{
    /**
     * Admin ads per page.
     */
    private const PER_PAGE = 15;

    /**
     * Available ad positions – keep in sync with the Advertisement model.
     *
     * @var array<string>
     */
    private const POSITIONS = [
        'header',
        'sidebar-top',
        'sidebar-bottom',
        'in-content',
        'footer',
        'popup',
    ];

    /**
     * Display a listing of advertisements.
     */
    public function index(Request $request): View
    {
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

    /**
     * Show the form for creating a new advertisement.
     */
    public function create(): View
    {
        $positions = self::POSITIONS;

        return view('admin.advertisements.create', compact('positions'));
    }

    /**
     * Store a new advertisement.
     */
    public function store(StoreAdvertisementRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('advertisements', 'public');
        }

        Advertisement::create($data);

        return redirect()->route('admin.advertisements.index')
            ->with('success', 'Advertisement created successfully.');
    }

    /**
     * Show an advertisement detail (admin view).
     */
    public function show(Advertisement $advertisement): RedirectResponse
    {
        return redirect()->route('admin.advertisements.edit', $advertisement);
    }

    /**
     * Show the edit form for an advertisement.
     */
    public function edit(Advertisement $advertisement): View
    {
        $positions = self::POSITIONS;

        return view('admin.advertisements.edit', compact('advertisement', 'positions'));
    }

    /**
     * Update an advertisement.
     */
    public function update(UpdateAdvertisementRequest $request, Advertisement $advertisement): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            if ($advertisement->image) {
                Storage::disk('public')->delete($advertisement->image);
            }

            $data['image'] = $request->file('image')->store('advertisements', 'public');
        }

        $advertisement->update($data);

        return redirect()->route('admin.advertisements.index')
            ->with('success', 'Advertisement updated successfully.');
    }

    /**
     * Toggle the active/inactive status of an advertisement.
     */
    public function toggle(Advertisement $advertisement): RedirectResponse
    {
        $advertisement->update(['is_active' => ! $advertisement->is_active]);

        return back()->with('success', 'Advertisement status updated.');
    }

    /**
     * Delete an advertisement.
     */
    public function destroy(Advertisement $advertisement): RedirectResponse
    {
        if ($advertisement->image) {
            Storage::disk('public')->delete($advertisement->image);
        }

        $advertisement->delete();

        return redirect()->route('admin.advertisements.index')
            ->with('success', 'Advertisement deleted successfully.');
    }
}
