@component('mail::message')
# Bonjour,

Votre compte a été créé avec succès. Voici vos informations de connexion :

- **Email** : {{ $email }}
- **Matricule** : {{ $matricule }}
- **Mot de passe** : {{ $password }}

Merci de vous connecter et de modifier votre mot de passe après la première connexion.

@component('mail::button', ['url' => route('login')])
Se connecter
@endcomponent

Merci,<br>
{{ config('app.name') }}
@endcomponent
