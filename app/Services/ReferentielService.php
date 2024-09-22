<?php

namespace App\Services;

use App\Repository\ReferentielRepository;
use App\Models\Referentiel;
use Illuminate\Support\Facades\Log;
class ReferentielService
{
    protected $referentielRepository;

    public function __construct(ReferentielRepository $referentielRepository)
    {
        $this->referentielRepository = $referentielRepository;
    }

    public function createReferentiel(array $data)
    {
        // Create a new referentiel in the Firebase Realtime Database
        return $this->referentielRepository->create($data);
    }
 // Récupérer les référentiels actifs
// Dans ReferentielService.php
public function getReferentielsByStatus($status)
{
    return $this->referentielRepository->getByStatus($status);
}
public function findByCode($code)
{
    return $this->referentielRepository->findByCode($code);
}
public function getReferentielsByAllStatus(string $statut)
{
    return $this->referentielRepository->findByStatus($statut);
}

public function getAllReferentiels()
{
    // Fetch all référentiels from the repository
    return $this->referentielRepository->getAll();
}
public function findById($id)
{
    // Rechercher un référentiel par son ID dans la base de données Firebase
    return $this->referentielRepository->findById($id);
}

public function updateReferentiel($id, array $data)
{
    // Mettre à jour le référentiel dans la base de données
    return $this->referentielRepository->update($id, $data);
}
public function addCompetenceToReferentiel($referentielId, array $competenceData)
{
    // Logique pour ajouter une compétence au référentiel dans la base de données
    // Par exemple, en utilisant le référentielRepository
    return $this->referentielRepository->addCompetence($referentielId, $competenceData);
}
public function softDeleteCompetence($competenceId)
{
    // Logique pour effectuer une suppression en soft delete de la compétence
    return $this->referentielRepository->softDeleteCompetence($competenceId);
}

public function softDeleteReferentiel($id)
{
    // Appeler le référentiel pour effectuer la suppression douce
    $referentiel = $this->referentielRepository->findByIdDelete($id);
    
    if ($referentiel) {
        // Suppression douce du référentiel
        $referentiel->delete(); // Assurez-vous que c'est une instance du modèle
        return true;
    }

    return false;
}

public function getArchivedReferentiels()
{
    // Récupérer les référentiels supprimés
    return $this->referentielRepository->getArchived();
}




}
