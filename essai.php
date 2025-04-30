<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Plans d'abonnement</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f9f9f9;
    }
    .pricing-card {
      border-radius: 15px;
      transition: transform 0.3s;
    }
    .pricing-card:hover {
      transform: scale(1.02);
    }
    .popular-badge {
      background-color: #00b894;
      color: white;
      padding: 2px 8px;
      font-size: 0.8rem;
      border-radius: 10px;
      margin-left: 5px;
    }
  </style>
</head>
<body>
  <div class="container py-5">
    <h2 class="text-center mb-4">Choisissez votre plan</h2>
    <div class="row justify-content-center g-4">
      
      <!-- Gratuit -->
      <div class="col-md-4">
        <div class="card pricing-card shadow-sm">
          <div class="card-body text-center">
            <h4 class="card-title">Gratuit</h4>
            <h2>$0 <small class="text-muted">/mois</small></h2>
            <p>Découvrez comment l’IA peut vous aider dans vos tâches quotidiennes</p>
            <button class="btn btn-outline-secondary" disabled>Votre plan actuel</button>
            <ul class="list-unstyled mt-3 text-start">
              <li>✓ Accès à GPT-4o mini</li>
              <li>✓ Mode vocal standard</li>
              <li>✓ Recherches Web</li>
              <li>✓ Accès limité aux fichiers/images</li>
              <li>✓ Utilisation GPT personnalisés</li>
            </ul>
          </div>
        </div>
      </div>

      <!-- ChatGPT Plus -->
      <div class="col-md-4">
        <div class="card pricing-card shadow border-success border-2">
          <div class="card-body text-center">
            <h4 class="card-title">
              ChatGPT Plus <span class="popular-badge">Populaire</span>
            </h4>
            <h2>$20 <small class="text-muted">/mois</small></h2>
            <p>Gagnez en productivité et en créativité avec un accès étendu</p>
            <button class="btn btn-success" onclick="abonner('Plus')">Obtenir ChatGPT Plus</button>
            <ul class="list-unstyled mt-3 text-start">
              <li>✓ Toutes les fonctionnalités gratuites</li>
              <li>✓ Accès étendu fichiers/images</li>
              <li>✓ Recherche approfondie</li>
              <li>✓ Vidéos Sora (limité)</li>
              <li>✓ Test des nouvelles fonctionnalités</li>
            </ul>
          </div>
        </div>
      </div>

      <!-- Pro -->
      <div class="col-md-4">
        <div class="card pricing-card shadow-sm">
          <div class="card-body text-center">
            <h4 class="card-title">Pro</h4>
            <h2>$200 <small class="text-muted">/mois</small></h2>
            <p>Tirez le meilleur parti d’OpenAI avec le niveau d’accès le plus élevé</p>
            <button class="btn btn-dark" onclick="abonner('Pro')">Obtenir ChatGPT Pro</button>
            <ul class="list-unstyled mt-3 text-start">
              <li>✓ Toutes les fonctionnalités de Plus</li>
              <li>✓ Accès illimité à tous les modèles</li>
              <li>✓ Recherche multi-étapes</li>
              <li>✓ Vidéos Sora étendues</li>
              <li>✓ Mode Pro 01</li>
            </ul>
          </div>
        </div>
      </div>

    </div>
  </div>

  <script>
    function abonner(plan) {
      alert(`Vous avez sélectionné le plan : ${plan}`);
      // Ici, tu peux rediriger vers une page de paiement par exemple
    }
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>