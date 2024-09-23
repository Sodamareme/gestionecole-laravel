<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class LoyaltyCardMail extends Mailable
{
    use Queueable, SerializesModels;

    
    public $user;
    public $qr_code_url; // Add this property

    public function __construct(User $user, $qr_code_url)
    {
        $this->user = $user;
        $this->qr_code_url = $qr_code_url; // Assign the QR code URL
    }

    public function build()
    {
        // Chemin du répertoire où les fichiers PDF seront enregistrés
        $directoryPath = 'public/loyalty_cards';

        // Vérifier si le répertoire existe, sinon le créer
        if (!Storage::exists($directoryPath)) {
            Storage::makeDirectory($directoryPath);
        }

        $qrCodePath = storage_path('app/public/qrcodes/' . $this->user->user_id . '.png');

        // Générer le PDF à partir de la vue 'emails.loyalty_card'
        $pdf = Pdf::loadView('emails.loyalty_card', [
            'user' => $this->user,
            'qr_code_url' => $qrCodePath // Pass QR code URL to view
        ]);

        // Définir le chemin complet du fichier PDF à enregistrer
        $pdfFileName = 'user_' . $this->user->id . '.pdf';
        $pdfPath = storage_path('app/' . $directoryPath . '/' . $pdfFileName);

        // Enregistrer le PDF sur le disque
        $pdf->save($pdfPath);
       
        // Chemin du fichier QR code
        
        return $this->view('emails.loyalty_card')
        ->with([
            'user' => $this->user,
            'qr_code_url' => $qrCodePath
        ])
        ->attach($pdfPath, [
            'as' => 'loyalty_card.pdf',
            'mime' => 'application/pdf'
        ])
        ->withSwiftMessage(function ($message) use ($pdfPath) {
            $message->getHeaders()->addTextHeader('X-Mailer', 'Laravel');
            // Supprimer le fichier après avoir vérifié que l'email est envoyé
            Storage::delete($pdfPath);
        });

    }
}
