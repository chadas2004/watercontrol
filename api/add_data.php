<?php
// Connexion à la base
$conn = new mysqli("localhost", "root", "", "watercontrol");

// Vérifier la connexion
if ($conn->connect_error) {
    http_response_code(500);
    echo "Erreur connexion";
    exit();
}

// Vérifier si le débit est envoyé
if (isset($_POST['debit'])) {
    $debit = floatval($_POST['debit']);
    $timestamp = date("Y-m-d H:i:s");

    // Insérer dans la table capteurs
    $sql = "INSERT INTO capteurs (debit, date_heure) VALUES ($debit, '$timestamp')";

    if ($conn->query($sql) === TRUE) {
        echo "OK";
    } else {
        http_response_code(500);
        echo "Erreur SQL";
    }
} else {
    http_response_code(400); // mauvaise requête
    echo "Paramètre manquant";
}

$conn->close();
?>
