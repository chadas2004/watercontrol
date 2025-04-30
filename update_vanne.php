<?php
$host = "localhost"; // Adresse du serveur
$user = "root"; // Nom d'utilisateur MySQL
$pass = ""; // Mot de passe MySQL (met le tien si nécessaire)
$dbname = "watercontrol"; // Nom de la base

// Connexion à MySQL
$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Erreur de connexion: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $valveState = isset($_POST['valve']) ? (int) $_POST['valve'] : 0;

    // Mettre à jour l'état de l'électrovanne dans la base de données
    $update_query = "UPDATE vanne_statut SET statut = ? WHERE id = 1"; // Par exemple, id = 1 pour la vanne
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("i", $valveState);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo $valveState ? "Activée" : "Désactivée";
    } else {
        echo "Erreur de mise à jour de l'état de la vanne.";
    }
}
$conn->close();
?>



