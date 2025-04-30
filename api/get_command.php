<?php
include("../config.php");
$sql = "SELECT statut FROM vanne_statut ORDER BY id DESC LIMIT 1";
$res = mysqli_query($conn, $sql);
$data = mysqli_fetch_assoc($res);
echo $data['statut']; // 0 ou 1
?>