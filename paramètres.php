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

$utilisateur_id = $_SESSION['user_id'];

// Récupération des infos utilisateur (nom et image)
$query = $conn->prepare("SELECT nom, image, can_set_seuil, can_control_valve, abonnement FROM utilisateur WHERE id = ?");
$query->bind_param("i", $utilisateur_id);
$query->execute();
$query->store_result();
$query->bind_result($nom, $image, $can_set_seuil, $can_control_valve, $abonnement);
$query->fetch();
$query->close();

if (!$nom) {
    die("Utilisateur introuvable.");
}

// Récupération du seuil
$seuil_query = $conn->prepare("SELECT seuil FROM seuils WHERE utilisateur_id = ?");
$seuil_query->bind_param("i", $utilisateur_id);
$seuil_query->execute();
$seuil_query->bind_result($seuil);
$seuil_query->fetch();
$seuil_query->close();
if (!isset($seuil)) $seuil = 1000;

// Récupération du statut de l'électrovanne
$query_vanne = $conn->prepare("SELECT statut FROM vanne_statut WHERE utilisateur_id = ?");
$query_vanne->bind_param("i", $utilisateur_id);
$query_vanne->execute();
$query_vanne->bind_result($valveStatus);
$query_vanne->fetch();
$query_vanne->close();

// Mise à jour du seuil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $can_set_seuil && isset($_POST['flow-threshold'])) {
    $nouveau_seuil = $_POST['flow-threshold'];
    $update_stmt = $conn->prepare("UPDATE seuils SET seuil = ? WHERE utilisateur_id = ?");
    $update_stmt->bind_param("di", $nouveau_seuil, $utilisateur_id);
    $update_stmt->execute();
    $update_stmt->close();

    echo "<script>alert('Seuil mis à jour avec succès !'); window.location.href = 'paramètres.php';</script>";
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
    .form-label, .form-check-label {
            color: white;
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

 

  <main class="container py-4">
  <div class="row g-4">
    <!-- Carte Seuil -->
    <div class="col-lg-6">
      <div class="card shadow rounded-4 border-0">
        <div class="card-body">
          <h5 class="card-title"><i class="bi bi-speedometer2 me-2"></i>Seuil de consommation</h5>
          <hr>
          <form method="POST">
          <?php if ($can_set_seuil && $abonnement !== 'gratuit'): ?>

              <label for="flow-threshold" class="form-label">Modifier le seuil (L/mois)</label>
              <input type="range" class="form-range" id="flow-threshold" name="flow-threshold" min="100" max="50000" step="200" value="<?= $seuil ?>" oninput="updateFlowValue(this.value)">
              <p class="mt-2">Valeur actuelle : <strong><span id="flow-value"><?= $seuil ?></span> L/mois</strong></p>
              <button type="submit" class="btn btn-primary mt-3">Mettre à jour</button>
              <?php elseif ($abonnement === 'gratuit'): ?>
              <p>Seuil défini par l'administrateur : <strong><?= $seuil ?> L/mois</strong></p>
              <div class="alert alert-info mt-2">
                Fonction réservée aux abonnés premium. <a href="abonnement.php" class="text-decoration-underline">Passer à Premium</a>.
              </div>
            <?php else: ?>
            <p>Seuil défini par l'administrateur : <strong><?= $seuil ?> L/mois</strong></p>
            <div class="alert alert-warning mt-2">Vous n'avez pas l'autorisation de modifier le seuil.</div>
            <?php endif; ?>
          </form>
        </div>
      </div>
    </div>

    <!-- Carte Vanne -->
    <div class="col-lg-6">
      <div class="card shadow rounded-4 border-0">
        <div class="card-body">
          <h5 class="card-title"><i class="bi bi-toggle-on me-2"></i>Contrôle de l'électrovanne</h5>
          <hr>
          <div class="form-check form-switch fs-5">
            <input class="form-check-input" type="checkbox" id="valve-control" <?= $valveStatus == 1 ? 'checked' : '' ?> <?= !$can_control_valve ? 'disabled' : '' ?>>
            <label class="form-check-label ms-2" for="valve-control">Activer/Désactiver</label>
          </div>
          <?php if (!$can_control_valve): ?>
            <div class="alert alert-warning mt-3">Vous n'avez pas l'autorisation de contrôler la vanne.</div>
          <?php endif; ?>
        </div>
      </div>
    </div>

        <div class="card shadow-sm border-0 rounded-4 p-3 mb-4">
      <h5 class="card-title text-primary">Alerte de seuil</h5>
      <p>Si vous avez désactivé les alertes de dépassement de seuil, vous pouvez les réactiver ici.</p>
      <button style="width: 110px;" class="btn btn-success " onclick="reactiverAlertes()">Réactiver les alertes</button>
      </div>


    <!-- Carte Réinitialisation -->
    <div class="col-12">
      <div class="card shadow rounded-4 border-0 bg-danger-subtle">
        <div class="card-body">
          <h5 class="card-title text-danger"><i class="bi bi-arrow-clockwise me-2"></i>Réinitialisation des données</h5>
          <hr>
          <p class="mb-3">Cette action supprimera toutes les consommations enregistrées pour cet utilisateur.</p>
          <form method="POST" action="reset_data.php" onsubmit="return confirm('Confirmer la réinitialisation de vos données ?');">
            <input type="hidden" name="utilisateur_id" value="<?= $utilisateur_id ?>">
            <button type="submit" class="btn btn-outline-danger">Réinitialiser mes données</button>
          </form>
        </div>
      </div>
    </div>
  </div>
  </div>
</main>

<?php include 'alert.php'; ?>

  <!-- JS -->
  <script>
    function updateFlowValue(value) {
        document.getElementById("flow-value").textContent = value;
    }

    document.getElementById("valve-control").addEventListener("change", function () {
        let valveState = this.checked ? 1 : 0;

        <?php if ($can_control_valve): ?>
        fetch("update_vanne.php", {
            method: "POST",
            headers: {"Content-Type": "application/x-www-form-urlencoded"},
            body: "valve=" + valveState
        })
        .then(response => response.text())
        .then(data => console.log("Électrovanne: " + data));
        <?php else: ?>
        alert("Vous n'avez pas l'autorisation de contrôler la vanne.");
        this.checked = !this.checked;
        <?php endif; ?>
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
  function reactiverAlertes() {
    localStorage.removeItem("alertDisabled");
    localStorage.removeItem("alertStopUntil");
    alert("Les alertes ont été réactivées !");
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
