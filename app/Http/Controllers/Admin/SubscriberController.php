<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscriber;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class SubscriberController extends Controller
{
    private const PER_PAGE = 15;

    public function index(Request $request): View
    {
        abort_if(! auth()->user()->hasPermissionTo('subscribers.viewAny'), 403);

        $query = Subscriber::query();

        if ($request->filled('verified')) {
            if ($request->verified === '1') {
                $query->verified();
            } elseif ($request->verified === '0') {
                $query->unverified();
            }
        }

        if ($request->filled('search')) {
            $s = str_replace(['%', '_', '\\'], ['\\%', '\\_', '\\\\'], $request->search);
            $query->where('email', 'LIKE', "%{$s}%");
        }

        $subscribers     = $query->latest()->paginate(self::PER_PAGE)->withQueryString();
        $totalCount      = Subscriber::count();
        $verifiedCount   = Subscriber::verified()->count();
        $unverifiedCount = $totalCount - $verifiedCount;

        return view('admin.subscribers.index', compact(
            'subscribers', 'totalCount', 'verifiedCount', 'unverifiedCount'
        ));
    }

    public function verify(Subscriber $subscriber): RedirectResponse
    {
        abort_if(! auth()->user()->hasPermissionTo('subscribers.viewAny'), 403);

        $subscriber->verify();
        ActivityLogger::log('subscriber.verified', "Subscriber \"{$subscriber->email}\" was manually verified.", [], $subscriber);

        return back()->with('success', 'Subscriber marked as verified.');
    }

    public function destroy(Subscriber $subscriber): RedirectResponse
    {
        abort_if(! auth()->user()->hasPermissionTo('subscribers.delete'), 403);

        $email = $subscriber->email;
        $subscriber->delete();
        ActivityLogger::log('subscriber.deleted', "Subscriber \"{$email}\" was removed.");

        return back()->with('success', 'Subscriber removed successfully.');
    }

    public function export(): StreamedResponse
    {
        abort_if(! auth()->user()->hasPermissionTo('subscribers.viewAny'), 403);

        $subscribers = Subscriber::orderBy('email')->get(['email', 'verified_at', 'created_at']);

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

            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['Email', 'Status', 'Verified At', 'Subscribed At']);

            foreach ($subscribers as $subscriber) {
                fputcsv($handle, [
                    $subscriber->email,
                    $subscriber->verified_at ? 'Verified' : 'Unverified',
                    $subscriber->verified_at?->toDateTimeString() ?? '',
                    $subscriber->created_at->toDateTimeString(),
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
