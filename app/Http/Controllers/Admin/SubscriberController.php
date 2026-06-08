<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class SubscriberController extends Controller
{
    /**
     * Admin subscribers per page.
     */
    private const PER_PAGE = 15;

    /**
     * Display a listing of subscribers.
     */
    public function index(Request $request): View
    {
        $query = Subscriber::query();

        // Filter by verified status
        if ($request->filled('status')) {
            if ($request->status === 'verified') {
                $query->verified();
            } else {
                $query->unverified();
            }
        }

        // Search by name or email
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('email', 'LIKE', "%{$q}%")
                    ->orWhere('name', 'LIKE', "%{$q}%");
            });
        }

        $subscribers   = $query->latest()->paginate(self::PER_PAGE)->withQueryString();
        $totalVerified = Subscriber::verified()->count();
        $totalAll      = Subscriber::count();

        return view('admin.subscribers.index', compact('subscribers', 'totalVerified', 'totalAll'));
    }

    /**
     * Delete a subscriber.
     */
    public function destroy(Subscriber $subscriber): RedirectResponse
    {
        $subscriber->delete();

        return back()->with('success', 'Subscriber removed successfully.');
    }

    /**
     * Export all verified subscribers as a CSV file.
     */
    public function export(): Response
    {
        $subscribers = Subscriber::verified()->orderBy('email')->get(['name', 'email', 'verified_at', 'created_at']);

        $filename = 'subscribers-' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($subscribers) {
            $handle = fopen('php://output', 'w');

            // BOM for Excel UTF-8 compatibility
            fwrite($handle, "\xEF\xBB\xBF");

            // Header row
            fputcsv($handle, ['Name', 'Email', 'Verified At', 'Subscribed At']);

            foreach ($subscribers as $subscriber) {
                fputcsv($handle, [
                    $subscriber->name ?? '',
                    $subscriber->email,
                    $subscriber->verified_at?->toDateTimeString() ?? '',
                    $subscriber->created_at->toDateTimeString(),
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
