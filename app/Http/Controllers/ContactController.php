<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function submit(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'message' => 'required|string',
        ]);

        // Send the email using Postmark with a Blade view
        Mail::send('emails.contact', ['name' => $data['name'], 'email' => $data['email'], 'messageContent' => $data['message']], function ($message) use ($data) {
            $message->from($data['email'], $data['name'])
                ->to('angelo.marasa@hrefcreative.com')
                ->subject('SERP Surfer - Contact Request');
        });

        return back()->with('success', 'Your message has been sent successfully!');
    }
}
