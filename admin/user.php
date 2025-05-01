<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "watercontrol");

// Vérification de la connexion
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM utilisateur";
$result = $conn->query($sql);
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
            margin: 0;
            background-color: #f1f5f9;
            padding-left: 250px; /* largeur sidebar */
        }

        .container-fluid {
            padding: 30px;
        }

        .card-header {
            background-color: #0d1b2a;
            color: white;
        }

        .table th, .table td {
            vertical-align: middle;
            text-align: center;
        }

        .table-actions a {
            margin: 0 3px;
        }

        .pagination {
            justify-content: center;
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
    <a href="settings.php"><i class="fa fa-cogs"></i> Paramètres</a>
    <a href="logout.php"><i class="fa fa-sign-out"></i> Déconnexion</a>
</div>



<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h4 class="mb-0">Liste des Utilisateurs</h4>
        </div>
        <div class="card-body">
            <table class="table table-striped table-hover">
                <thead class="table-secondary">
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Adresse</th>
                        <th>Email</th>
                        <th>Peut définir seuil</th>
                        <th>Peut contrôler vanne</th>
                        <th>Actif</th>
                        <th>Abonnement</th>
                        <th>Date d'abonnement</th>
                        <th>Date d'expiration</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td><?= htmlspecialchars($row['nom']) ?></td>
                                <td><?= htmlspecialchars($row['adresse']) ?></td>
                                <td><?= htmlspecialchars($row['mail']) ?></td>
                                <td><?= $row['can_set_seuil'] ? 'Oui' : 'Non' ?></td>
                                <td><?= $row['can_control_valve'] ? 'Oui' : 'Non' ?></td>
                                <td><?= $row['is_active'] ? 'Actif' : 'Inactif' ?></td>
                                <td><?= htmlspecialchars($row['abonnement']) ?></td>
                                <td><?= $row['date_abonnement'] ?></td>
                                <td><?= $row['date_expiration'] ?></td>
                                <td class="table-actions">
                                    <a href="statistique.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-info">Visualiser</a>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="11">Aucun utilisateur trouvé.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Pagination (si nécessaire) -->
            <nav>
                <ul class="pagination">
                    <li class="page-item"><a class="page-link" href="#">Précédent</a></li>
                    <li class="page-item"><a class="page-link" href="#">1</a></li>
                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">Suivant</a></li>
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

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

<?php $conn->close(); ?>


