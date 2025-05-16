<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "watercontrol");

// Statistiques
$active_users = $conn->query("SELECT COUNT(*) AS total FROM utilisateur WHERE is_active = 1")->fetch_assoc()['total'];
$inactive_users = $conn->query("SELECT COUNT(*) AS total FROM utilisateur WHERE is_active = 0")->fetch_assoc()['total'];

$current_month = date('m');
$previous_month = date('m', strtotime('first day of previous month'));

$current_month_consumption = $conn->query("SELECT SUM(debit) AS total FROM capteurs WHERE MONTH(date_mesure) = $current_month")->fetch_assoc()['total'] ?? 0;
$previous_month_consumption = $conn->query("SELECT SUM(debit) AS total FROM capteurs WHERE MONTH(date_mesure) = $previous_month")->fetch_assoc()['total'] ?? 0;

$daily_result = $conn->query("SELECT DATE(date_mesure) AS date, SUM(debit) AS total FROM capteurs WHERE date_mesure >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) GROUP BY DATE(date_mesure) ORDER BY date_mesure ASC");
$daily_data = [];
while ($row = $daily_result->fetch_assoc()) {
    $daily_data[] = $row;
}

$weekly_result = $conn->query("SELECT WEEK(date_mesure) AS semaine, SUM(debit) AS total FROM capteurs GROUP BY WEEK(date_mesure)");
$weekly_data = [];
while ($row = $weekly_result->fetch_assoc()) {
    $weekly_data[] = $row;
}

$monthly_result = $conn->query("SELECT MONTH(date_mesure) AS mois, SUM(debit) AS total FROM capteurs GROUP BY MONTH(date_mesure)");
$monthly_data = [];
while ($row = $monthly_result->fetch_assoc()) {
    $monthly_data[] = $row;
}

// Recherche utilisateur
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

if ($search) {
    $query = "SELECT id, nom, mail, can_set_seuil, can_control_valve, is_active 
              FROM utilisateur 
              WHERE nom LIKE '%$search%' OR mail LIKE '%$search%'";
} else {
    $query = "SELECT id, nom, mail, can_set_seuil, can_control_valve, is_active FROM utilisateur";
}

$users = $conn->query($query);

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin - Tableau de bord</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/font-awesome/css/font-awesome.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
    </style>
</head>
<body>

<!-- Bouton menu mobile -->
<button class="toggle-btn" onclick="toggleSidebar()">☰</button>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <h4><i class="fa fa-tint"></i> WaterControl</h4>
    <a href="dashboard_admin.php" class="active"><i class="fa fa-tachometer"></i> Tableau de bord</a>
    <a href="user.php"><i class="fa fa-users"></i> Utilisateurs</a>
    <a href="message.php"><i class="fa fa-envelope"></i> Messages</a>
    <a href="logout.php" onclick="return confirmLogout();"><i class="fa fa-sign-out"></i> Déconnexion</a>
    </div>

<!-- Contenu principal -->
<div class="content">
    <div class="container-fluid">
        <h2 class="mb-4 text-primary">Tableau de bord - Administrateur</h2>

        <!-- Recherche -->
        <form method="get" class="row g-3 mb-4">
            <div class="col-md-6">
                <input type="text" name="search" class="form-control" placeholder="Rechercher par nom ou email" value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-2 d-grid">
                <button type="submit" class="btn btn-success">
                    <i class="fa fa-search me-1"></i> Recherche
                </button>
            </div>
            <div class="col-md-2 d-grid">
                <a href="dashboard_admin.php" class="btn btn-outline-secondary">
                    <i class="fa fa-refresh me-1"></i> Réinitialiser
                </a>
            </div>


        </form>

        <!-- Statistiques -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card p-3 text-center bg-light">
                    <h5 class="text-success">Utilisateurs Actifs</h5>
                    <h3><?= $active_users ?></h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-3 text-center bg-light">
                    <h5 class="text-danger">Utilisateurs Inactifs</h5>
                    <h3><?= $inactive_users ?></h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-3 bg-light">
                    <h5 class="text-primary">Consommation</h5>
                    <p><strong>Mois actuel :</strong> <?= round($current_month_consumption, 2) ?> L</p>
                    <p><strong>Mois précédent :</strong> <?= round($previous_month_consumption, 2) ?> L</p>
                </div>
            </div>
        </div>

        <!-- Graphiques -->
        <div class="row mb-5">
            <div class="col-md-4">
                <div class="card p-3">
                    <h6 class="text-center">Consommation quotidienne</h6>
                    <canvas id="dailyChart" height="200"></canvas>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-3">
                    <h6 class="text-center">Consommation hebdomadaire</h6>
                    <canvas id="weeklyChart" height="200"></canvas>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-3">
                    <h6 class="text-center">Consommation mensuelle</h6>
                    <canvas id="monthlyChart" height="200"></canvas>
                </div>
            </div>
            
        </div>

        <!-- Liste des utilisateurs -->
        <div class="card p-3">
            <h5>Liste des utilisateurs</h5>
            <div class="table-responsive">
            <table class="table table-bordered table-hover mt-3">
            <thead class="table-secondary">
                <tr>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Peut définir seuil</th>
                    <th>Peut contrôler vanne</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($u = $users->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($u['nom']) ?></td>
                        <td><?= htmlspecialchars($u['mail']) ?></td>
                        <td><?= $u['can_set_seuil'] ? 'Oui' : 'Non' ?></td>
                        <td><?= $u['can_control_valve'] ? 'Oui' : 'Non' ?></td>
                        <td><?= $u['is_active'] ? '<span class="text-success">Actif</span>' : '<span class="text-danger">Inactif</span>' ?></td>
                        <td>
                            
                            <a href="edit_user.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-warning">Modifier</a>
                            <a href="delete_user.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer cet utilisateur ?')">Supprimer</a>
                            <?php if ($u['is_active']): ?>
                                <a href="toggle_user.php?id=<?= $u['id'] ?>&action=deactivate" class="btn btn-sm btn-secondary">Désactiver</a>
                            <?php else: ?>
                                <a href="toggle_user.php?id=<?= $u['id'] ?>&action=activate" class="btn btn-sm btn-success">Activer</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

            </div>
        </div>
    </div>
</div>

<!-- Script ChartJS + Menu toggle -->
<script>
    const dailyData = <?= json_encode($daily_data) ?>;
    const weeklyData = <?= json_encode($weekly_data) ?>;
    const monthlyData = <?= json_encode($monthly_data) ?>;

    new Chart(document.getElementById('dailyChart'), {
        type: 'line',
        data: {
            labels: dailyData.map(d => d.date),
            datasets: [{
                label: 'Jours',
                data: dailyData.map(d => d.total),
                borderColor: 'blue',
                backgroundColor: 'rgba(0,123,255,0.2)',
                fill: true
            }]
        }
    });

    new Chart(document.getElementById('weeklyChart'), {
        type: 'bar',
        data: {
            labels: weeklyData.map(d => 'Semaine ' + d.semaine),
            datasets: [{
                label: 'Hebdo (L)',
                data: weeklyData.map(d => d.total),
                backgroundColor: 'rgba(40,167,69,0.8)'
            }]
        }
    });

    new Chart(document.getElementById('monthlyChart'), {
        type: 'line',
        data: {
            labels: monthlyData.map(d => 'Mois ' + d.mois),
            datasets: [{
                label: 'Mensuelle (L)',
                data: monthlyData.map(d => d.total),
                borderColor: 'orange',
                backgroundColor: 'rgba(255,165,0,0.2)',
                fill: true
            }]
        }
    });

    // Gestion du menu mobile
    const menuToggle = document.getElementById('menu-toggle');
    const sidebar = document.getElementById('sidebar');

    menuToggle.addEventListener('click', (e) => {
        e.stopPropagation(); // Empêche la propagation
        sidebar.classList.toggle('active');
    });

    // Clique extérieur pour fermer la sidebar
    document.addEventListener('click', (e) => {
        if (window.innerWidth <= 768 && sidebar.classList.contains('active')) {
            if (!sidebar.contains(e.target) && e.target !== menuToggle) {
                sidebar.classList.remove('active');
            }
        }
    });
</script>

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