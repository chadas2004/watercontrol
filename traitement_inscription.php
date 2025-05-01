<?php 
// Information de connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "watercontrol";

try {
    // Création d'une connexion PDO
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    
    // Configuration des options PDO
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "CONNEXION ÉTABLIE <br>";

    // Récupération des données du formulaire
    $nom = $_POST['nom'];
    $adresse = $_POST['adresse'];
    $mail = $_POST['mail'];
    $password = $_POST['password'];
    $confirmpass = $_POST['confirmpass'];

    // Vérifications de base
    if (empty($nom) || empty($adresse) || empty($mail) || empty($password) || empty($confirmpass)) {
        echo "Tous les champs sont obligatoires.";
        exit;
    }

    if ($password !== $confirmpass) {
        echo "Le mot de passe et la confirmation ne correspondent pas.";
        exit;
    }

    // Vérifie si l'email existe déjà
    $sql = "SELECT COUNT(*) FROM utilisateur WHERE mail = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$mail]);
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        echo "<script>
            alert('Cet email est déjà utilisé. Veuillez en choisir un autre.');
            window.location.href = 'index.html';
        </script>";
        exit;
    }

   

    // Définir les valeurs par défaut pour les utilisateur
    $can_set_seuil = 1;
    $can_control_valve = 1;
    $is_active = 1;
    $image = ''; // valeur par défaut

    // Hash du mot de passe
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Insertion du nouvel utilisateur avec rôles et permissions
    $sql = "INSERT INTO utilisateur (nom, adresse, mail, password, image, can_set_seuil, can_control_valve, is_active)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$nom, $adresse, $mail, $passwordHash, $image, $can_set_seuil, $can_control_valve, $is_active]);

    // Récupérer l'ID du nouvel utilisateur
    $utilisateur_id = $conn->lastInsertId();
    echo "Nouvel utilisateur ID : " . $utilisateur_id;

    // Insertion dans la table seuils pour tous les utilisateurs
    $seuil_defaut = 1000; // valeur par défaut du seuil
    $stmt_seuil = $conn->prepare("INSERT INTO seuils (seuil, utilisateur_id) VALUES (?, ?)");
    $stmt_seuil->execute([$seuil_defaut, $utilisateur_id]);

    // Insertion dans la table vanne_statut pour tous les utilisateurs
    $vanne_statut = 1; // 1 = ouverte, 0 = fermée (selon ta logique)
    $stmt_vanne = $conn->prepare("INSERT INTO vanne_statut (statut, utilisateur_id) VALUES (?, ?)");
    $stmt_vanne->execute([$vanne_statut, $utilisateur_id]);

    // Redirection après réussite
    echo "<script>
        alert('Inscription réussie ! Vous pouvez maintenant vous connecter.');
        window.location.href = 'index.html';
    </script>";

} catch (PDOException $e) {
    echo "Erreur lors de la connexion à la base de données : " . $e->getMessage();
}

// Fermeture de la connexion
$conn = null;
?>
