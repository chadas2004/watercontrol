<?php
session_start();
header('Content-Type: application/json');

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "Utilisateur non connecté"]);
    exit();
}

$utilisateur_id = $_SESSION['user_id'];

// Connexion à la base de données
$conn = new mysqli("localhost", "root", "", "watercontrol");
if ($conn->connect_error) {
    die(json_encode(["error" => "Erreur de connexion à la base de données"]));
}

// 1. Consommation du jour
$day_stmt = $conn->prepare("SELECT SUM(debit) AS consommationJour FROM capteurs WHERE utilisateur_id = ? AND DATE(date_mesure) = CURDATE()");
$day_stmt->bind_param("i", $utilisateur_id);
$day_stmt->execute();
$day_result = $day_stmt->get_result();
$consommationJour = $day_result->fetch_assoc()['consommationJour'] ?? 0;
$day_stmt->close();

// 2. Consommation du mois
$month_stmt = $conn->prepare("SELECT SUM(debit) AS consommationMois FROM capteurs WHERE utilisateur_id = ? AND MONTH(date_mesure) = MONTH(CURDATE()) AND YEAR(date_mesure) = YEAR(CURDATE())");
$month_stmt->bind_param("i", $utilisateur_id);
$month_stmt->execute();
$month_result = $month_stmt->get_result();
$consommationMois = $month_result->fetch_assoc()['consommationMois'] ?? 0;
$month_stmt->close();

// 3. Historique des 7 derniers jours
$history_stmt = $conn->prepare("SELECT DATE(date_mesure) AS date, SUM(debit) AS conso FROM capteurs WHERE utilisateur_id = ? AND date_mesure >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) GROUP BY DATE(date_mesure) ORDER BY date ASC");
$history_stmt->bind_param("i", $utilisateur_id);
$history_stmt->execute();
$history_result = $history_stmt->get_result();

$historique = [];
while ($row = $history_result->fetch_assoc()) {
    $historique[] = $row;
}
$history_stmt->close();

// 4. Récupération du seuil utilisateur
$seuil_stmt = $conn->prepare("SELECT seuil FROM seuils WHERE utilisateur_id = ?");
$seuil_stmt->bind_param("i", $utilisateur_id);
$seuil_stmt->execute();
$result = $seuil_stmt->get_result();
$seuil = $result->fetch_assoc()['seuil'] ?? 0;
$seuil_stmt->close();

// 5. Dépassement de seuil
$alerte = ($consommationMois > $seuil);

// 6. Envoi du SMS si seuil dépassé
if ($alerte) {
    // Vérifie la dernière alerte envoyée
    $check_stmt = $conn->prepare("SELECT date_envoi FROM notifications WHERE utilisateur_id = ? AND type = 'alerte_seuil' ORDER BY date_envoi DESC LIMIT 1");
    $check_stmt->bind_param("i", $utilisateur_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $last_notification = $check_result->fetch_assoc();
    $check_stmt->close();

    $canSend = true;

    if ($last_notification) {
        $last_time = strtotime($last_notification['date_envoi']);
        $current_time = time();
        $diff_minutes = ($current_time - $last_time) / 60;

        // Autorise l'envoi uniquement si 10 minutes sont passées
        $canSend = ($diff_minutes >= 10);
    }

    if ($canSend) {
        require_once 'vendor/autoload.php';

        // 🔐 À adapter : récupérer le numéro depuis la base de données
        $tel_stmt = $conn->prepare("SELECT telephone FROM utilisateur WHERE id = ?");
        $tel_stmt->bind_param("i", $utilisateur_id);
        $tel_stmt->execute();
        $tel_result = $tel_stmt->get_result();
        $numero = $tel_result->fetch_assoc()['telephone'] ?? "+22900000000";
        $tel_stmt->close();

        $corpsMessage = "⚠️ WaterControl : Seuil dépassé ! Consommation actuelle : {$consommationMois} L.";

        $config = ClickSend\Configuration::getDefaultConfiguration()
            ->setUsername('junioralia613@gmail.com')
            ->setPassword('3E0E7304-285D-D7C1-5162-B74CB8680AD9');

        $apiInstance = new ClickSend\Api\SMSApi(new GuzzleHttp\Client(), $config);

        $msg = new \ClickSend\Model\SmsMessage();
        $msg->setBody($corpsMessage);
        $msg->setTo($numero);
        $msg->setSource("php");

        $sms_messages = new \ClickSend\Model\SmsMessageCollection();
        $sms_messages->setMessages([$msg]);

        try {
            $result = $apiInstance->smsSendPost($sms_messages);

            // Insérer l’enregistrement dans la table des notifications
            $insert_stmt = $conn->prepare("INSERT INTO notifications (utilisateur_id, date_envoi, type, message) VALUES (?, NOW(), 'alerte_seuil', ?)");
            $insert_stmt->bind_param("is", $utilisateur_id, $corpsMessage);
            $insert_stmt->execute();
            $insert_stmt->close();
        } catch (Exception $e) {
            error_log('Erreur SMS : ' . $e->getMessage());
        }
    }
}

// 7. Réponse JSON
echo json_encode([
    "consommationJour" => $consommationJour,
    "consommationMois" => $consommationMois,
    "historique" => $historique,
    "seuil" => $seuil,
    "alerte" => $alerte
]);
?>
