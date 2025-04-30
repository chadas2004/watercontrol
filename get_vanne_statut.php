<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "watercontrol";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Erreur de connexion: " . $conn->connect_error);
}

// Lire l’état actuel
$sql = "SELECT status FROM vanne_statut WHERE id = 1";
$result = $conn->query($sql);
$row = $result->fetch_assoc();

echo $row["statut"]; // 1 ou 0

$conn->close();
?>
