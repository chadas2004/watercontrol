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

    // Vérifie s'il y a déjà des utilisateurs
    $check_sql = "SELECT COUNT(*) AS total FROM utilisateur";
    $result = $conn->query($check_sql);
    $data = $result->fetch(PDO::FETCH_ASSOC);
    $is_first_user = ($data['total'] == 0);

    // Définir les valeurs selon si c'est le premier utilisateur
    $role = $is_first_user ? 'admin' : 'user';
    $can_set_seuil = $is_first_user ? 1 : 0;
    $can_control_valve = $is_first_user ? 1 : 0;
    $is_active = 1;
    $image = ''; // valeur par défaut

    // Hash du mot de passe
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Insertion du nouvel utilisateur avec rôles et permissions
    $sql = "INSERT INTO utilisateur (nom, adresse, mail, password, image, role, can_set_seuil, can_control_valve, is_active)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$nom, $adresse, $mail, $passwordHash, $image, $role, $can_set_seuil, $can_control_valve, $is_active]);

    // Récupérer l'ID du nouvel utilisateur
    $utilisateur_id = $conn->lastInsertId();
    echo "Nouvel utilisateur ID : " . $utilisateur_id;

    // Si c'est le premier utilisateur, insérer dans les tables `seuils` et `vanne_statut`
    if ($is_first_user) {
        // Insertion dans la table `seuils`
        $seuil_defaut = 1000; // valeur par défaut du seuil
        $stmt_seuil = $conn->prepare("INSERT INTO seuils (seuil, utilisateur_id) VALUES (?, ?)");
        $stmt_seuil->execute([$seuil_defaut, $utilisateur_id]);

        // Insertion dans la table `vanne_statut`
        $vanne_statut = 1; // 1 = ouverte, 0 = fermée (selon ta logique)
        $stmt_vanne = $conn->prepare("INSERT INTO vanne_statut (statut, utilisateur_id) VALUES (?, ?)");
        $stmt_vanne->execute([$vanne_statut, $utilisateur_id]);
    }

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
