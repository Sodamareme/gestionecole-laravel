const nodemailer = require('nodemailer');

exports.sendAuthenticationEmail = async (email, password) => {
    const transporter = nodemailer.createTransport({
        // Configuration de votre transporteur
    });

    await transporter.sendMail({
        from: 'your-email@example.com',
        to: email,
        subject: 'Informations de Connexion',
        text: `Votre login: ${email}\nVotre mot de passe: ${password}`,
    });
};
