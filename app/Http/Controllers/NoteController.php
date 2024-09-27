<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Note;
use App\Models\Apprenant;
use App\Models\Module;
use Illuminate\Support\Facades\DB;

class NoteController extends Controller
{
    public function addModuleNote($nom, Request $request)
    {
        // Valider la requête
        $validatedData = $request->validate([
                'notes.*.user_id' => 'required|exists:apprenants,user_id', // Vérifie si le user_id existe dans la table apprenants
                'notes.*.note' => 'required|numeric', // Validation de la note
            
        ]);
        // Rechercher le module par son nom
        $module = Module::where('nom', $nom)->first();

        if (!$module) {
            return response()->json(['message' => 'Module not found'], 404);
        }

        // Transaction pour ajouter les notes
        DB::transaction(function () use ($validatedData, $module) {
            foreach ($validatedData['notes'] as $noteData) {
                $apprenant = Apprenant::findOrFail($noteData['apprenantId']);

                // Ajouter ou mettre à jour la note de l'apprenant pour ce module
                Note::updateOrCreate(
                    [
                        'apprenant_id' => $apprenant->id,
                        'module_id' => $module->id,
                    ],
                    [
                        'note' => $noteData['note']
                    ]
                );
            }
        });

        return response()->json(['message' => 'Notes added successfully'], 201);
    }
}
