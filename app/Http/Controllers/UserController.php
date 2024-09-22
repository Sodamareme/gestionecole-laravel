<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Http\Request;
use App\Services\DataStorageInterface;
use App\Models\User;
use Kreait\Firebase\Auth;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;
use App\Exports\UsersExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade as PDF;


class UserController extends Controller
{
    protected $dataStorage;
    protected $firebaseAuth;

    public function __construct(DataStorageInterface $dataStorage,Auth $firebaseAuth)
    {
        $this->dataStorage = $dataStorage;
        $this->firebaseAuth = $firebaseAuth;
    }
     // Gestion de la connexion Firebase
     public function login(Request $request)
     {
         $credentials = $request->only(['email', 'password']);
         
         try {
             // Utilisez Firebase Auth pour authentifier l'utilisateur avec email et mot de passe
             $signInResult = $this->firebaseAuth->signInWithEmailAndPassword($credentials['email'], $credentials['password']);
             $idToken = $signInResult->idToken();
             
             // Vérifiez le token retourné par Firebase
             $verifiedIdToken = $this->firebaseAuth->verifyIdToken($idToken);
             
             // Récupérez l'UID de l'utilisateur
             $uid = $verifiedIdToken->claims()->get('sub');
             
             // Obtenez les détails de l'utilisateur depuis Firebase
             $user = $this->firebaseAuth->getUser($uid);
             
             return response()->json([
                 'message' => 'Connexion réussie',
                 'user' => $user,
                 'token' => $idToken
             ], 200);
         } catch (FailedToVerifyToken $e) {
             return response()->json(['message' => 'Échec de la vérification du token Firebase'], 401);
         } catch (\Exception $e) {
             return response()->json(['message' => 'Échec de la connexion : ' . $e->getMessage()], 500);
         }
     }
     
    public function index(Request $request)
    {
        $users = $this->dataStorage->getAllUsers();
        if ($request->has('role') && in_array($request->role, User::getRoles())) {
            $users = array_filter($users, function($user) use ($request) {
                return $user['role'] === $request->role;
            });
        }
        return response()->json($users);
    }

    public function show($id)
    {
        $user = $this->dataStorage->getUser($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
                // Only keep users with the specified role
        }
        return response()->json($user);
    }

        // Return the filtered users as JSON

    // public function store(Request $request)
    // {
    //     $firebase = app('firebase.database');
    //     $reference = $firebase->getReference('users');
    //     $userData = $request->only(['id','nom', 'prenom', 'email','password', 'role','photo', 'telephone', 'statut', 'fonction']);
    //     $newUser = $reference->push($userData);
    //     return response()->json(['user' => $userData]);
    // }
    public function store(Request $request)
    {
        // Validation des données d'entrée
        $request->validate([
            'nom' => 'required|string',
            'prenom' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|string',
            'photo' => 'required|file|mimes:jpeg,png,jpg,gif|max:2048', // Validation du fichier
        ]);
    
        // Gérer le téléchargement du fichier
        $photoPath = null;
        if ($request->hasFile('photo')) {
            // Récupérer le fichier
            $file = $request->file('photo');
            
            // Obtenez le nom de fichier et l'extension
            $fileName = time() . '_' . $file->getClientOriginalName();
            
            // Uploader vers Firebase Storage
            $storage = app('firebase.storage')->getBucket(env('FIREBASE_STORAGE_BUCKET'));
    
            $storage->upload(
                fopen($file->getPathname(), 'r'),
                [
                    'name' => 'photos/' . $fileName,
                    'metadata' => [
                        'contentType' => $file->getClientMimeType(),
                    ],
                ]
            );
    
            // Générez l'URL de la photo
            $photoPath = 'https://firebasestorage.googleapis.com/v0/b/' . env('FIREBASE_PROJECT_ID') . '.appspot.com/o/photos%2F' . $fileName . '?alt=media';
        }
    
        // Préparez les données de l'utilisateur
        $userData = $request->only(['nom', 'prenom', 'email', 'role', 'telephone', 'statut', 'fonction']);
        $userData['photo'] = $photoPath;
        $userData['role'] = $request->role;
        // Créez l'utilisateur dans Firebase Authentication
        try {
            $createdUser = $this->firebaseAuth->createUser([
                'email' => $request->email,
                'password' => $request->password,
                'displayName' => $request->prenom . ' ' . $request->nom,
                'photoURL' => $userData['photo'], // URL de la photo
            ]);
            
            // Enregistrez les données de l'utilisateur dans votre base de données Firebase
            $userData['firebase_uid'] = $createdUser->uid; // Enregistrez l'UID pour référence future
            $firebase = app('firebase.database');
            $reference = $firebase->getReference('users');
            $newUser = $reference->push($userData);
    
            // Appel à la méthode pour générer le fichier Excel
            $this->exportExcel();
    
            return response()->json(['user' => $userData, 'firebase_uid' => $createdUser->uid], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Échec de la création de l\'utilisateur : ' . $e->getMessage()], 500);
        }
    }
    
    
    public function exportExcel()
    {
        $export = new UsersExport($this->dataStorage);
        
        // Générer un nom de fichier avec un timestamp
        $fileName = 'users_' . time() . '.xlsx';
        
        // Stocker le fichier Excel dans le répertoire storage/app/public
        Excel::store($export, $fileName, 'public');
        
        return response()->json(['message' => 'Fichier Excel mis à jour avec succès.', 'file' => $fileName], 200);
    }
    
    
    
    
// public function exportToPDF()
// {
//     $users = $this->dataStorage->getAllUsers(); // Récupérer tous les utilisateurs
//     $pdf = PDF::loadView('users', ['users' => $users]);
//     return $pdf->download('users.pdf');
// }

    public function update(UpdateUserRequest $request, $id)
    {
        $userData = $request->validated();
        $this->dataStorage->updateUser($id, $userData);
        $updatedUser = $this->dataStorage->getUser($id);
        if (!$updatedUser) {
            return response()->json(['message' => 'User not found'], 404);
        }
        return response()->json($updatedUser);
    }

    public function destroy($id)
    {
        $user = $this->dataStorage->getUser($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        $this->dataStorage->deleteUser($id);
        return response()->json(null, 204);
    }

    public function filtreRole(Request $request)
{
    $users = $this->dataStorage->getAllUsers();
    
    // Vérifier si un rôle est fourni dans le corps de la requête
    if ($request->has('role')) {
        $role = $request->input('role');
        if (in_array($role, User::getRoles())) {
            $users = array_filter($users, function($user) use ($role) {
                return $user['role'] === $role;
            });
        }
    }
    
    return response()->json($users);
}
public function getUserById($id)
{
    $firebase = app('firebase.database');
    $reference = $firebase->getReference('users/' . $id); // Chercher par ID
    
    $user = $reference->getValue(); // Obtenez la valeur des données
    
    if ($user) {
        return response()->json(['user' => $user], 200);
    } else {
        return response()->json(['message' => 'Utilisateur non trouvé'], 404);
    }
}


}
