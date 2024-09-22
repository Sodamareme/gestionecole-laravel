<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use Notifiable;

    protected $connection = 'firebase';
    protected $table = 'users';

    const ROLE_ADMIN = 'Admin';
    const ROLE_COACH = 'Coach';
    const ROLE_CM = 'CM';
    const ROLE_APPRENANT = 'Apprenant';
    const ROLE_MANAGER = 'Manager';

    protected $fillable = [
       'id', 'nom', 'prenom', 'adresse', 'telephone', 'fonction', 'email','password', 'photo', 'statut', 'role'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public static function getRoles(): array
    {
        return [
            self::ROLE_ADMIN,
            self::ROLE_COACH,
            self::ROLE_CM,
            self::ROLE_APPRENANT,
            self::ROLE_MANAGER,
        ];
    }
}