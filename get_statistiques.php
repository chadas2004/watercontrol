<?php
session_start(); 
header('Content-Type: application/json');

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "Utilisateur non connecté"]);
    exit();
}

$utilisateur_id = $_SESSION['user_id'];

$conn = new mysqli("localhost", "root", "", "watercontrol");

if ($conn->connect_error) {
    die(json_encode(["error" => "Erreur de connexion à la base de données"]));
}

// 1. Consommation du jour pour l'utilisateur connecté
$day_sql = "SELECT SUM(debit) AS consommationJour 
            FROM capteurs 
            WHERE DATE(date_mesure) = CURDATE() 
            AND utilisateur_id = ?";
$day_stmt = $conn->prepare($day_sql);
$day_stmt->bind_param("i", $utilisateur_id);
$day_stmt->execute();
$day_result = $day_stmt->get_result();
$consommationJour = $day_result->fetch_assoc()['consommationJour'] ?? 0;
$day_stmt->close();

// 2. Consommation du mois pour l'utilisateur connecté
$month_sql = "SELECT SUM(debit) AS consommationMois 
              FROM capteurs 
              WHERE MONTH(date_mesure) = MONTH(CURDATE()) 
              AND YEAR(date_mesure) = YEAR(CURDATE()) 
              AND utilisateur_id = ?";
$month_stmt = $conn->prepare($month_sql);
$month_stmt->bind_param("i", $utilisateur_id);
$month_stmt->execute();
$month_result = $month_stmt->get_result();
$consommationMois = $month_result->fetch_assoc()['consommationMois'] ?? 0;
$month_stmt->close();

// 3. Historique des 7 derniers jours pour l'utilisateur connecté
$history_sql = "SELECT DATE(date_mesure) AS date, SUM(debit) AS conso
                FROM capteurs 
                WHERE date_mesure >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) 
                AND utilisateur_id = ?
                GROUP BY DATE(date_mesure) 
                ORDER BY date ASC";
$history_stmt = $conn->prepare($history_sql);
$history_stmt->bind_param("i", $utilisateur_id);
$history_stmt->execute();
$history_result = $history_stmt->get_result();

$historique = [];
while ($row = $history_result->fetch_assoc()) {
    $historique[] = $row;
}
$history_stmt->close();

// 4. Seuil pour cet utilisateur
$seuil_stmt = $conn->prepare("SELECT seuil FROM seuils WHERE utilisateur_id = ?");
$seuil_stmt->bind_param("i", $utilisateur_id);
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
