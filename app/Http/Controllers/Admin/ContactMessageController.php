<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactMessageController extends Controller
{
    public function index(Request $request): View
    {
        abort_if(! auth()->user()->hasPermissionTo('contact_messages.viewAny'), 403);

        $query = ContactMessage::latest();

        // Keyword search across name, email, subject
        if ($search = $request->input('search')) {
            $s = str_replace(['%', '_', '\\'], ['\\%', '\\_', '\\\\'], $search);
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('subject', 'like', "%{$s}%");
            });
        }

        // Date range
        if ($from = $request->input('date_from')) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->input('date_to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        // Read / unread filter
        if ($request->input('status') === 'unread') {
            $query->where('is_read', false);
        } elseif ($request->input('status') === 'read') {
            $query->where('is_read', true);
        }

        $messages     = $query->paginate(20)->withQueryString();
        $totalUnread  = ContactMessage::unread()->count();

        return view('admin.contact-messages.index', compact('messages', 'totalUnread'));
    }

    public function show(ContactMessage $contactMessage): View
    {
        abort_if(! auth()->user()->hasPermissionTo('contact_messages.viewAny'), 403);

        if (! $contactMessage->is_read) {
            $contactMessage->markAsRead();
        }

        return view('admin.contact-messages.show', compact('contactMessage'));
    }

    public function destroy(ContactMessage $contactMessage): RedirectResponse
    {
        abort_if(! auth()->user()->hasPermissionTo('contact_messages.delete'), 403);

        ActivityLogger::log(
            'contact_message.deleted',
            "Deleted contact message from {$contactMessage->name} ({$contactMessage->email})",
            ['subject' => $contactMessage->subject],
            $contactMessage
        );

        $contactMessage->delete();

        return back()->with('success', 'Message deleted.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        abort_if(! auth()->user()->hasPermissionTo('contact_messages.delete'), 403);

        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return back()->with('error', 'No messages selected.');
        }

        $count = ContactMessage::whereIn('id', $ids)->count();
        ContactMessage::whereIn('id', $ids)->delete();

        ActivityLogger::log(
            'contact_message.bulk_deleted',
            "Bulk deleted {$count} contact message(s)",
            ['ids' => $ids]
        );

        return back()->with('success', "{$count} message(s) deleted.");
    }
}
