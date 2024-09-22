<?php
namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Services\AuthenticationServiceInterface;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
class AuthentificationSanctum implements AuthenticationServiceInterface
{
    public function authenticate( $request)
    {
        $request = $request->only('email', 'password');

        if (!Auth::attempt($request)) {
            // $user = Auth::user();
          
            throw new \Exception('Unauthorized', 401);

           
        }
        $user = User::where('email', $request['email'])->first();
        $token = $user->createToken('Personal Access Token')->plainTextToken;

            return response()->json([
                'token' => $token,
                'user' => $user
            ], 200);
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
