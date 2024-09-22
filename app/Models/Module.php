<?php
namespace App\Models;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Model;


class Module extends Model
{
    use SoftDeletes; // Ajoutez ceci

    protected $fillable = ['nom', 'description', 'duree_acquisition', 'competence_id'];
}