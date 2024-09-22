<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Competence extends Model
{
    protected $fillable = ['nom', 'description', 'duree_acquisition', 'type', 'referentiel_id'];

    public function referentiel()
    {
        return $this->belongsTo(Referentiel::class);
    }

    public function modules()
    {
        return $this->hasMany(Module::class);
    }
}

class Module extends Model
{
    protected $fillable = ['nom', 'description', 'duree_acquisition', 'competence_id'];

    public function competence()
    {
        return $this->belongsTo(Competence::class);
    }
}

