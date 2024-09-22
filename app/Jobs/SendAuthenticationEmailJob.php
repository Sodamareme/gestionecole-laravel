<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\AuthenticationEmail;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory; // Importez la classe Firebase

class SendAuthenticationEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $userId; // Stocker l'ID de l'utilisateur
    protected $password;

    public function __construct($userId, $password)
    {
        $this->userId = $userId; // Enregistrez l'ID de l'utilisateur
        $this->password = $password;
    }

    public function handle()
    {
        try {
            $firebase = (new Factory)
                ->withServiceAccount(config('firebase.credentials'))
                ->withDatabaseUri(config('firebase.database_url'));
    
            $database = $firebase->createDatabase();
            $userData = $database->getReference('users/' . $this->userId)->getValue();
    
            Log::info('Données utilisateur récupérées : ', ['userData' => $userData]);
    
            // Assurez-vous que les données utilisateur sont disponibles
            if (is_null($userData) || !isset($userData['email'])) {
                Log::error('Utilisateur non trouvé pour l\'ID : ' . $this->userId);
                throw new \Exception('Utilisateur introuvable.');
            }
    
            // Créer un objet utilisateur pour l'envoi d'e-mail
            $user = (object) $userData;
    
            Mail::to($user->email)->send(new AuthenticationEmail($user, $this->password));
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'envoi de l\'email : ' . $e->getMessage());
            throw $e; // Relancez l'exception pour que Laravel gère la file d'attente correctement
        }
    }
    
    
}
