<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // ou le chemin vers PHPMailer si installé manuellement

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'];
    $email = $_POST['email'];
    $message = $_POST['message'];

    try {
        // Connexion à la base de données
        $pdo = new PDO('mysql:host=localhost;dbname=watercontrol', 'root', '');
        $stmt = $pdo->prepare("INSERT INTO contacts (nom, email, message) VALUES (:nom, :email, :message)");
        $stmt->execute([
            ':nom' => $nom,
            ':email' => $email,
            ':message' => $message
        ]);

        // Envoi d'un mail à l'admin
        $mail = new PHPMailer(true);

        try {
            // Configuration SMTP (exemple avec Gmail, adapte selon ton hébergeur)
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com'; // ou smtp.ton-hebergeur.com
            $mail->SMTPAuth   = true;
            $mail->Username   = 'chadasglele@gmail.com'; // adresse de l’expéditeur
            $mail->Password   = 'frtq rxax bnvt fdhw'; // mot de passe ou mot de passe d'application
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Destinataire
            $mail->setFrom('chadasglele@gmail.com', 'WaterControl Contact');
            $mail->addAddress('junioralia613@gmail.com'); // mail de l’administrateur

            // Contenu
            $mail->isHTML(true);
            $mail->Subject = "Nouveau message de contact WaterControl";
            $mail->Body    = "
                <h3>Nouveau message reçu :</h3>
                <p><strong>Nom :</strong> {$nom}</p>
                <p><strong>Email :</strong> {$email}</p>
                <p><strong>Message :</strong><br>{$message}</p>
            ";

            $mail->send();
        } catch (Exception $e) {
            // Optionnel : log ou notifier l’échec
        }

        header("Location: contact.php?success=1");
        exit();
    } catch (PDOException $e) {
        echo "Erreur : " . $e->getMessage();
    }
}
?>
