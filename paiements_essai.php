<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    die("Utilisateur non connecté.");
}

try {
    $pdo = new PDO('mysql:host=localhost;dbname=watercontrol', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur DB : " . $e->getMessage());
}

// Données fictives
$utilisateur_id = 3;
$abonnement_id = 2; // Remplace par l'ID réel du plan
$montant = 5000; // FCFA
$transaction_id = uniqid('TEST_'); // ID unique fictif
$date_paiement = date('Y-m-d H:i:s');
$date_expiration = date('Y-m-d H:i:s', strtotime('+30 days'));
$statut = 'réussi';

// Insertion dans la table paiements
$stmt = $pdo->prepare("INSERT INTO paiements 
    (utilisateur_id, abonnement_id, montant, transaction_id, date_paiement, date_expiration, statut)
    VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->execute([
    $utilisateur_id,
    $abonnement_id,
    $montant,
    $transaction_id,
    $date_paiement,
    $date_expiration,
    $statut
]);

// Mise à jour des informations de l'utilisateur
$updateUser = $pdo->prepare("UPDATE utilisateur SET
    abonnement = :abonnement,
    date_abonnement = NOW(),
    date_expiration = :date_expiration,
    is_active = 1
    WHERE id = :id");

$updateUser->execute([
    ':abonnement' => $abonnement_id, // Remplace par le nom réel de l'abonnement
    ':date_expiration' => $date_expiration,
    ':id' => $utilisateur_id
]);

echo "✅ Paiement test inséré avec succès et utilisateur mis à jour !<br>";
echo "Montant : " . number_format($montant, 0, ',', ' ') . " FCFA<br>";
echo "Abonnement activé jusqu'au : " . date('d/m/Y', strtotime($date_expiration));
?>
