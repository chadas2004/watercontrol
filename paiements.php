<?php
session_start();

if (!isset($_GET['pay']) || !isset($_SESSION['user_id']) || !isset($_SESSION['selected_plan'])) {
    echo "Erreur : ID de paiement, utilisateur ou plan manquant.";
    exit;
}

$transactionId = $_GET['pay'];
$apiKey = "pk_live_mmzuO6aBLrXZAvzAOYAETwbh";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.fedapay.com/v1/transactions/" . $transactionId);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $apiKey",
    "Accept: application/json"
]);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
if (!isset($data['transaction'])) {
    echo "Transaction non trouvée.";
    exit;
}

$transaction = $data['transaction'];

if ($transaction['status'] === 'approved') {
    try {
        $pdo = new PDO('mysql:host=localhost;dbname=watercontrol', 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Vérifier si transaction déjà enregistrée
        $check = $pdo->prepare("SELECT id FROM paiements WHERE transaction_id = ?");
        $check->execute([$transaction['id']]);
        if ($check->fetch()) {
            echo "Paiement déjà enregistré.";
            exit;
        }

        $user_id = $_SESSION['user_id'];
        $abonnement_id = $_SESSION['selected_plan']['id'];
        $montant = $transaction['amount'];
        $date_paiement = date("Y-m-d H:i:s");
        $date_expiration = date("Y-m-d H:i:s", strtotime("+30 days")); // exemple : abonnement de 30 jours
        $statut = $transaction['status'];

        $stmt = $pdo->prepare("INSERT INTO paiements (utilisateur_id, abonnement_id, montant, transaction_id, date_paiement, date_expiration, statut)
            VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $user_id,
            $abonnement_id,
            $montant,
            $transaction['id'],
            $date_paiement,
            $date_expiration,
            $statut
        ]);

        unset($_SESSION['selected_plan']); // Nettoyage après paiement

        echo "✅ Paiement enregistré ! Montant : " . number_format($montant, 0, ',', ' ') . " FCFA";
    } catch (PDOException $e) {
        echo "Erreur DB : " . $e->getMessage();
    }
} else {
    echo "❌ Paiement échoué. Statut : " . htmlspecialchars($transaction['status']);
}
?>
