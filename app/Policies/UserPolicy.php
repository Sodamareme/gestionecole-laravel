<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Apprenant;
use App\Models\Role;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
class UserPolicy
{
   public function isAdmin(User $user){
    return $user->role === 'Admin';
   }
   public function isCoach(User $user){
    return $user->role === 'Coach';
   }
   public function isCM(User $user){
    return $user->role === 'CM';
   }
   public function isapprenant(User $user){
      return $user->role === 'apprenant';
     }
     
}
