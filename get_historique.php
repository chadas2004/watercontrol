<?php
header('Content-Type: application/json'); // Indique que la réponse est JSON
$conn = new mysqli("localhost", "root", "", "watercontrol");

if ($conn->connect_error) {
    die(json_encode(["error" => "Erreur de connexion à la base de données"]));
}

// Requête SQL pour récupérer la consommation mensuelle
$sql = "SELECT DATE_FORMAT(date_mesure, '%Y-%m') AS mois, SUM(debit) AS total_debit 
        FROM capteurs GROUP BY mois ORDER BY mois DESC";

$result = $conn->query($sql);

$data = []; // Tableau pour stocker les résultats
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data); // Convertit le tableau en JSON
?>
