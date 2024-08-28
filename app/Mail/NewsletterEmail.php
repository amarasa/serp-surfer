<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewsletterEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $htmlContent;
    public $cssContent;

    public function __construct($htmlContent, $cssContent)
    {
        $this->htmlContent = $htmlContent;
        $this->cssContent = $cssContent;
    }

    public function build()
    {
        return $this->view('emails.newsletter')
            ->with([
                'htmlContent' => $this->htmlContent,
                'cssContent' => $this->cssContent,
            ])
            ->subject('Your Newsletter')
            ->html($this->htmlContent);
    }
}
