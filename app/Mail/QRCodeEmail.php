<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class QRCodeEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $pdfFilePath;

    public function __construct($pdfFilePath)
    {
        $this->pdfFilePath = $pdfFilePath;
    }

    public function build()
    {
        return $this->view('emails.qr_code_email') // Créez une vue pour le corps de l'email
                    ->attach($this->pdfFilePath, [
                        'as' => 'carte_fidelite.pdf',
                        'mime' => 'application/pdf',
                    ]);
    }
}
