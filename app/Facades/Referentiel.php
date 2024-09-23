<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class Referentiel extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'referentiel';
    }
}
