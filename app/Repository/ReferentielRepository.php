<?php

namespace App\Repository;

use Kreait\Firebase\Database;
use App\Models\Referentiel;
class ReferentielRepository implements ReferentielRepositoryInterface
{
    protected $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function create(array $data)
    {
        try {
            // Enregistrement dans Firebase Realtime Database
            $newRef = $this->database->getReference('referentiels')->push($data);
            return $newRef->getKey();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
 
// Dans ReferentielRepository.php
public function getByStatus($status)
{
    $referentiels = $this->database->getReference('referentiels')
                    ->orderByChild('statut')
                    ->equalTo($status)
                    ->getValue();

    return $referentiels ? $referentiels : [];
}
public function findByCode($code)
{
    return $this->database->getReference('referentiels')->orderByChild('code')->equalTo($code)->getValue();
}
public function findByStatus(string $statut)
{
    try {
        // Exemple de requête pour récupérer les référentiels par statut
        $referentiels = $this->database->getReference('referentiels')
            ->orderByChild('statut')
            ->equalTo($statut)
            ->getSnapshot()
            ->getValue();

        return $referentiels ? array_values($referentiels) : []; // Renvoie un tableau vide si aucun résultat
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

public function getAll()
{
    try {
        $referentiels = $this->database->getReference('referentiels')->getValue();
        return $referentiels ? array_values($referentiels) : []; // Return an empty array if no results
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}
public function findById($id)
{
    try {
        // Récupérer le référentiel via son ID
        $referentiel = $this->database->getReference('referentiels')->getChild($id)->getValue();
        return $referentiel ?: null; // Retourner null si non trouvé
    } catch (\Exception $e) {
        return null; // Gérer l'erreur si nécessaire
    }
}

public function update($id, array $data)
{
    try {
        $referentielRef = $this->database->getReference('referentiels')->getChild($id);
        
        // Mettre à jour le référentiel
        $referentielRef->update($data);

        return $referentielRef->getValue();
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

public function addCompetence($referentielId, array $competenceData)
{
    try {
        // Récupérer la référence du référentiel
        $referentielRef = $this->database->getReference('referentiels/' . $referentielId);

        // Ajouter la compétence au référentiel
        $competenceRef = $referentielRef->getChild('competences')->push($competenceData);

        return $competenceRef->getKey(); // Retourner l'ID de la compétence ajoutée
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

public function softDeleteCompetence($competenceId)
{
    try {
        // Récupérer la référence de la compétence
        $competenceRef = $this->database->getReference('competences/' . $competenceId);

        // Marquer la compétence comme supprimée (soft delete)
        $competenceRef->update(['deleted_at' => now()]); // Ajouter un champ 'deleted_at'

        return true; // Retourner true pour indiquer que la suppression a réussi
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

public function findByIdDelete($id)
{
    try {
        // Utilisez le modèle avec les suppressions douces pour récupérer le référentiel
        return Referentiel::withTrashed()->find($id); // Renvoie une instance de modèle
    } catch (\Exception $e) {
        return null; // Gérer l'erreur si nécessaire
    }
}
public function getArchived()
{
    try {
        // Récupérer tous les référentiels supprimés (soft deleted)
        return Referentiel::onlyTrashed()->get(); // Assurez-vous que le modèle utilise SoftDeletes
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}




}
