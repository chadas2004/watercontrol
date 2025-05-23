<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "watercontrol");

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $can_set_seuil = isset($_POST['can_set_seuil']) ? 1 : 0;
        $can_control_valve = isset($_POST['can_control_valve']) ? 1 : 0;
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        $stmt = $conn->prepare("UPDATE utilisateur SET can_set_seuil = ?, can_control_valve = ?, is_active = ? WHERE id = ?");
        $stmt->bind_param("iiii", $can_set_seuil, $can_control_valve, $is_active, $id);
        $stmt->execute();

        header("Location: dashboard_admin.php");
        exit();
    }

    $stmt = $conn->prepare("SELECT nom, mail, can_set_seuil, can_control_valve, is_active FROM utilisateur WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_assoc();
} else {
    die("ID utilisateur non fourni.");
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier utilisateur</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/font-awesome/css/font-awesome.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
        }

        .sidebar {
    transition: transform 0.3s ease-in-out;
            }

        .sidebar {
            height: 100vh;
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #1e1e2f;
            color: white;
            padding-top: 20px;
            transition: transform 0.3s ease-in-out;
        }
        .sidebar h4 {
            text-align: center;
            margin-bottom: 30px;
        }
        .sidebar a {
            color: #dcdcdc;
            padding: 12px 20px;
            text-decoration: none;
            display: block;
        }
        .sidebar a:hover,
        .sidebar a.active {
            background-color: #343a40;
            color: #fff;
        }
        .content {
            margin-left: 250px;
            padding: 30px;
            transition: margin-left 0.3s ease-in-out;
        }
        .card {
            border: none;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 10px;
        }
        table th, table td {
            vertical-align: middle !important;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                position: fixed;
                z-index: 1000;
            }
            .sidebar.active {
                transform: translateX(0);
            }
            .content {
                margin-left: 0;
            }
            #menu-toggle {
                display: block;
            }
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
<!-- Sidebar -->
<div class="sidebar" id="sidebar">
<img src="logo.png" alt="Logo WaterControl" class="logo">
    <a href="dashboard_admin.php" class="active"><i class="fa fa-tachometer"></i> Tableau de bord</a>
    <a href="user.php"><i class="fa fa-users"></i> Utilisateurs</a>
    <a href="message.php"><i class="fa fa-envelope"></i> Messages</a>
    <a href="logout.php" onclick="return confirmLogout();"><i class="fa fa-sign-out"></i> Déconnexion</a>
    </div>

    <div class="content">
        <h2 class="mb-4">Modifier l'utilisateur : <?= htmlspecialchars($user['nom']) ?></h2>
        <form method="POST" class="bg-light p-4 rounded shadow-sm">
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="can_set_seuil" id="seuil" <?= $user['can_set_seuil'] ? 'checked' : '' ?>>
                <label class="form-check-label" for="seuil">Peut définir un seuil</label>
            </div>
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="can_control_valve" id="valve" <?= $user['can_control_valve'] ? 'checked' : '' ?>>
                <label class="form-check-label" for="valve">Peut contrôler la vanne</label>
            </div>
            <div class="form-check mb-4">
                <input class="form-check-input" type="checkbox" name="is_active" id="active" <?= $user['is_active'] ? 'checked' : '' ?>>
                <label class="form-check-label" for="active">Compte actif</label>
            </div>
            <button type="submit" class="btn btn-success">💾 Enregistrer</button>
            <a href="dashboard_admin.php" class="btn btn-secondary">↩️ Annuler</a>
        </form>
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
