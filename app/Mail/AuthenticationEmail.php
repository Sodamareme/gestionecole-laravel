<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

use Barryvdh\DomPDF\Facade\Pdf;
class AuthenticationEmail extends Mailable
{
    use Queueable, SerializesModels;

    protected $user;
    protected $password;
    protected $qrCode;

    public function __construct($user, $password, $qrCode)
    {
        $this->user = $user;
        $this->password = $password;
        $this->qrCode = $qrCode; // Assigner correctement la variable QR Code
    }

    public function build()
    {
        $qrCode = $this->qrCode; // Utilisation du QR Code fourni
    
        // Générer le PDF avec la vue correspondante
        $pdf = PDF::loadView('pdfs.qr_code', [
            'user' => $this->user,
            'qrCode' => $qrCode,
        ]);
    
        return $this->view('emails.authentication')
            ->with([
                'user' => $this->user,
                'password' => $this->password,
            ])
            ->attachData($pdf->output(), 'qr_code.pdf', [
                'mime' => 'application/pdf',
            ]);
    }
}
