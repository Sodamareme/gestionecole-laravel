<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ReferentielService;
use Illuminate\Support\Facades\Log;
use App\Models\Competence;
use App\Models\Module;
class ReferentielController extends Controller
{
    protected $referentielService;

    public function __construct(ReferentielService $referentielService)
    {
        $this->referentielService = $referentielService;
    }

    public function store(Request $request)
    {
        // Decode the JSON data
        $jsonData = json_decode($request->input('data'), true);

        if (is_null($jsonData)) {
            return response()->json(['message' => 'Invalid JSON data'], 400);
        }
            // Vérifiez si le référentiel existe déjà
            $existingReferentiel = $this->referentielService->findByCode($jsonData['code']);
            if ($existingReferentiel) {
                return response()->json(['message' => 'Un référentiel avec ce code existe déjà.'], 400);
            }
        $photoPath = null;

        // Check if a photo is uploaded
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $fileName = time() . '_' . $file->getClientOriginalName();
            
            // Upload to Firebase Storage
            $storage = app('firebase.storage')->getBucket();
            $storage->upload(
                fopen($file->getPathname(), 'r'),
                ['name' => 'photos/' . $fileName]
            );

            // Generate URL for the uploaded photo
            $photoPath = 'https://firebasestorage.googleapis.com/v0/b/' . env('FIREBASE_PROJECT_ID') . '/o/photos%2F' . $fileName . '?alt=media';
        }

        // Merge JSON data and photo into the validation array
        $validatedData = array_merge($jsonData, [
            'photo' => $photoPath,
             // Par défaut, on assigne le statut "Actif" si aucun statut n'est fourni
             'statut' => $jsonData['statut'] ?? 'Actif',
           
        ]);

        // Validate the data
        $validatedData = validator($validatedData, [
            'code' => 'required|unique:referentiels,code',
            'libelle' => 'required|unique:referentiels,libelle',
            'description' => 'nullable|string',
            'photo' => 'nullable|string',
            'statut' => 'nullable|',
            'competences' => 'nullable|array',
            'competences.*.nom' => 'required|string',
            'competences.*.description' => 'required|string',
            'competences.*.duree_acquisition' => 'required|integer',
            'competences.*.type' => 'required|in:Back-end,Front-end',
            'competences.*.modules' => 'nullable|array',
            'competences.*.modules.*.nom' => 'required|string',
            'competences.*.modules.*.description' => 'required|string',
            'competences.*.modules.*.duree_acquisition' => 'required|integer',
        ])->validate();
       
        // Pass validated data to the service
        $referentiel = $this->referentielService->createReferentiel($validatedData);

        return response()->json($referentiel, 201);
    }
    public function getActiveReferentiels()
    {
        // Récupération des référentiels avec le statut "Actif"
        $activeReferentiels = $this->referentielService->getReferentielsByStatus('Actif');

        // Retourner la liste des référentiels actifs en réponse
        return response()->json($activeReferentiels, 200);
    }
    public function getReferentielsByStatus(Request $request)
{
     // Récupérer le statut depuis le corps de la requête
     $statut = $request->input('statut');

    //  if (!in_array($statut, ['Actif', 'Inactif', 'Archivé'])) {
    //      return response()->json(['message' => 'Statut invalide'], 400);
    //  }
 
     // Récupération des référentiels avec le statut spécifié
     $referentiels = $this->referentielService->getReferentielsByStatus($statut);
 
     // Retourner la liste des référentiels en réponse
     return response()->json($referentiels, 200);
}
public function getAllReferentiels()
{
    // Retrieve all référentiels
    $referentiels = $this->referentielService->getAllReferentiels();

    // Return the list of référentiels in response
    return response()->json($referentiels, 200);
}
public function getReferentielById($id)
{
    // Utiliser le service pour récupérer le référentiel par ID
    $referentiel = $this->referentielService->findById($id);

    // Vérifier si le référentiel existe
    if (!$referentiel) {
        return response()->json(['message' => 'Référentiel non trouvé'], 404);
    }

    // Retourner le référentiel trouvé
    return response()->json($referentiel, 200);
}

public function deleteReferentiel($id)
{
    // Utiliser le service pour récupérer le référentiel par ID
    $referentiel = $this->referentielService->findById($id);

    // Vérifier si le référentiel existe
    if (!$referentiel) {
        return response()->json(['message' => 'Référentiel non trouvé'], 404);
    }

    // Effectuer la suppression douce
    $this->referentielService->softDeleteReferentiel($id);

    return response()->json(['message' => 'Référentiel supprimé avec succès'], 200);
}
public function getArchivedReferentiels()
{
    // Utiliser le service pour récupérer les référentiels supprimés
    $archivedReferentiels = $this->referentielService->getArchivedReferentiels();

    // Retourner la liste des référentiels archivés en réponse
    return response()->json($archivedReferentiels, 200);
}




}
