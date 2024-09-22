<?php

// namespace App\Http\Controllers;

// use Kreait\Firebase\Factory;
// use Illuminate\Http\Request;
// use SimpleSoftwareIO\QrCode\Facades\QrCode; // Import du QR Code
// use Illuminate\Support\Facades\Bus; // Ajoutez ceci pour dispatcher les jobs
// use App\Jobs\SendAuthenticationEmailJob; // Import du job

// class ApprenantController extends Controller
// {
//     protected $database;

//     public function __construct()
//     {
//         $firebase = (new Factory)
//             ->withServiceAccount(config('firebase.credentials'))
//             ->withDatabaseUri(config('firebase.database_url'));

//         // Connexion à la base de données Firebase
//         $this->database = $firebase->createDatabase();
//     }

//     public function inscrireApprenant(Request $request)
//     {
//         // Validation des données d'entrée
//         $validatedData = $request->validate([
//             'user_id' => 'required|string',
//             'referentiel_id' => 'required|string',
//             'promotion_id' => 'required|string',
//             'photo' => 'nullable|image',
//             'email' => 'required|email|unique:users,email',
//         ]);

//         // Génération du matricule (ex: APPR0001)
//         $matricule = 'APPR' . str_pad(random_int(1, 9999), 4, '0', STR_PAD_LEFT);

//         // Génération du QR code
//         $qrCodeData = [
//             'user_id' => $validatedData['user_id'],
//             'matricule' => $matricule,
//             'email' => $validatedData['email'],
//             'referentiel_id' => $validatedData['referentiel_id'],
//             'promotion_id' => $validatedData['promotion_id'],
//         ];
//         $qrCode = QrCode::format('png')->size(200)->generate(json_encode($qrCodeData));

//         // Enregistrer l'apprenant dans Firebase
//         $apprenantRef = $this->database->getReference('apprenants/' . $validatedData['user_id']);
//         $apprenantRef->set([
//             'email' => $validatedData['email'],
//             'matricule' => $matricule,
//             'referentiel_id' => $validatedData['referentiel_id'],
//             'promotion_id' => $validatedData['promotion_id'],
//             'photo' => $request->hasFile('photo') ? $request->file('photo')->store('photos_apprenants') : null,
//             'qr_code' => $qrCode,
//         ]);
   
//         // Envoi de l'e-mail d'authentification
//         Bus::dispatch(new SendAuthenticationEmailJob($validatedData['email'], 'le_mot_de_passe')); // Remplacez 'le_mot_de_passe' par le mot de passe généré ou stocké

//         return response()->json([
//             'message' => 'Apprenant inscrit avec succès.',
//             'apprenant' => $apprenantRef->getValue(),
//         ], 201);
//     }
// }


namespace App\Http\Controllers;

use Kreait\Firebase\Factory;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode; // Import du QR Code
use Illuminate\Support\Facades\Bus; // Ajoutez ceci pour dispatcher les jobs
use App\Jobs\SendAuthenticationEmailJob; // Import du job
use Illuminate\Support\Facades\Log;
class ApprenantController extends Controller
{
    protected $database;

    public function __construct()
    {
        $firebase = (new Factory)
            ->withServiceAccount(config('firebase.credentials'))
            ->withDatabaseUri(config('firebase.database_url'));

        // Connexion à la base de données Firebase
        $this->database = $firebase->createDatabase();
    }

    public function inscrireApprenant(Request $request)
    {
        // Validation des données d'entrée
        $validatedData = $request->validate([
            'user_id' => 'required|string',
            'referentiel_id' => 'required|string',
            'promotion_id' => 'required|string',
            'photo' => 'nullable|image',
            'email' => 'required|email|unique:users,email',
        ]);
    
        // Génération du matricule (ex: APPR0001)
        $matricule = 'APPR' . str_pad(random_int(1, 9999), 4, '0', STR_PAD_LEFT);
    
        // Génération du QR code
        $qrCodeData = [
            'user_id' => $validatedData['user_id'],
            'matricule' => $matricule,
            'email' => $validatedData['email'],
            'referentiel_id' => $validatedData['referentiel_id'],
            'promotion_id' => $validatedData['promotion_id'],
        ];
        $qrCode = QrCode::format('png')->size(200)->generate(json_encode($qrCodeData));
    
        // Enregistrer l'apprenant dans Firebase
        $apprenantRef = $this->database->getReference('apprenants/' . $validatedData['user_id']);
        $apprenantRef->set([
            'email' => $validatedData['email'],
            'matricule' => $matricule,
            'referentiel_id' => $validatedData['referentiel_id'],
            'promotion_id' => $validatedData['promotion_id'],
            'photo' => $request->hasFile('photo') ? $request->file('photo')->store('photos_apprenants') : null,
            'qr_code' => $qrCode,
        ]);
    
        // Log pour vérifier l'enregistrement
        Log::info('Apprenant inscrit : ', ['user_id' => $validatedData['user_id'], 'data' => $apprenantRef->getValue()]);
    
        // Créer ou récupérer l'utilisateur
        $userRef = $this->database->getReference('users/' . $validatedData['user_id']);
        $user = $userRef->getValue();
    
        if (is_null($user)) {
            // Créer l'utilisateur s'il n'existe pas
            $userRef->set([
                'email' => $validatedData['email'],
                // Ajoutez d'autres champs si nécessaire
            ]);
            $user = [
                'email' => $validatedData['email'],
                // Ajoutez d'autres champs si nécessaire
            ];
        }
    
        // Envoi de l'e-mail d'authentification
        Bus::dispatch(new SendAuthenticationEmailJob((object)$user, 'le_mot_de_passe')); // Remplacez par le mot de passe généré
    
        return response()->json([
            'message' => 'Apprenant inscrit avec succès.',
            'apprenant' => $apprenantRef->getValue(),
        ], 201);
    }
    
}
