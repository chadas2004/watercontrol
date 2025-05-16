function verifierConsommationEtAlerte() {
    fetch("get_statistiques.php")
      .then(response => response.json())
      .then(data => {
        document.getElementById("consommationJour").innerText = data.consommationJour + " L";
  
        // Graphique (peut être placé ailleurs si tu veux éviter de le recréer)
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
  }
  
  // Appel initial
  verifierConsommationEtAlerte();
  
  // Appel automatique toutes les 5 minutes (300 000 ms)
  setInterval(verifierConsommationEtAlerte, 300000); 
  