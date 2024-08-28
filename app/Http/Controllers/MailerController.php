<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mail\BugReportMail;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewsletterEmail;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;

class MailerController extends Controller
{
    public function submitBugReport(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'message' => 'required|string',
            'screenshots.*' => 'nullable|file|mimes:jpg,png,jpeg,gif,pdf|max:51200',
        ]);

        $name = $request->input('name');
        $email = $request->input('email');
        $message = $request->input('message');
        $attachment = $request->file('screenshots')[0] ?? null;

        \Log::info('Processing attachment:', ['attachment' => $attachment]);

        Mail::to(env('BUG_REPORT_EMAIL'))->send(new BugReportMail($name, $email, $message, $attachment));

        return response()->json(['message' => 'Bug report submitted successfully.']);
    }

    public function sendEmail(Request $request)
    {
        $htmlContent = $request->input('html');
        $cssContent = $request->input('css');

        // Combine HTML and CSS into one inline style (using a package or custom logic)

        $user = auth()->user(); // Assuming you're sending the email to the logged-in user

        Mail::to($user->email)->send(new NewsletterEmail($htmlContent, $cssContent));

        return response()->json(['success' => 'Email sent successfully!']);
    }
}
