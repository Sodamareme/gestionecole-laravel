<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AuthenticationEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $password;

    /**
     * Create a new message instance.
     *
     * @param  object  $user  L'utilisateur avec les informations nécessaires
     * @param  string  $password  Le mot de passe de l'utilisateur
     * @return void
     */
    public function __construct($user, $password)
    {
        // Assurez-vous que $user est un objet avec les propriétés requises
        $this->user = $user;
        $this->password = $password;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.auth')
                    ->subject('Informations de Connexion')
                    ->with([
                        'email' => $this->user->email, // Assurez-vous que la propriété existe
                        'matricule' => $this->user->matricule, // Assurez-vous que la propriété existe
                        'password' => $this->password,
                    ]);
    }
}
