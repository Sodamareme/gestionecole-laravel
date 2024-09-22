<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Promotion;
use App\Models\Referentiel;
use Kreait\Firebase\Database as FirebaseDatabase;
use App\Models\Apprenant; // Import du modèle Apprenant
use App\Jobs\SendAuthenticationEmailJob; // Import du Job
use Illuminate\Support\Facades\Hash; // Pour le Hashage du mot de passe
use Illuminate\Support\Facades\Storage; // Pour la gestion des fichiers (photo)
use SimpleSoftwareIO\QrCode\Facades\QrCode; // Import du QR Code
use Illuminate\Support\Str; // Pour la génération de chaîne aléatoire (mot de passe)
use App\Models\User; // Import du modèle User

class PromotionController extends Controller
{
    protected $database;

    public function __construct(FirebaseDatabase $database)
    {
        $this->database = $database;
    }

    public function store(Request $request)
    {
        // Validation des données
        $validatedData = $request->validate([
            'libelle' => 'required|string|max:255',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'duree' => 'required|integer',
            'etat' => 'required|in:Actif,Cloturé,Inactif',
            'photo' => 'nullable|image|max:2048',
            'referentiels' => 'nullable|array',
            'referentiels.*' => 'string', // ID en chaîne
        ]);
    
        // Gestion de l'upload de la photo
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('promotions', 'public');
            $validatedData['photo'] = $photoPath;
        }
    
        // Enregistrement de la promotion dans Firebase
        $promotionData = collect($validatedData)->except('referentiels')->toArray();
        $promotion = $this->database->getReference('promotions')->push($promotionData);
    
        // Récupération des informations des référentiels
        $referentielsData = [];
        if (!empty($validatedData['referentiels'])) {
            foreach ($validatedData['referentiels'] as $referentielId) {
                $referentielRef = $this->database->getReference('referentiels/' . $referentielId);
                $referentielInfo = $referentielRef->getValue();
                if ($referentielInfo) {
                    $referentielsData[$referentielId] = $referentielInfo;
                }
            }
            // Enregistrement des référentiels avec leurs informations
            $this->database->getReference('promotions/' . $promotion->getKey() . '/referentiels')->set($referentielsData);
        }
    
        return response()->json($promotion->getValue(), 201);
    }
    
    

    public function index()
    {
        return Promotion::all();
    }

    public function update(Request $request, $id)
    {
        // Validation des données
        $validatedData = $request->validate([
            'libelle' => 'required|string|max:255',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'duree' => 'required|integer',
            'etat' => 'required|in:Actif,Cloturé,Inactif',
            'photo' => 'nullable|image|max:2048', // Max 2MB
        ]);

        // Gestion de l'upload de la photo
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('promotions', 'public');
            $validatedData['photo'] = $photoPath;
        }

        // Mise à jour dans Firebase
        $this->database->getReference('promotions/' . $id)->update($validatedData);

        return response()->json(['message' => 'Promotion mise à jour avec succès'], 200);
    }

    public function updateReferentiel(Request $request, $id)
{
    $validatedData = $request->validate([
        'referentiels' => 'required|array',
        'referentiels.*' => 'string', // ID en chaîne
        'action' => 'required|in:add,remove',
    ]);

    // Récupération de la promotion
    $promotionRef = $this->database->getReference('promotions/' . $id);
    $promotionData = $promotionRef->getValue();

    if (!$promotionData) {
        return response()->json(['message' => 'Promotion non trouvée.'], 404);
    }

    $currentReferentiels = $promotionData['referentiels'] ?? [];

    if ($validatedData['action'] === 'add') {
        foreach ($validatedData['referentiels'] as $referentielId) {
            $referentielRef = $this->database->getReference('referentiels/' . $referentielId);
            $referentielInfo = $referentielRef->getValue();
            if ($referentielInfo) {
                $currentReferentiels[$referentielId] = $referentielInfo; // Ajout de l'info du référentiel
            }
        }
        $this->database->getReference('promotions/' . $id . '/referentiels')->set($currentReferentiels);
    } elseif ($validatedData['action'] === 'remove') {
        $updatedReferentiels = array_diff_key($currentReferentiels, array_flip($validatedData['referentiels']));
        $this->database->getReference('promotions/' . $id . '/referentiels')->set($updatedReferentiels);
    }

    return response()->json(['message' => 'Référentiels mis à jour avec succès.'], 200);
}
public function updateEtat(Request $request, $id)
{
    $validatedData = $request->validate([
        'etat' => 'required|in:Actif,Cloturé,Inactif',
    ]);

    // Vérifiez si l'état est "Actif" et qu'il n'y a pas d'autres promotions en cours
    if ($validatedData['etat'] === 'Actif') {
        $promotionsEnCours = $this->database->getReference('promotions')->orderByChild('etat')->equalTo('Actif')->getValue();

        // S'il y a d'autres promotions en cours, renvoyez une erreur
        if (!empty($promotionsEnCours)) {
            return response()->json(['message' => 'Il ne peut y avoir qu\'une seule promotion en cours.'], 400);
        }
    }

    // Mise à jour de l'état de la promotion
    $promotionRef = $this->database->getReference('promotions/' . $id);
    $promotionRef->update(['etat' => $validatedData['etat']]);

    return response()->json(['message' => 'Statut de la promotion mis à jour avec succès.'], 200);
}
public function getPromotionEncours()
{
    // Récupérer la promotion avec l'état "Actif"
    $promotionEncours = $this->database->getReference('promotions')
        ->orderByChild('etat')
        ->equalTo('Actif')
        ->getValue();

    if (empty($promotionEncours)) {
        return response()->json(['message' => 'Aucune promotion active trouvée.'], 404);
    }

    return response()->json($promotionEncours, 200);
}
public function getReferentielsByPromotion($id)
{
    // Récupérer la promotion
    $promotionRef = $this->database->getReference('promotions/' . $id);
    $promotionData = $promotionRef->getValue();

    if (!$promotionData) {
        return response()->json(['message' => 'Promotion non trouvée.'], 404);
    }

    // Récupérer les référentiels associés à la promotion
    $referentiels = $promotionData['referentiels'] ?? [];

    // Filtrer les référentiels actifs
    $actifs = [];
    foreach ($referentiels as $referentielId => $referentielInfo) {
        if (isset($referentielInfo['statut']) && $referentielInfo['statut'] === 'Actif') {
            $actifs[$referentielId] = $referentielInfo; // Ajouter les détails du référentiel actif
        }
    }
   // Vérifier s'il y a des référentiels actifs
   if (empty($actifs)) {
    return response()->json(['message' => 'Aucun référentiel actif associé à cette promotion.'], 200);
}

    return response()->json($actifs, 200);
}
public function getPromotionStats($id)
{
    // Récupérer la promotion
    $promotionRef = $this->database->getReference('promotions/' . $id);
    $promotionData = $promotionRef->getValue();

    if (!$promotionData) {
        return response()->json(['message' => 'Promotion non trouvée.'], 404);
    }

    // Initialiser les compteurs
    $totalApprenants = 0;
    $apprenantsActifs = 0;
    $apprenantsInactifs = 0;
    $referentielsActifs = [];

    // Récupérer les référentiels associés à la promotion
    $referentiels = $promotionData['referentiels'] ?? [];

    foreach ($referentiels as $referentielId => $referentielInfo) {
        // Filtrer les référentiels actifs
        if (isset($referentielInfo['statut']) && $referentielInfo['statut'] === 'Actif') {
            $referentielsActifs[$referentielId] = [
                'id' => $referentielId,
                'libelle' => $referentielInfo['libelle'],
                'nbre_apprenant' => isset($referentielInfo['apprenants']) ? count($referentielInfo['apprenants']) : 0, // Compter le nombre d'apprenants
            ];

            // Compter les apprenants actifs et inactifs
            if (isset($referentielInfo['apprenants'])) {
                foreach ($referentielInfo['apprenants'] as $apprenant) {
                    if ($apprenant['statut'] === 'Actif') {
                        $apprenantsActifs++;
                    } else {
                        $apprenantsInactifs++;
                    }
                }
            }
            
            // Mise à jour du total d'apprenants
            $totalApprenants += $referentielsActifs[$referentielId]['nbre_apprenant'];
        }
    }

    // Rassembler les statistiques
    $stats = [
        'promotion' => $promotionData,
        'total_apprenants' => $totalApprenants,
        'nbre_apprenant_actif' => $apprenantsActifs,
        'nbre_apprenant_inactif' => $apprenantsInactifs,
        'referentiels_actifs' => $referentielsActifs,
    ];

    return response()->json($stats, 200);
}
public function cloturerPromotion($id)
{
    // Récupérer la promotion
    $promotionRef = $this->database->getReference('promotions/' . $id);
    $promotionData = $promotionRef->getValue();

    if (!$promotionData) {
        return response()->json(['message' => 'Promotion non trouvée.'], 404);
    }

    // Vérifier si la date de fin est échue
    $dateFin = $promotionData['date_fin'] ?? null;
    if (!$dateFin || strtotime($dateFin) > time()) {
        return response()->json(['message' => 'La date de fin n\'est pas échue.'], 400);
    }

    // Mettre à jour le statut de la promotion à "Clôturée"
    $this->database->getReference('promotions/' . $id . '/statut')->set('Clôturée');

    // Envoyer les relevés de notes aux apprenants
    $this->envoyerRelevesNotes($promotionData['apprenants'] ?? []);

    return response()->json(['message' => 'Promotion clôturée avec succès.'], 200);
}

// Méthode pour envoyer les relevés de notes
private function envoyerRelevesNotes($apprenants)
{
    foreach ($apprenants as $apprenant) {
        // Logique pour envoyer le relevé de notes à chaque apprenant
        // Vous pouvez intégrer une logique d'email ou de notification ici
    }
}
protected function verifierStatutPromotion($promotionData)
{
    if (isset($promotionData['statut']) && $promotionData['statut'] === 'Clôturée') {
        return response()->json(['message' => 'Cette promotion est clôturée. Aucune modification n\'est autorisée.'], 403);
    }
}
// public function inscrireApprenant(Request $request)
// {
//     // Validation des données d'entrée
//     $validatedData = $request->validate([
//         'user_id' => 'required|string',
//         'referentiel_id' => 'required|string',
//         'promotion_id' => 'required|string',
//         'photo' => 'nullable|image', // Vérifier si une photo est fournie
//         'email' => 'required|email|unique:users,email',
//     ]);

//     // Création d'un compte utilisateur
//     $defaultPassword = Str::random(8); // Génération d'un mot de passe par défaut
//     $user = User::create([
//         'email' => $validatedData['email'],
//         'password' => Hash::make($defaultPassword),
//         'role' => 'apprenant', // ou tout autre rôle nécessaire
//     ]);

//     // Génération du matricule (ex: APPR0001)
//     $matricule = 'APPR' . str_pad($user->id, 4, '0', STR_PAD_LEFT);

//     // Génération du QR code contenant les informations de l'apprenant
//     $qrCodeData = [
//         'user_id' => $user->id,
//         'matricule' => $matricule,
//         'email' => $user->email,
//         'referentiel_id' => $validatedData['referentiel_id'],
//         'promotion_id' => $validatedData['promotion_id'],
//     ];
//     $qrCode = QrCode::format('png')->size(200)->generate(json_encode($qrCodeData));

//     // Enregistrer l'apprenant avec son référentiel et promotion
//     $apprenant = Apprenant::create([
//         'user_id' => $user->id,
//         'referentiel_id' => $validatedData['referentiel_id'],
//         'promotion_id' => $validatedData['promotion_id'],
//         'matricule' => $matricule,
//         'photo' => $request->hasFile('photo') ? $request->file('photo')->store('photos_apprenants') : null,
//         'qr_code' => $qrCode, // Enregistrer l'image du QR code ou le lien
//     ]);

//     // Envoi de l'email avec le login et le mot de passe par défaut à l'apprenant via un Job
//     dispatch(new SendAuthenticationEmailJob($user, $defaultPassword));
   
//     return response()->json([
//         'message' => 'Apprenant inscrit avec succès. Un email a été envoyé avec les informations de connexion.',
//         'apprenant' => $apprenant,
//     ], 201);
// }


// Méthode pour gérer l'upload de la photo
private function uploadPhoto($photo)
{
    $path = 'photos/apprenants/' . time() . '_' . $photo->getClientOriginalName();
    $photo->storeAs('public', $path); // Stocker dans le dossier public
    return $path; // Retourner le chemin de l'image
}


}
