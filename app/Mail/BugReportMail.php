<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Http\UploadedFile;

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
        $email = $this->from($this->email)
            ->subject('Bug Report Submission')
            ->view('emails.bug_report')
            ->with([
                'name' => $this->name,
                'messageContent' => $this->message,
            ]);

        // Attach the first file only, if it exists and is valid
        if ($this->attachment instanceof UploadedFile) {
            \Log::info('Attaching file:', ['name' => $this->attachment->getClientOriginalName()]);
            $email->attach($this->attachment->getRealPath(), [
                'as' => $this->attachment->getClientOriginalName(),
                'mime' => $this->attachment->getMimeType(),
            ]);
        } else {
            \Log::error('No valid file to attach', ['attachment' => $this->attachment]);
        }

        return $email;
    }
}
