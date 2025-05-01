<?php
session_start();
include('config.php');

$admin_id = $_SESSION['admin_id'];

// Seul l'utilisateur ID = 1 est autorisé à supprimer
if ($admin_id != 1) {
    echo "Accès refusé. Vous n'avez pas l'autorisation de supprimer un utilisateur.";
    exit();
}

// Vérifier si un ID d'utilisateur a été passé dans l'URL
if (!isset($_GET['id'])) {
    echo "ID utilisateur manquant.";
    exit();
}

$user_to_delete = intval($_GET['id']);

// Empêcher la suppression de l'utilisateur admin (ID = 1)
if ($user_to_delete == 1) {
    echo "Impossible de supprimer l'administrateur principal.";
    exit();
}

// Supprimer l'utilisateur de la table `utilisateur`
$stmt = $conn->prepare("DELETE FROM utilisateur WHERE id = ?");
$stmt->bind_param("i", $user_to_delete);
if ($stmt->execute()) {
   

    // Redirection vers la liste des utilisateurs
    header("Location: dashboard_admin.php");
    exit();
} else {
    echo "Erreur lors de la suppression.";
}
?>
