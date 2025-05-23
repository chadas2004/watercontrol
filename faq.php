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

$activePage = basename($_SERVER['PHP_SELF']);
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
    .profile-dropdown .dropdown-menu.show {
      display: block;
    }
    .profile-dropdown .dropdown-item:hover {
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
      <li class="nav-item mb-2">
        <a href="accueil.php" class="nav-link">
          <i class="bi bi-house"></i> Accueil
        </a>
      </li>
      <li class="nav-item mb-2">
        <a href="historique.php" class="nav-link">
          <i class="bi bi-clock-history"></i> Historique
        </a>
      </li>
      <li class="nav-item mb-2">
        <a href="paramètres.php" class="nav-link ">
          <i class="bi bi-gear"></i> Paramètres
        </a>
      </li>
      <li class="nav-item mb-2">
        <a href="profil.php" class="nav-link ">
          <i class="bi bi-person-circle"></i> Profil
        </a>
      </li>
      <li class="nav-item mb-2">
        <a href="faq.php" class="nav-link ">
          <i class="bi bi-question-circle"></i> FAQs
        </a>
      </li>
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

    <!-- Contenu de la FAQ -->
    <div class="container my-5 px-3 px-md-5">
      <h2 class="text-center mb-5 display-6 fw-bold">❓ Foire aux Questions</h2>
      <div class="accordion" id="faqAccordion">
        <?php
        $faqs = [
          "Qu'est-ce que WaterControl ?" => "WaterControl est une plateforme intelligente de gestion de la consommation d'eau.",
          "Comment fonctionne WaterControl ?" => "WaterControl suit votre consommation d'eau en temps réel et vous alerte en cas d'utilisation excessive.",
          "Quels sont les équipements nécessaires pour utiliser WaterControl ?" => "Vous avez besoin d'un capteur de débit d'eau et d'un module Arduino ou ESP32 connecté à Internet.",
          "Puis-je consulter ma consommation journalière ?" => "Oui, votre consommation d'eau est affichée dans le tableau de bord.",
          "Comment configurer une alerte en cas de consommation excessive ?" => "Vous pouvez définir un seuil dans les paramètres et recevoir une alerte visuelle et sonore.",
          "WaterControl fonctionne-t-il avec tous les types de robinets ?" => "Oui, tant que vous utilisez un capteur de débit compatible.",
          "Puis-je utiliser WaterControl sans connexion Internet ?" => "Une connexion est requise pour envoyer des données à l'application, mais le capteur fonctionne en local.",
          "Comment puis-je économiser de l'eau avec WaterControl ?" => "WaterControl vous aide à identifier les fuites et à optimiser votre consommation.",
          "Puis-je partager mes données avec d'autres utilisateurs ?" => "Oui, vous pouvez partager vos statistiques avec votre famille via l'application.",
          "Comment puis-je recevoir des notifications sur ma consommation ?" => "Vous recevrez des alertes par email ou notification push sur votre téléphone.",
          "L'application est-elle compatible avec iOS et Android ?" => "Oui, WaterControl est accessible via une application web et mobile.",
          "Comment voir l'historique de ma consommation ?" => "Dans la section 'Historique', vous pouvez voir vos données sur plusieurs jours.",
          "Comment puis-je modifier mes informations de profil ?" => "Accédez à la section 'Profil' pour mettre à jour vos informations personnelles.",
          "WaterControl peut-il détecter une fuite d'eau ?" => "Oui, le système peut détecter des changements anormaux de consommation indiquant une fuite.",
          "Le système est-il sécurisé ?" => "Oui, toutes vos données sont cryptées et sécurisées.",
          "Puis-je connecter plusieurs capteurs ?" => "Oui, vous pouvez surveiller plusieurs points d'eau en ajoutant plusieurs capteurs.",
          "WaterControl est-il compatible avec les maisons intelligentes ?" => "Oui, il peut être intégré avec d'autres systèmes domotiques.",
          "Comment contacter le support en cas de problème ?" => "Vous pouvez nous contacter via le formulaire de contact ou par email.",
          "Comment réinitialiser mes paramètres ?" => "Dans la section 'Paramètres', cliquez sur 'Réinitialiser' pour revenir aux valeurs par défaut.",
          "WaterControl fonctionne-t-il en cas de coupure de courant ?" => "Oui, si votre capteur est sur batterie ou alimentation de secours, il continue à collecter des données."
        ];
        $index = 0;
        foreach ($faqs as $question => $answer) {
            echo '
            <div class="accordion-item border-0 mb-3 shadow-sm rounded-3">
              <h2 class="accordion-header">
                <button class="accordion-button collapsed bg-white fw-semibold"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#faq' . $index . '"
                        aria-expanded="false"
                        aria-controls="faq' . $index . '">
                  ' . htmlspecialchars($question) . '
                </button>
              </h2>
              <div id="faq' . $index . '" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                <div class="accordion-body text-secondary">
                  ' . htmlspecialchars($answer) . '
                </div>
              </div>
            </div>';
            $index++;
        }
        ?>
      </div>
    </div>
  </div>

  <?php include 'alert.php'; ?>

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

    // Menu Profil
    const profileToggle = document.getElementById("profileToggle");
    const profileMenu = document.getElementById("profileMenu");

    profileToggle.addEventListener("click", (e) => {
      e.stopPropagation();
      profileMenu.classList.toggle("show");
    });

    document.addEventListener("click", (e) => {
      if (!profileMenu.contains(e.target)) {
        profileMenu.classList.remove("show");
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
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
