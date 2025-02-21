<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mail\ContactMessage;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function sendEmail(Request $request)
    {
        // Validate the form input
        $validated = $request->validate([
            'username' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string|max:15',
            'message' => 'required|string',
        ]);

        $data = $request->all();
        $recipients = ['vhandrade90@gmail.com', 'wlsfernandes@gmail.com'];
        Mail::to($recipients)->send(new ContactMessage($data));

        return redirect()->back()
            ->with('success', 'Your message has been sent! An WiVor Member will reach you out');


    }
}
