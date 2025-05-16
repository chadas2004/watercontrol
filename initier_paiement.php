<?php
session_start();

// Affichage des erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inclusion de la bibliothèque FedaPay
require_once 'vendor/autoload.php';

use FedaPay\FedaPay;
use FedaPay\Transaction;

// Définir la clé API et l'environnement (test ou live)
FedaPay::setApiKey('pk_sandbox_7VV03iEWf4lacQIAR5d1zgH0');  // Assurez-vous que la clé API est correcte
FedaPay::setEnvironment('test');  // Si vous êtes en mode test, utilisez 'test'

// Vérifier que les données nécessaires sont présentes
if (!isset($_SESSION['user_id']) || !isset($_SESSION['selected_plan'])) {
    die("Données manquantes.");
}

$user_id = $_SESSION['user_id'];
$plan = $_SESSION['selected_plan'];

// Créer l'URL de callback
$callback_url = "http://localhost/watercontrol/paiement.php?user={$user_id}&plan={$plan['id']}";

try {
    // Créer la transaction
    $transaction = Transaction::create([
        'amount' => $plan['prix'],
        'description' => $plan['nom'],
        'currency' => ['iso' => 'XOF'],
        'callback_url' => $callback_url,
        'customer' => [
            'firstname' => $_SESSION['user_nom'] ?? 'Utilisateur',
            'email' => $_SESSION['email'] ?? 'utilisateur@domaine.com'
        ]
    ]);

    // Inspecter la réponse de la transaction
    var_dump($transaction);

    // Vérifier si l'URL de paiement est présente
    if (isset($transaction->url)) {
        // ✅ Redirection vers l’URL de paiement
        header("Location: " . $transaction->url);
        exit;
    } else {
        // Si l'URL de paiement est vide, afficher un message d'erreur
        echo "Transaction créée mais sans URL.";
        file_put_contents('error_log.txt', "Erreur : Transaction sans URL.\n", FILE_APPEND);
    }

} catch (Exception $e) {
    // En cas d'erreur FedaPay
    echo "Erreur FedaPay : " . $e->getMessage();
    file_put_contents('error_log.txt', "Erreur FedaPay : " . $e->getMessage() . "\n", FILE_APPEND);
}
?>
