<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Models\ContactMessage;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\ContactFormSubmission;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ContactController extends Controller
{
    /**
     * Display the contact form.
     */
    public function index(): View
    {
        $contactEmail = Setting::get('contact_email', config('mail.from.address'));
        $contactPhone = Setting::get('contact_phone');
        $contactAddress = Setting::get('contact_address');

        return view('pages.contact', compact('contactEmail', 'contactPhone', 'contactAddress'));
    }

    /**
     * Save the submitted contact message and dispatch a notification email.
     */
    public function store(ContactRequest $request): RedirectResponse
    {
        // Persist the message
        $message = ContactMessage::create([
            'name'       => $request->name,
            'email'      => $request->email,
            'subject'    => $request->subject,
            'message'    => $request->message,
            'ip_address' => $request->ip(),
        ]);

        // Send notification email to the configured admin inbox
        try {
            $adminEmail = Setting::get('contact_email', config('mail.from.address'));
            Mail::send('emails.contact-notification', ['contactMessage' => $message], function ($mail) use ($message, $adminEmail) {
                $mail->to($adminEmail)
                     ->replyTo($message->email, $message->name)
                     ->subject('New Contact Message: ' . $message->subject);
            });

            // Auto-reply to the sender
            Mail::send('emails.contact-autoreply', ['contactMessage' => $message], function ($mail) use ($message) {
                $mail->to($message->email, $message->name)
                     ->subject('We received your message – ' . Setting::get('site_name', config('app.name')));
            });
        } catch (\Exception $e) {
            logger()->error('Contact form mail failed: ' . $e->getMessage());
        }

        // Dispatch a database notification to every admin user
        try {
            User::role('admin')->get()->each(
                fn (User $admin) => $admin->notify(new ContactFormSubmission($message))
            );
        } catch (\Exception $e) {
            logger()->error('Contact notification dispatch failed: ' . $e->getMessage());
        }

        ActivityLogger::log(
            'contact.received',
            "Contact form submission from {$message->name} ({$message->email})",
            ['subject' => $message->subject],
            $message
        );

        return redirect()->route('contact')
            ->with('success', 'Your message has been sent. We will get back to you shortly!');
    }
}
