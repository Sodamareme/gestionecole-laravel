<?php
namespace App\Services;

use Illuminate\Http\Request;

interface AuthenticationServiceInterface
{
    public function authenticate(Request $request);
    public function logout();
}
