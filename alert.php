<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Page avec Alerte</title>
  <style>
    .clignotant {
      animation: clignote 1s infinite alternate;
    }

    @keyframes clignote {
      0% { opacity: 1; }
      100% { opacity: 0; }
    }

    .fixed-alert {
      position: fixed;
      bottom: 0;
      left: 250px; /* largeur de ta sidebar */
      right: 0;
      z-index: 9999;
      margin: 0;
      padding: 1rem;
      border-radius: 0;
      background-color: #f8d7da;
      color: #842029;
      border-top: 2px solid #f5c6cb;
      text-align: center;
      font-weight: bold;
    }

    .btn {
      display: inline-block;
      margin: 0.5rem 0.5rem 0 0;
      padding: 0.3rem 0.8rem;
      font-size: 0.85rem;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }

    .btn-warning {
      background-color: #ffc107;
      color: #000;
    }

    .btn-danger {
      background-color: #dc3545;
      color: #fff;
    }

    .d-none {
      display: none !important;
    }

    body {
      margin-bottom: 80px; /* espace pour que le contenu ne soit pas masqué */
    }

    @media (max-width: 768px) {
      .fixed-alert {
        left: 0; /* sur mobile, la sidebar est souvent cachée */
      }
    }
  </style>
</head>
<body>

  <!-- Alerte -->
  <div id="alert" class="fixed-alert clignotant d-none">
    ⚠️ Attention ! Vous avez dépassé le seuil de consommation !<br>
    <button class="btn btn-warning" onclick="stopAlertTemporarily()">Arrêter 1 min</button>
    <button class="btn btn-danger" onclick="stopAlertPermanently()">Désactiver</button>
  </div>

  <script>
    fetch("get_statistiques.php")
      .then(response => response.json())
      .then(data => {
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
      localStorage.setItem("alertStopUntil", now.getTime() + 60 * 1000);
      location.reload();
    }

    function stopAlertPermanently() {
      localStorage.setItem("alertDisabled", "true");
      location.reload();
    }
  </script>
</body>
</html>
