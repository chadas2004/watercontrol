<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

$pdo = new PDO('mysql:host=localhost;dbname=watercontrol', 'root', '');
$query = $pdo->prepare("SELECT nom, image, abonnement FROM utilisateur WHERE id = :id");
$query->bindParam(':id', $_SESSION['user_id']);
$query->execute();
$user = $query->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("Utilisateur introuvable.");
}

// Vérifier l'abonnement de l'utilisateur
if ($user['abonnement'] != 'premium') {
    // Rediriger l'utilisateur vers une page d'erreur ou vers la page d'upgrade
    header("Location: abonnement.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>WaterControl Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="assets/fontawesome/css/all.min.css"/>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"/>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    @keyframes clignote { 0% {opacity: 1;} 100% {opacity: 0;} }
    .clignotant { animation: clignote 1s infinite alternate; }
    .fixed-alert { position: fixed; bottom: 0; left: 0; right: 0; z-index: 1050; border-radius: 0; }
    .sidebar {
      min-height: 100vh;
      background-color: rgb(32, 22, 22);
      transition: transform 0.3s ease;
      position: fixed;
      top: 0; left: 0;
      width: 250px;
      z-index: 1040;
    }

    body {
        font-family: Arial, sans-serif;
    }
    .sidebar a { color: white; text-decoration: none; }
    .sidebar a:hover, .sidebar a.active { color: rgb(234, 237, 242); font-weight: bold; }
    .sidebar i { color: rgb(64, 14, 244); margin-right: 15px; }
    .overlay {
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.5);
      z-index: 1030;
      display: none;
    }
    .overlay.show { display: block; }
    @media (max-width: 767px) {
      #sidebar { transform: translateX(-100%); }
      #sidebar.show { transform: translateX(0); }
      #toggleSidebar { display: inline-block; }
    }
    @media (min-width: 768px) {
      #toggleSidebar { display: none; }
    }
    .top-navbar {
      position: sticky;
      top: 0;
      background-color: rgb(220, 227, 232);
      padding: 10px 20px;
      z-index: 1020;
    }
    .content-wrapper {
      margin-left: 250px;
      transition: margin 0.3s ease;
    }
    @media (max-width: 767px) {
      .content-wrapper { margin-left: 0; }
    }
    .profile-dropdown { position: relative; }
    .profile-dropdown img {
      width: 40px; height: 40px;
      border-radius: 50%;
      cursor: pointer;
      object-fit: cover;
    }
    .profile-dropdown .dropdown-menu {
      display: none;
      position: absolute;
      top: 110%;
      right: 0;
      background-color: white;
      border: 1px solid #ccc;
      z-index: 1050;
      min-width: 160px;
    }
    .profile-dropdown .dropdown-menu .dropdown-item:hover {
      background-color: #f8f9fa;
    }
    
    /* Ajuster le tableau et le graphique pour les petites tailles d'écran */
    table {
        width: 70%;
        margin: auto;
        border-collapse: collapse;
    }

    th, td {
        border: 2px solid black;
        padding: 10px;
        text-align: center;
    }

    .logo {
  display: block;
  margin: 0 auto 15px;
  width: 70px; /* ajustez selon votre besoin */
  height: 90px;
}
  </style>
</head>

<body class="bg-tertiary text-dark">
  <div id="overlay" class="overlay"></div>

  <!-- Sidebar -->
  <nav id="sidebar" class="sidebar py-4 px-3">
  <img src="logo/logo1.png" alt="Logo WaterControl" class="logo">
    <ul class="nav flex-column">
      <li class="nav-item mb-2"><a href="accueil.php" class="nav-link"><i class="bi bi-house"></i> Accueil</a></li>
      <li class="nav-item mb-2"><a href="historique.php" class="nav-link"><i class="bi bi-clock-history"></i> Historique</a></li>
      <li class="nav-item mb-2"><a href="paramètres.php" class="nav-link"><i class="bi bi-gear"></i> Paramètres</a></li>
      <li class="nav-item mb-2"><a href="profil.php" class="nav-link"><i class="bi bi-person-circle"></i> Profil</a></li>
      <li class="nav-item mb-2"><a href="faq.php" class="nav-link"><i class="bi bi-question-circle"></i> FAQs</a></li>
      <li class="nav-item mb-2"><a href="contact.php" class="nav-link"><i class="bi bi-telephone"></i> Contactez-nous</a></li>

    </ul>
  </nav>

  <!-- Contenu principal -->
  <div class="content-wrapper">
    <!-- Navbar -->
    <div class="top-navbar d-flex justify-content-between align-items-center">
      <div>
        <button class="btn btn-outline-dark me-2" id="toggleSidebar">☰</button>
      </div>
      <div class="profile-dropdown d-flex align-items-center gap-2">
      <img src="uploaded_img/<?php echo !empty($user['image']) ? htmlspecialchars($user['image']) : 'avatar.jpg'; ?>" alt="Avatar" id="profileToggle" />
        <div class="position-relative">
          <span class="fw-semibold"><?php echo htmlspecialchars($user['nom']); ?></span>
          <div class="dropdown-menu shadow rounded" id="profileMenu">
            <a href="#" class="dropdown-item" onclick="confirmLogout(event)">Déconnexion</a>
          </div>
        </div>
      </div>
    </div>

    
    <div class="container-fluid mt-4 px-5">
        <div class="row g-4 mb-4">
            <div class="col-md-2">

            </div>
            <div class="col-md-8">
            <div class="card shadow-sm p-4 equal-card">
        <h2 style="text-align: center;">📊 Historique de Consommation d'Eau</h2>
         <!-- Tableau -->
         <table>
            <thead>
                <tr>
                    <th>Mois</th>
                    <th>Consommation (L)</th>
                </tr>
            </thead>
            <tbody id="historiqueTable"></tbody>
        </table>

        </div>

            </div>
            <div class="col-md-2">

            </div>

        <br><br>

        <div class="col-md-2">

        </div>

        <div class="col-md-8">
        <div class="card shadow-sm p-4 equal-card">
        <h2 class="mt-4" style="text-align: center;">📈 Graphique de Consommation</h2>
        <canvas id="historiqueChart"></canvas>

        </div>

        </div>

        <div class="col-md-2">

        </div>


        

        </div>

    </div>

  </div>

  <?php include 'alert.php'; ?>
  <!-- JS -->
  <script>
        fetch("get_historique.php")
            .then(response => response.json())
            .then(data => {
                let tableBody = document.getElementById("historiqueTable");
                let labels = [];
                let valeurs = [];

                data.forEach(row => {
                    labels.push(row.mois);
                    valeurs.push(row.total_debit);

                    let tr = document.createElement("tr");
                    tr.innerHTML = `<td>${row.mois}</td><td>${row.total_debit} L</td>`;
                    tableBody.appendChild(tr);
                });

                // Affichage du graphique
                new Chart(document.getElementById("historiqueChart"), {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Consommation mensuelle (L)',
                            data: valeurs,
                            backgroundColor: 'rgba(40, 120, 241, 0.6)',
                            borderColor: 'rgb(6, 5, 7)',
                            borderWidth: 1
                        }]
                    }
                });
            });
    </script>



  <script>
    const sidebar = document.getElementById("sidebar");
    const overlay = document.getElementById("overlay");

    document.getElementById("toggleSidebar").addEventListener("click", () => {
      sidebar.classList.toggle("show");
      overlay.classList.toggle("show");
    });

    overlay.addEventListener("click", () => {
      sidebar.classList.remove("show");
      overlay.classList.remove("show");
    });

   

    // Menu Profil
    const profileToggle = document.getElementById("profileToggle");
    const profileMenu = document.getElementById("profileMenu");

    profileToggle.addEventListener("click", (e) => {
      e.stopPropagation();
      profileMenu.style.display = profileMenu.style.display === "block" ? "none" : "block";
    });

    document.addEventListener("click", (e) => {
      if (!profileMenu.contains(e.target)) {
        profileMenu.style.display = "none";
      }
    });

    function confirmLogout(event) {
      event.preventDefault();
      if (confirm("Êtes-vous sûr de vouloir vous déconnecter ?")) {
        window.location.href = "logout.php";
      }
    }
  </script>

<script>
  // Actualiser la page toutes les 10 minutes (600000 ms)
  setInterval(() => {
    location.reload();
  }, 600000);
</script>
</body>
</html>
