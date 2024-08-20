<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class IndexingSuccessNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $url;
    public $submissionCount;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($url, $submissionCount)
    {
        $this->url = $url;
        $this->submissionCount = $submissionCount;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Your URL Has Been Successfully Indexed!')
            ->markdown('emails.indexing_success')
            ->with([
                'url' => $this->url,
                'submissionCount' => $this->submissionCount,
            ]);
    }
}
