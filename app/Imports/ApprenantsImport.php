<?php
namespace App\Imports;

use App\Models\Apprenant;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use App\Exports\UsersExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Jobs\SendAuthenticationEmailJob;
class ApprenantsImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        // Vérifier l'unicité
        $existing = Apprenant::where('nom', $row['nom'])
            ->where('prenom', $row['prenom'])
            ->where('date_naissance', $row['date_naissance'])
            ->where('sexe', $row['sexe'])
            ->first();

        if ($existing) {
            return null; // Si l'apprenant existe déjà
        }

        // Créer un nouvel apprenant
        $apprenant = new Apprenant([
            'nom' => $row['nom'],
            'prenom' => $row['prenom'],
            'date_naissance' => $row['date_naissance'],
            'sexe' => $row['sexe'],
            // Autres champs...
        ]);

        // Générer matricule et code QR
        $apprenant->matricule = $this->generateMatricule();
        $apprenant->qr_code = $this->generateQRCode($apprenant);

        $apprenant->save();

        // Envoyer l'e-mail
        SendAuthenticationEmailJob::dispatch($apprenant);

        return $apprenant;
    }

    public function rules(): array
    {
        return [
            'nom' => 'required|string',
            'prenom' => 'required|string',
            'date_naissance' => 'required|date',
            'sexe' => 'required|string|in:male,female',
        ];
    }

    protected function generateMatricule()
    {
        // Logique de génération de matricule
    }

    protected function generateQRCode($apprenant)
    {
        // Logique de génération de code QR
    }
}
