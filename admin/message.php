<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "watercontrol");

// Suppression d’un message si demandé
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM contacts WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: message.php?deleted=1");
    exit();
}

// Récupération des messages
$result = $conn->query("SELECT * FROM contacts ORDER BY date_envoi DESC");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Messages - WaterControl Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/font-awesome/css/font-awesome.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar {
            height: 100vh;
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #1e1e2f;
            color: white;
            padding-top: 20px;
        }
        .sidebar h4 { text-align: center; margin-bottom: 30px; }
        .sidebar a {
            color: #dcdcdc;
            padding: 12px 20px;
            text-decoration: none;
            display: block;
        }
        .sidebar a:hover, .sidebar a.active {
            background-color: #343a40;
            color: #fff;
        }
        .content {
            margin-left: 250px;
            padding: 30px;
        }
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); z-index: 1000; }
            .sidebar.active { transform: translateX(0); }
            .content { margin-left: 0; }
            #menu-toggle { display: block; }
        }
        #menu-toggle {
            display: none;
            position: fixed;
            top: 10px;
            left: 10px;
            font-size: 24px;
            background: none;
            border: none;
            color: #000;
            z-index: 1100;
        }

        .logo {
  display: block;
  margin: 0 auto 15px;
  width: 70px; /* ajustez selon votre besoin */
  height: 90px;
}
    </style>
</head>
<body>

<button class="toggle-btn" onclick="toggleSidebar()">☰</button>

<div class="sidebar" id="sidebar">
<img src="logo.png" alt="Logo WaterControl" class="logo">
    <a href="dashboard_admin.php" class="active"><i class="fa fa-tachometer"></i> Tableau de bord</a>
    <a href="user.php"><i class="fa fa-users"></i> Utilisateurs</a>
    <a href="message.php"><i class="fa fa-envelope"></i> Messages</a>
    <a href="logout.php" onclick="return confirmLogout();"><i class="fa fa-sign-out"></i> Déconnexion</a>
    </div>

<div class="content">
    <div class="container-fluid">
        <h2 class="mb-4 text-primary">Messages Reçus</h2>

        <?php if (isset($_GET['deleted'])): ?>
            <div class="alert alert-success">Message supprimé avec succès.</div>
        <?php endif; ?>

        <?php if ($result->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-secondary">
                        <tr>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Message</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($msg = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($msg['nom']) ?></td>
                                <td><?= htmlspecialchars($msg['email']) ?></td>
                                <td><?= nl2br(htmlspecialchars($msg['message'])) ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($msg['date_envoi'])) ?></td>
                                <td>
                                    <a href="message.php?delete=<?= $msg['id'] ?>"
                                       class="btn btn-danger btn-sm"
                                       onclick="return confirm('Supprimer ce message ?');">
                                        Supprimer
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">Aucun message reçu pour le moment.</div>
        <?php endif; ?>
    </div>
</div>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('active');
    }

    function confirmLogout() {
        return confirm("Êtes-vous sûr de vouloir vous déconnecter ?");
    }
</script>

</body>
</html>
