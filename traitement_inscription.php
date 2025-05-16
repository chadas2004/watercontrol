<?php
// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "watercontrol";

// PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

try {
    // Connexion PDO
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupération des données du formulaire
    $nom = $_POST['nom'];
    $prenoms = $_POST['prenoms'];
    $telephone = $_POST['telephone'];
    $adresse = $_POST['adresse'];
    $mail = $_POST['mail'];

    // Vérifications
    if (empty($nom) || empty($prenoms) || empty($telephone) || empty($adresse) || empty($mail)) {
        echo "Tous les champs sont obligatoires.";
        exit;
    }

    // Vérification email existant
    $sql = "SELECT COUNT(*) FROM utilisateur WHERE mail = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$mail]);
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        echo "<script>
            alert('Cet email est déjà utilisé. Veuillez en choisir un autre.');
            window.location.href = 'index.php';
        </script>";
        exit;
    }

    // Valeurs par défaut
    $can_set_seuil = 1;
    $can_control_valve = 1;
    $is_active = 0;
    $image = '';
    $code_connexion = 'WAG' . str_pad(random_int(0, 999999999), 9, '0', STR_PAD_LEFT);


    // Insertion utilisateur
    $sql = "INSERT INTO utilisateur (nom, prenoms, telephone, adresse, mail, code_connexion, image, can_set_seuil, can_control_valve, is_active)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$nom, $prenoms, $telephone, $adresse, $mail, $code_connexion, $image, $can_set_seuil, $can_control_valve, $is_active]);

    $utilisateur_id = $conn->lastInsertId();

    // Insertion seuil par défaut
    $seuil_defaut = 1000;
    $stmt_seuil = $conn->prepare("INSERT INTO seuils (seuil, utilisateur_id) VALUES (?, ?)");
    $stmt_seuil->execute([$seuil_defaut, $utilisateur_id]);

    // Insertion statut vanne
    $vanne_statut = 1;
    $stmt_vanne = $conn->prepare("INSERT INTO vanne_statut (statut, utilisateur_id) VALUES (?, ?)");
    $stmt_vanne->execute([$vanne_statut, $utilisateur_id]);

    // Envoi du mail avec le code de connexion
    $mailer = new PHPMailer(true);

    try {
        $mailer->isSMTP();
        $mailer->Host = 'smtp.gmail.com';
        $mailer->SMTPAuth = true;
        $mailer->Username = 'chadasglele@gmail.com';
        $mailer->Password = 'frtq rxax bnvt fdhw'; // mot de passe d'application Gmail
        $mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mailer->Port = 587;

        $mailer->setFrom('chadasglele@gmail.com', 'WaterControl');
        $mailer->addAddress($mail, "$nom $prenoms");
        $mailer->isHTML(true);
        $mailer->Subject = 'Votre code de connexion';
        $mailer->Body = "Bonjour $nom $prenoms,<br><br>Votre code de connexion est : <b>$code_connexion</b><br>Veuillez le conserver précieusement.";

        $mailer->send();

        echo "<script>
            alert('Inscription réussie ! Un email contenant votre code de connexion a été envoyé.');
            window.location.href = 'index.php';
        </script>";
        exit;

    } catch (Exception $e) {
        echo "Inscription réussie, mais l'envoi de l'email a échoué. Erreur : {$mailer->ErrorInfo}";
    }

} catch (PDOException $e) {
    echo "Erreur lors de la connexion à la base de données : " . $e->getMessage();
}

$conn = null;
?>
