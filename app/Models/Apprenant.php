<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Apprenant extends Model
{
    use HasFactory;

    protected $table = 'apprenants';

    /**
     * Les attributs pouvant être assignés en masse.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'referentiel_id',
        'promotion_id',
        'matricule',
        'photo',
        'qr_code',
    ];

    /**
     * Relation avec le modèle User (Un apprenant a un utilisateur).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation avec le modèle Promotion (Un apprenant appartient à une promotion).
     */
    public function promotion()
    {
        return $this->belongsTo(Promotion::class);
    }

    /**
     * Relation avec le modèle Referentiel (Un apprenant est lié à un référentiel).
     */
    public function referentiel()
    {
        return $this->belongsTo(Referentiel::class);
    }
}
