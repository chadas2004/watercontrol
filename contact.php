<?php
// Initialisation de la session
session_start();
include 'config.php';

// Redirection si non connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

// Récupération des informations utilisateur
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
  <title>Contact - WaterControl</title>

  <!-- Feuilles de style -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="assets/fontawesome/css/all.min.css"/>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"/>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>

  <style>
    /* ----------- Styles généraux ----------- */
    body {
  background: #f1f5f9;
  margin: 0;
  padding: 0;
  /* font-family: 'Poppins', sans-serif; */
}


    /* Sidebar */
    .sidebar {
      min-height: 100vh;
      background-color: rgb(32, 22, 22);
      position: fixed;
      top: 0; left: 0;
      width: 250px;
      z-index: 1040;
      padding: 20px 15px;
      transition: transform 0.3s ease;
    }
    .sidebar a { color: white; text-decoration: none; }
    .sidebar a:hover, .sidebar a.active {
      color: rgb(234, 237, 242);
      font-weight: bold;
    }
    .sidebar i { color: rgb(64, 14, 244); margin-right: 15px; }

    /* Overlay (mobile) */
    .overlay {
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.5);
      z-index: 1030;
      display: none;
    }
    .overlay.show { display: block; }

    /* Navbar */
    .top-navbar {
      position: sticky;
      top: 0;
      background-color: rgb(220, 227, 232);
      padding: 10px 20px;
      z-index: 1020;
    }

    /* Responsive */
    .content-wrapper {
      margin-left: 250px;
      transition: margin 0.3s ease;
    }
    .profile-dropdown {
      margin-left: auto; /* Pousse le div vers la droite */
    }
    
    @media (max-width: 767px) {
      #sidebar { transform: translateX(-100%); }
      #sidebar.show { transform: translateX(0); }
      .content-wrapper { margin-left: 0; }
      #toggleSidebar { display: inline-block; }
    }
    @media (min-width: 768px) {
      #toggleSidebar { display: none; }
    }

    /* Profil */
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
    .profile-dropdown .dropdown-menu.show { display: block; }

    /* Boîte de contact */
    .contact-box {
      background: #fff;
      padding: 40px;
      border-radius: 12px;
      box-shadow: 0 8px 20px rgba(0,0,0,0.1);
      max-width: 600px;
      width: 100%;
      margin: 40px auto;
    }
    .contact-box h2 {
      text-align: center;
      margin-bottom: 25px;
      color: #0d6efd;
    }
    .input-group {
      margin-bottom: 20px;
      position: relative;
    }
    .input-group input, .input-group textarea {
      width: 100%;
      padding: 12px 15px;
      font-size: 15px;
      border: 1px solid #ccc;
      border-radius: 6px;
      outline: none;
    }
    .input-group label {
      position: absolute;
      top: -8px;
      left: 12px;
      background: white;
      padding: 0 6px;
      font-size: 14px;
    }
    .btn {
      width: 100%;
      padding: 12px;
      background: #0d6efd;
      color: white;
      font-weight: 600;
      border: none;
      border-radius: 6px;
    }
    .btn:hover { background: #0056b3; }
    .contact-info {
      margin-top: 25px;
      font-size: 14px;
      color: #555;
    }
    .logo {
  display: block;
  margin: 0 auto 15px;
  width: 70px; /* ajustez selon votre besoin */
  height: 90px;
}

    .contact-info i { margin-right: 8px; color: #0d6efd; }
  </style>
</head>

<body>
  <!-- Overlay mobile -->
  <div id="overlay" class="overlay"></div>

  <!-- Sidebar -->
  <nav id="sidebar" class="sidebar">
  <img src="logo/logo1.png" alt="Logo WaterControl" class="logo">
    <ul class="nav flex-column">
      <li class="nav-item mb-2"><a href="accueil.php" class="nav-link <?= ($activePage === 'accueil.php') ? 'active' : '' ?>"><i class="bi bi-house"></i> Accueil</a></li>
      <li class="nav-item mb-2"><a href="historique.php" class="nav-link <?= ($activePage === 'historique.php') ? 'active' : '' ?>"><i class="bi bi-clock-history"></i> Historique</a></li>
      <li class="nav-item mb-2"><a href="paramètres.php" class="nav-link <?= ($activePage === 'paramètres.php') ? 'active' : '' ?>"><i class="bi bi-gear"></i> Paramètres</a></li>
      <li class="nav-item mb-2"><a href="profil.php" class="nav-link <?= ($activePage === 'profil.php') ? 'active' : '' ?>"><i class="bi bi-person-circle"></i> Profil</a></li>
      <li class="nav-item mb-2"><a href="faq.php" class="nav-link <?= ($activePage === 'faq.php') ? 'active' : '' ?>"><i class="bi bi-question-circle"></i> FAQs</a></li>
      <li class="nav-item mb-2"><a href="contact.php" class="nav-link"><i class="bi bi-telephone"></i> Contactez-nous</a></li>

    </ul>
  </nav>

  <!-- Contenu principal -->
  <div class="content-wrapper">
    <!-- Top navbar -->
    <div class="top-navbar d-flex justify-content-between align-items-center">
      <button class="btn btn-outline-dark" id="toggleSidebar">☰</button>
      <div class="profile-dropdown d-flex align-items-center gap-2">
        <img src="uploaded_img/<?php echo !empty($user['image']) ? htmlspecialchars($user['image']) : 'avatar.jpg'; ?>" alt="Avatar" id="profileToggle" />
        <span class="fw-semibold"><?php echo htmlspecialchars($user['nom']); ?></span>
        <div class="dropdown-menu shadow rounded" id="profileMenu">
          <a href="#" class="dropdown-item" onclick="confirmLogout(event)">Déconnexion</a>
        </div>
      </div>
    </div>

    <!-- Formulaire de contact -->
    <div class="contact-box">
      <h2>Contactez-nous</h2>
      <form action="traitement_contact.php" method="POST">
        <div class="input-group">
          <label for="nom"><i class="fas fa-user"></i> Nom</label>
          <input type="text" id="nom" name="nom" required>
        </div>
        <div class="input-group">
          <label for="email"><i class="fas fa-envelope"></i> Email</label>
          <input type="email" id="email" name="email" required>
        </div>
        <div class="input-group">
          <label for="message"><i class="fas fa-message"></i> Message</label>
          <textarea id="message" name="message" rows="5" required></textarea>
        </div>
        <button type="submit" class="btn">Envoyer le message</button>
      </form>

      <div class="contact-info mt-4">
        <p><i class="fas fa-phone"></i> +229 90 00 00 00</p>
        <p><i class="fas fa-envelope"></i> support@watercontrol.com</p>
        <p><i class="fas fa-map-marker-alt"></i> Bohicon, Bénin</p>
      </div>
    </div>

    <?php include 'alert.php'; ?>
  </div>

  <!-- JS scripts -->
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
    // Affiche un message de succès s'il y a "?success=1" dans l'URL
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('success') === '1') {
      const successDiv = document.createElement('div');
      successDiv.className = 'alert alert-success text-center mt-3';
      successDiv.textContent = 'Votre message a bien été envoyé. Merci de nous avoir contactés !';

      document.querySelector('.contact-box').appendChild(successDiv);

      // Supprime le paramètre de l'URL après 4 secondes
      setTimeout(() => {
        window.history.replaceState(null, '', window.location.pathname);
      }, 4000);
    }
  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
