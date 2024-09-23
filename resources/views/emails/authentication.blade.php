<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Email d'authentification</title>
</head>
<body>
    <h1>Bienvenue, {{ $user->nom }}</h1>
    <p>Votre mot de passe est : {{ $password }}</p>
    <p>Veuillez trouver ci-joint votre QR code avec vos informations.</p>
    <img src="data:image/png;base64,{{ $qrCode }}" alt="QR Code">
</body>
</html>
