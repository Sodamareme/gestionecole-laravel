<?php

namespace App\Http\Controllers;

use Kreait\Firebase\Factory;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode; // Import du QR Code
use Illuminate\Support\Facades\Bus; // Ajoutez ceci pour dispatcher les jobs
use App\Models\Apprenant; // Import du modèle Apprenant
use Illuminate\Support\Facades\Log;
use App\Jobs\SendMailJob;; 
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Note;
use App\Imports\ApprenantsImport;
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
     // Génération du QR code
        $qrCodeData = [
            'user_id' => $validatedData['user_id'],
            'matricule' => $matricule,
            'email' => $validatedData['email'],
            'referentiel_id' => $validatedData['referentiel_id'],
            'promotion_id' => $validatedData['promotion_id'],
        ];

        // Définir le nom de fichier pour le QR code
        $qrCodeFileName = 'qr_code_' . $matricule . '.png';
        $qrCodePath = storage_path('app/' . $qrCodeFileName);

     // Générer et sauvegarder le QR code en tant qu'image
        QrCode::format('png')->size(200)->generate(json_encode($qrCodeData), $qrCodePath);
        $qrCodeUrl = 'storage/qr_codes/' . $qrCodeFileName; // Chemin relatif à utiliser dans l'email

        // Envoi de l'e-mail
        SendMailJob::dispatch($validatedData['email'], $qrCodeUrl);


        $apprenantRef = $this->database->getReference('apprenants/' . $validatedData['user_id']);
        $apprenantRef->set([
            'email' => $validatedData['email'],
            'matricule' => $matricule,
            'referentiel_id' => $validatedData['referentiel_id'],
            'promotion_id' => $validatedData['promotion_id'],
            'photo' => $request->hasFile('photo') ? $request->file('photo')->store('photos_apprenants') : null,
            'qr_code' => $qrCodeUrl, // Utilisez le chemin relatif
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
    ]);
    $user = [
        'email' => $validatedData['email'],
    ];
}

// Dispatcher le job pour envoyer l'email avec le QR code
// SendQRCodeEmailJob::dispatch($validatedData['email'], $qrCodeUrl);
// SendMailJob::dispatch($validatedData['email'], $qrCodeUrl);
return response()->json([
    'message' => 'Apprenant inscrit avec succès.',
    'apprenant' => $apprenantRef->getValue(),
], 201);
    }
    public function import(Request $request)
    {
        // Validation pour s'assurer qu'un fichier est bien envoyé
        $request->validate([
            'file' => 'required|mimes:xlsx,csv'
        ]);

        // Importer les apprenants
        Excel::import(new ApprenantsImport, $request->file('file'));

        // Redirection après l'importation
        return redirect()->back()->with('success', 'Les apprenants ont été importés avec succès.');
    }


    public function index(Request $request)
    {
        // Récupérer les filtres envoyés dans la requête
        $referentielId = $request->query('referentiel_id');
        $statut = $request->query('statut'); // Peut être 'Abandon' ou 'Actif'

        // Commencer la requête de base pour lister tous les apprenants
        $query = Apprenant::query();

        // Appliquer le filtre par référentiel si disponible

        if ($referentielId) {
            $query->where('referentiel_id', $referentielId);
        }

        // Appliquer le filtre par statut si disponible
        if ($statut) {
            $query->where('statut', $statut); // Supposons que la colonne 'statut' existe dans le modèle Apprenant
        }

        // Exécuter la requête avec les filtres appliqués
        $apprenants = $query->get();

        // Retourner les résultats en JSON
        return response()->json($apprenants);
    }
    public function enregistrerNotes(Request $request)
{
    $validatedData = $request->validate([
        'notes.*.user_id' => 'required|exists:apprenants,user_id', // Vérification de l'existence du user_id
        'notes.*.note' => 'required|numeric', // Validation de la note
    ]);

    foreach ($validatedData['notes'] as $noteData) {
        // Trouver l'apprenant avec le user_id
        $apprenant = Apprenant::where('user_id', $noteData['user_id'])->first();

        if ($apprenant) {
            // Enregistrez la note
            Note::create([
                'apprenant_id' => $apprenant->id, // Utilisez l'ID de l'apprenant
                'note' => $noteData['note'],
            ]);
        } else {
            // Gérer le cas où l'apprenant n'est pas trouvé
            return response()->json(['message' => 'Apprenant non trouvé pour le user_id: ' . $noteData['user_id']], 404);
        }
    }

    return response()->json(['message' => 'Notes enregistrées avec succès.']);
}

  
}