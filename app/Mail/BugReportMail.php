<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class BugReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $email;
    public $message;
    public $attachment;

    /**
     * Create a new message instance.
     *
     * @param string $name
     * @param string $email
     * @param string $message
     * @param UploadedFile|null $attachment
     */
    public function __construct($name, $email, $message, $attachment = null)
    {
        $this->name = $name;
        $this->email = $email;
        $this->message = $message;
        $this->attachment = $attachment;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        Log::info('BUG_REPORT_EMAIL: ' . env('BUG_REPORT_EMAIL'));


        $email = $this->from($this->email)
            ->to('angelo.marasa@hrefcreative.com')  // Ensure this is correctly set
            //  ->to(env('BUG_REPORT_EMAIL'))  // Ensure this is correctly set
            ->subject('Bug Report Submission')
            ->view('emails.bug_report')
            ->with([
                'name' => $this->name,
                'messageContent' => $this->message,
            ]);

        if ($this->attachment instanceof UploadedFile) {
            $email->attach($this->attachment->getRealPath(), [
                'as' => $this->attachment->getClientOriginalName(),
                'mime' => $this->attachment->getMimeType(),
            ]);
        }

        return $email;
    }
}
