<?php

namespace App\Imports;

use App\Models\Apprenant;
use Maatwebsite\Excel\Concerns\ToModel;

class ApprenantsImport implements ToModel
{
    /**
     * Chaque ligne du fichier Excel sera convertie en une instance du modèle Apprenant.
     *
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return new Apprenant([
            'user_id' => $row[0], // Supposons que la première colonne est l'ID utilisateur
            'referentiel_id' => $row[1], // Deuxième colonne pour le référentiel
            'promotion_id' => $row[2], // Troisième colonne pour la promotion
            'email' => $row[3], // Quatrième colonne pour l'e-mail
            'photo' => $row[4], // Cinquième colonne pour la photo
        ]);
    }
}
