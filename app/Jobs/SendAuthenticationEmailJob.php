<?php
namespace App\Jobs;

use App\Mail\AuthenticationEmail; // Créez ce mailable
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Mail; 
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Dompdf\Dompdf;

class SendAuthenticationEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $password;
    protected $qrCode;

    public function __construct($user, $password, $qrCode)
    {
        $this->user = $user;
        $this->password = $password;
        $this->qrCode = $qrCode;
    }

    public function handle()
    {
        $email = $this->user->email;
        $password = $this->password;
    
        // Vérifiez et créez le dossier pour le PDF
        $pdfDirectory = storage_path('app/public/qrcodes');
        if (!file_exists($pdfDirectory)) {
            mkdir($pdfDirectory, 0755, true);
        }
    
        // Génération du PDF
        $dompdf = new Dompdf();
        $html = "
            <h1>Bienvenue, {$this->user->email}</h1>
            <p>Votre compte a été créé.</p>
            <p>Voici votre QR code :</p>
            <img src='" . asset($this->qrCode) . "' alt='QR Code' />
            <p>Votre mot de passe est : {$password}</p>
        ";
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
    
        // Sauvegarder le PDF dans un fichier
        $pdfOutput = $dompdf->output();
        $pdfPath = $pdfDirectory . '/' . uniqid() . '.pdf'; // Utilisez le chemin du dossier
        file_put_contents($pdfPath, $pdfOutput);
        
        Log::info('Envoi d\'e-mail à : ' . $email);
    
        // Envoyer l'e-mail avec le PDF en pièce jointe
        Mail::send([], [], function ($message) use ($email, $pdfPath) {
            $message->to($email)
                    ->subject('Authentification')
                    ->attach($pdfPath)
                    ->setBody('Veuillez trouver ci-joint votre QR code en format PDF.', 'text/html');
        });
        
        Log::info('PDF généré avec succès.');
    }
    
}

