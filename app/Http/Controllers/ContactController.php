<?php

namespace App\Http\Controllers;

use App\Mail\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

/** Handles messages submitted through the public WivorPhotos contact form. */
class ContactController extends Controller
{
    /** Validate and deliver one public contact message to the configured support address. */
    public function sendEmail(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:15'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        Mail::to(config('contact.email'))->send(new ContactMessage($validated));

        return redirect()->back()
            ->with('success', 'Your message has been sent. A WivorPhotos team member will contact you.');
    }
}
