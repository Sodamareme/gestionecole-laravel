<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InscriptionApprenantMail extends Mailable
{
    use Queueable, SerializesModels;

    public $mailData;

    public function __construct($mailData)
    {
        $this->mailData = $mailData;
    }

    public function build()
    {
        return $this->subject('Inscription à la plateforme')
                    ->view('emails.inscription_apprenant')
                    ->with('mailData', $this->mailData)
                    ->attach($this->mailData['qr_code'], [
                        'as' => 'qrcode.png',
                        'mime' => 'image/png',
                    ]);
    }
}