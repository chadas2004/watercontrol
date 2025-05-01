<?php
session_start();
include("../config.php");

// Vérification si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    echo "Vous devez être connecté pour voir cette page.";
    exit;
}

$user_id = $_SESSION['user_id']; // ID de l'utilisateur connecté

// Requête SQL pour récupérer le statut de la vanne spécifique à cet utilisateur
$sql = "SELECT statut FROM vanne_statut WHERE user_id = ? ";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id); // Lier l'ID de l'utilisateur à la requête
$stmt->execute();
$res = $stmt->get_result();
$data = $res->fetch_assoc();

// Vérification si des résultats existent
if ($data) {
    echo $data['statut']; // 0 ou 1
} else {
    echo "Aucun statut trouvé pour cet utilisateur.";
}

$stmt->close();
$conn->close();
?>
