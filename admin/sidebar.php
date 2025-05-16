<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "watercontrol");

// Récupérer l'ID de l'utilisateur à afficher
$user_id = isset($_GET['id']) ? $_GET['id'] : 0;

// Récupérer les informations de l'utilisateur
$user_query = $conn->query("SELECT * FROM utilisateur WHERE id = $user_id");
$user = $user_query->fetch_assoc();

// Consommation spécifique de l'utilisateur
$daily_user_consumption = $conn->query("SELECT DATE(date_mesure) AS date, SUM(debit) AS total FROM capteurs WHERE utilisateur_id = $user_id AND date_mesure >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) GROUP BY DATE(date_mesure) ORDER BY date_mesure ASC");
$weekly_user_consumption = $conn->query("SELECT WEEK(date_mesure) AS semaine, SUM(debit) AS total FROM capteurs WHERE utilisateur_id = $user_id GROUP BY WEEK(date_mesure)");
$monthly_user_consumption = $conn->query("SELECT MONTH(date_mesure) AS mois, SUM(debit) AS total FROM capteurs WHERE utilisateur_id = $user_id GROUP BY MONTH(date_mesure)");

// Transformez les résultats en tableaux pour les graphiques
$daily_data_user = [];
while ($row = $daily_user_consumption->fetch_assoc()) {
    $daily_data_user[] = $row;
}

$weekly_data_user = [];
while ($row = $weekly_user_consumption->fetch_assoc()) {
    $weekly_data_user[] = $row;
}

$monthly_data_user = [];
while ($row = $monthly_user_consumption->fetch_assoc()) {
    $monthly_data_user[] = $row;
}
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
    <a href="logout.php"><i class="fa fa-sign-out"></i> Déconnexion</a>
</div>


<div class="content">
    <h2 class="mb-4 text-primary">Consommation de <?= htmlspecialchars($user['nom']) ?></h2>

    <div class="row mb-5">
        <div class="col-md-4">
            <div class="card p-3">
                <h6 class="text-center">Consommation quotidienne</h6>
                <canvas id="dailyUserChart" height="200"></canvas>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3">
                <h6 class="text-center">Consommation hebdomadaire</h6>
                <canvas id="weeklyUserChart" height="200"></canvas>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3">
                <h6 class="text-center">Consommation mensuelle</h6>
                <canvas id="monthlyUserChart" height="200"></canvas>
            </div>
        </div>
    </div>
    
    <div class="card p-3">
        <h5>Informations de l'utilisateur</h5>
        <p><strong>Nom :</strong> <?= htmlspecialchars($user['nom']) ?></p>
        <p><strong>Email :</strong> <?= htmlspecialchars($user['mail']) ?></p>
        <p><strong>Peut définir seuil :</strong> <?= $user['can_set_seuil'] ? 'Oui' : 'Non' ?></p>
        <p><strong>Peut contrôler vanne :</strong> <?= $user['can_control_valve'] ? 'Oui' : 'Non' ?></p>
        <p><strong>Statut :</strong> <?= $user['is_active'] ? '<span class="text-success">Actif</span>' : '<span class="text-danger">Inactif</span>' ?></p>
    </div>
</div>

<script>
    const dailyDataUser = <?= json_encode($daily_data_user) ?>;
    const weeklyDataUser = <?= json_encode($weekly_data_user) ?>;
    const monthlyDataUser = <?= json_encode($monthly_data_user) ?>;

    // Graphique de la consommation quotidienne
    new Chart(document.getElementById('dailyUserChart'), {
        type: 'line',
        data: {
            labels: dailyDataUser.map(d => d.date),
            datasets: [{
                label: 'Consommation quotidienne (L)',
                data: dailyDataUser.map(d => d.total),
                borderColor: 'blue',
                backgroundColor: 'rgba(0,123,255,0.2)',
                fill: true
            }]
        }
    });

    // Graphique de la consommation hebdomadaire
    new Chart(document.getElementById('weeklyUserChart'), {
        type: 'bar',
        data: {
            labels: weeklyDataUser.map(d => 'Semaine ' + d.semaine),
            datasets: [{
                label: 'Consommation hebdomadaire (L)',
                data: weeklyDataUser.map(d => d.total),
                backgroundColor: 'rgba(40,167,69,0.8)'
            }]
        }
    });

    // Graphique de la consommation mensuelle
    new Chart(document.getElementById('monthlyUserChart'), {
        type: 'line',
        data: {
            labels: monthlyDataUser.map(d => 'Mois ' + d.mois),
            datasets: [{
                label: 'Consommation mensuelle (L)',
                data: monthlyDataUser.map(d => d.total),
                borderColor: 'orange',
                backgroundColor: 'rgba(255,165,0,0.2)',
                fill: true
            }]
        }
    });
</script>


<!-- Script ChartJS + Menu toggle -->
<script>


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
    </script>


</body>
</html>