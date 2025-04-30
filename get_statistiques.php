<?php
session_start(); 
header('Content-Type: application/json');

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "Utilisateur non connecté"]);
    exit();
}

$conn = new mysqli("localhost", "root", "", "watercontrol");

if ($conn->connect_error) {
    die(json_encode(["error" => "Erreur de connexion à la base de données"]));
}

// 1. Consommation du jour pour TOUS les utilisateurs
$day_sql = "SELECT SUM(debit) AS consommationJour FROM capteurs WHERE DATE(date_mesure) = CURDATE()";
$day_result = $conn->query($day_sql);
$consommationJour = $day_result->fetch_assoc()['consommationJour'] ?? 0;

// 2. Consommation du mois en cours pour TOUS les utilisateurs
$month_sql = "SELECT SUM(debit) AS consommationMois 
              FROM capteurs 
              WHERE MONTH(date_mesure) = MONTH(CURDATE()) 
              AND YEAR(date_mesure) = YEAR(CURDATE())";
$month_result = $conn->query($month_sql);
$consommationMois = $month_result->fetch_assoc()['consommationMois'] ?? 0;

// 3. Historique des 7 derniers jours pour TOUS les utilisateurs
$history_sql = "SELECT DATE(date_mesure) AS date, SUM(debit) AS conso
                FROM capteurs 
                WHERE date_mesure >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) 
                GROUP BY date 
                ORDER BY date ASC";
$history_result = $conn->query($history_sql);

$historique = [];
while ($row = $history_result->fetch_assoc()) {
    $historique[] = $row;
}

// 4. Récupère le seuil du premier utilisateur (utilisateur_id le plus bas)
$seuil_stmt = $conn->prepare("SELECT seuil FROM seuils ORDER BY utilisateur_id ASC LIMIT 1");
$seuil_stmt->execute();
$result = $seuil_stmt->get_result();
$seuil = $result->fetch_assoc()['seuil'] ?? 0;
$seuil_stmt->close();

// 5. Vérifie si la consommation du mois dépasse le seuil
$alerte = ($consommationMois > $seuil);

// 6. Envoie des données au format JSON
echo json_encode([
    "consommationJour" => $consommationJour,
    "consommationMois" => $consommationMois,
    "historique" => $historique,
    "alerte" => $alerte
]);
?>
