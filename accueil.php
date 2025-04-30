<?php
session_start();
include 'config.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

$pdo = new PDO('mysql:host=localhost;dbname=watercontrol', 'root', '');
$query = $pdo->prepare("SELECT nom, image FROM utilisateur WHERE id = :id");
$query->bindParam(':id', $_SESSION['user_id']);
$query->execute();
$user = $query->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("Utilisateur introuvable.");
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
    .sidebar a:hover { color: rgb(234, 237, 242); }
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
    @media (min-width: 768px) {
      #alert {
        margin-left: 250px;
        width: calc(100% - 250px);
      }
    }
  </style>
</head>

<body class="bg-tertiary text-dark">
  <div id="overlay" class="overlay"></div>

  <!-- Sidebar -->
  <nav id="sidebar" class="sidebar py-4 px-3">
    <h2 class="text-center text-white mb-4">WaterControl</h2>
    <ul class="nav flex-column">
      <li class="nav-item mb-2"><a href="accueil.php" class="nav-link"><i class="bi bi-house"></i> Accueil</a></li>
      <li class="nav-item mb-2"><a href="historique.php" class="nav-link"><i class="bi bi-clock-history"></i> Historique</a></li>
      <li class="nav-item mb-2"><a href="paramètres.php" class="nav-link"><i class="bi bi-gear"></i> Paramètres</a></li>
      <li class="nav-item mb-2"><a href="profil.php" class="nav-link"><i class="bi bi-person-circle"></i> Profil</a></li>
      <li class="nav-item mb-2"><a href="faq.php" class="nav-link"><i class="bi bi-question-circle"></i> FAQs</a></li>
      <li class="nav-item mb-2"><a href="abonnement.php" class="nav-link"><i class="bi bi-question-circle"></i> Abonnement</a></li>

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

    <!-- Main Content -->
    <main class="container-fluid mt-4 px-4">
      <h3 class="mb-4 fw-bold text-primary">Tableau de Bord</h3>
      <div class="row g-4">
        <div class="col-md-6">
          <div class="card shadow-sm border-0 rounded-4 h-100">
            <div class="card-body d-flex flex-column justify-content-center align-items-center">
              <h5 class="card-title text-primary">Consommation Aujourd'hui</h5>
              <p id="consommationJour" class="display-5 fw-bold text-info">Chargement...</p>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="card shadow-sm border-0 rounded-4 h-100">
            <div class="card-body">
              <h5 class="card-title text-primary mb-3">Évolution sur 7 jours</h5>
              <canvas id="graph" height="180"></canvas>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>

  <!-- Alerte avec contrôle -->
  <div id="alert" class="alert alert-danger text-center fw-bold clignotant fixed-alert d-none" role="alert">
    ⚠️ Attention ! Vous avez dépassé le seuil de consommation !<br>
    <button class="btn btn-sm btn-warning mt-2 me-2" onclick="stopAlertTemporarily()">Arrêter pendant 1 min</button>
    <button class="btn btn-sm btn-danger mt-2" onclick="stopAlertPermanently()">Désactiver l'alerte</button>
  </div>

  <!-- JS -->
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

    fetch("get_statistiques.php")
      .then(response => response.json())
      .then(data => {
        document.getElementById("consommationJour").innerText = data.consommationJour + " L";

        const labels = data.historique.map(row => row.date);
        const valeurs = data.historique.map(row => row.conso);

        new Chart(document.getElementById("graph"), {
          type: 'line',
          data: {
            labels: labels,
            datasets: [{
              label: 'Consommation (L)',
              data: valeurs,
              borderColor: '#0d6efd',
              backgroundColor: 'rgba(64, 96, 166, 0.2)',
              fill: true
            }]
          }
        });

        const now = new Date().getTime();
        const alertStopUntil = parseInt(localStorage.getItem("alertStopUntil")) || 0;
        const alertDisabled = localStorage.getItem("alertDisabled") === "true";

        if (data.alerte && !alertDisabled && now > alertStopUntil) {
          const alertElement = document.getElementById("alert");
          alertElement.classList.remove("d-none");

          const audio = new Audio('beat.mp3');
          audio.loop = true;
          audio.play();

          const message = "Attention, vous avez dépassé le seuil de consommation d'eau.";
          const utterance = new SpeechSynthesisUtterance(message);
          utterance.lang = "fr-FR";
          window.speechSynthesis.speak(utterance);

          setInterval(() => {
            window.speechSynthesis.speak(new SpeechSynthesisUtterance(message));
          }, 10000);
        }
      });

    function stopAlertTemporarily() {
      const now = new Date();
      localStorage.setItem("alertStopUntil", now.getTime() + 1 * 60 * 1000);
      location.reload();
    }

    function stopAlertPermanently() {
      localStorage.setItem("alertDisabled", "true");
      location.reload();
    }

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
</body>
</html>
