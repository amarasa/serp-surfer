<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function submit(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'message' => 'required|string',
        ]);

        $data = $request->only(['name', 'email', 'message']);

        // Send the email using Postmark
        Mail::send([], [], function ($message) use ($data) {
            $message->from($data['email'], $data['name'])
                ->to('angelo.marasa@hrefcreative.com') // Replace with your email
                ->subject('SERP Surfer - Contact Request')
                ->setBody($data['message'], 'text/plain');
        });

        return back()->with('success', 'Your message has been sent successfully!');
    }
}
