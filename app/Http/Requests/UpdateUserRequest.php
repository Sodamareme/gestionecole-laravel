<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\User;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        return [
            'nom' => 'sometimes|string|max:255',
            'prenom' => 'sometimes|string|max:255',
            'adresse' => 'sometimes|string',
            'telephone' => 'sometimes|string',
            'fonction' => 'sometimes|string',
            'email' => 'sometimes|email|unique:users,email,' . $this->route('user'),
            'photo' => 'nullable|string',
            'statut' => 'sometimes|in:Bloquer,Actif',
            'role' => ['sometimes', Rule::in(User::getRoles())],
        ];
    }
    public function messages(){
       return [
            'nom.required' => 'Le nom est obligatoire',
            'prenom.required' => 'Le prénom est obligatoire',
            'adresse.required' => 'L\'adresse est obligatoire',
            'telephone.required' => 'Le téléphone est obligatoire',
            'fonction.required' => 'La fonction est obligatoire',
            'email.required' => 'L\'email est obligatoire',
            'photo.required' => 'L\'URL de la photo est obligatoire',
            'statut.required' => 'Le statut est obligatoire',
            'role.required' => 'L\'ID du rôle est obligatoire',
            'email.unique' => 'L\'email est déjà utilisé',
        ];
    }
}
