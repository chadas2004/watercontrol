<?php
session_start();
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "watercontrol";

// Connexion à MySQL avec MySQLi orienté objet
$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Erreur de connexion: " . $conn->connect_error);
}

// Vérifie que l'utilisateur est bien connecté
if (!isset($_SESSION['user_id'])) {
    echo "Utilisateur non connecté.";
    exit();
}

$utilisateur_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $valveState = isset($_POST['valve']) ? (int) $_POST['valve'] : 0;

    // Mettre à jour l'état de la vanne pour l'utilisateur connecté
    $update_query = "UPDATE vanne_statut SET statut = ? WHERE utilisateur_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("ii", $valveState, $utilisateur_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo $valveState ? "Activée" : "Désactivée";
    } else {
        echo "Erreur de mise à jour de l'état de la vanne.";
    }
}

$conn->close();
?>