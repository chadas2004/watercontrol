<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "Utilisateur non connecté"]);
    exit();
}

$utilisateur_id = $_SESSION['user_id'];

$conn = new mysqli("localhost", "root", "", "watercontrol");

if ($conn->connect_error) {
    die(json_encode(["error" => "Erreur de connexion à la base de données"]));
}

// Récupérer les 12 derniers mois de consommation de l'utilisateur
$sql = "SELECT DATE_FORMAT(date_mesure, '%Y-%m') AS mois, SUM(debit) AS total_debit 
        FROM capteurs 
        WHERE utilisateur_id = ? 
        AND date_mesure >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
        GROUP BY mois 
        ORDER BY mois DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $utilisateur_id);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
?>
