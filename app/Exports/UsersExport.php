<?php
namespace App\Exports;

use App\Services\DataStorageInterface;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UsersExport implements FromCollection, WithHeadings
{
    protected $dataStorage;

    public function __construct(DataStorageInterface $dataStorage)
    {
        $this->dataStorage = $dataStorage;
    }

    public function collection()
    {
        // Récupérer tous les utilisateurs via le service de stockage de données
        return collect($this->dataStorage->getAllUsers())->map(function ($user) {
            return [
                'email' => $user['email'],
                'nom' => $user['nom'],
                'prenom' => $user['prenom'],
                'role' => $user['role'],
                'fonction' => $user['fonction'],
                'statut' => $user['statut'],
                'photo' => $user['photo'],
                'telephone' => $user['telephone'],
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Email',
            'Nom',
            'Prénom',
            'Rôle',
            'Fonction',
            'Statut',
            'Photo',
            'Téléphone',
        ];
    }
}
