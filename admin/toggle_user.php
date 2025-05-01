<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "watercontrol");

if (isset($_GET['id']) && isset($_GET['action'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'] === 'deactivate' ? 0 : 1;

    $stmt = $conn->prepare("UPDATE utilisateur SET is_active = ? WHERE id = ?");
    $stmt->bind_param("ii", $action, $id);
    $stmt->execute();
}

header("Location: dashboard_admin.php");
exit();
?>
