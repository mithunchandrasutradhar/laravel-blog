<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        $notifications = auth()->user()->notifications()->paginate(20);

        return view('admin.notifications.index', compact('notifications'));
    }

    public function markAllRead(): RedirectResponse
    {
        auth()->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'All notifications marked as read.');
    }

    public function markRead(string $id): RedirectResponse
    {
        $notification = auth()->user()->notifications()->where('id', $id)->first();

        if ($notification && ! $notification->read_at) {
            $notification->markAsRead();
        }

        return back();
    }
}
