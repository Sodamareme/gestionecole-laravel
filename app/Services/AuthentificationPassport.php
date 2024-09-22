<?php
namespace App\Services;

use App\Services\AuthenticationServiceInterface;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

class AuthentificationPassport implements AuthenticationServiceInterface
{
    
    public function authenticate( $request)
    {
        $request = $request->only('email', 'password');

        // Essayez d'authentifier l'utilisateur
        if (Auth::attempt($request)) {
            /** @var \App\Models\User $user **/
            $user = Auth::user(); // Obtenez l'utilisateur actuellement authentifié
            $token = $user->createToken('api_token')->accessToken; // Créez un token d'accès avec Passport
            return response()->json([
                'status' => 200,
                'data' => ['token' => $token],
                'message' => 'Login Successful'
            ]);
        } else {
            return response()->json([
                'status' => 401,
                'data' => null,
                'message' => 'Invalid Credentials'
            ], 401);
        }
    }

  public function logout()
{
    // Récupérez tous les tokens de l'utilisateur actuellement authentifié
    $user = Auth::user();

    if ($user) {
        $user->tokens->each(function($token) {
            $token->revoke();
        });

        return response()->json([
            'status' => 200,
            'message' => 'Logout Successful'
        ]);
    }

    return response()->json([
        'status' => 401,
        'message' => 'Not authenticated'
    ], 401);
}

}
