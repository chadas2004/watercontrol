<?php
session_start(); // Démarrer la session

// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "watercontrol";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Vérification si les champs sont bien envoyés
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $nom = $_POST['nom'];
        $code_connexion = $_POST['code_connexion'];

        // Vérification si les champs sont remplis
        if (empty($nom) || empty($code_connexion)) {
            echo "<script>alert('Tous les champs sont obligatoires.'); window.history.back();</script>";
            exit;
        }

        // Vérification des informations dans la base de données
        $sql = "SELECT * FROM utilisateur WHERE nom = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$nom]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Vérification si le compte est actif
            if ($user['is_active'] != 1) {
                echo "<script>alert('Votre compte est désactivé. Veuillez contacter l’administrateur.'); window.history.back();</script>";
                exit;
            }

            // Vérification du code de connexion
            if ($code_connexion == $user['code_connexion']) {
                // Enregistrement des informations dans la session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_nom'] = $user['nom'];

                echo "<script>
                        alert('Connexion réussie ! Bienvenue, $nom.');
                        window.location.href = 'accueil.php';
                      </script>";
                exit;
            } else {
                echo "<script>alert('Code de connexion incorrect.'); window.history.back();</script>";
                exit;
            }
        } else {
            echo "<script>alert('Nom incorrect.'); window.history.back();</script>";
            exit;
        }
    }
} catch (PDOException $e) {
    echo "<script>alert('Erreur de connexion à la base de données : " . $e->getMessage() . "');</script>";
}

// Fermeture de la connexion
$conn = null;
?>
