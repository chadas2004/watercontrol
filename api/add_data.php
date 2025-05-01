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
if (isset($_POST['debit']) && isset($_POST['utilisateur_id'])) {
    $debit = floatval($_POST['debit']);
    $utilisateur_id = intval($_POST['utilisateur_id']);
    $timestamp = date("Y-m-d H:i:s");

    // Insérer dans la table capteurs avec utilisateur_id
    $sql = "INSERT INTO capteurs (debit, utilisateur_id, date_heure) VALUES ($debit, $utilisateur_id, '$timestamp')";

    if (mysqli_query($conn, $sql)) {
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
