<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AccountCreated extends Mailable
{
    use Queueable, SerializesModels;

    protected $qrCodeUrl;

    /**
     * Create a new message instance.
     *
     * @param string $qrCodeUrl
     */
    public function __construct($qrCodeUrl)
    {
        $this->qrCodeUrl = $qrCodeUrl;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.account_created')
                    ->with(['qrCodeUrl' => $this->qrCodeUrl]);
    }
}
