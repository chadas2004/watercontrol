<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

try {
    $pdo = new PDO('mysql:host=localhost;dbname=watercontrol', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// Récupérer les plans
$query = $pdo->prepare("SELECT * FROM abonnement");
$query->execute();
$plans = $query->fetchAll(PDO::FETCH_ASSOC);

// Choix d'un plan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['plan_id'])) {
    $plan_id = $_POST['plan_id'];

    $plan = $pdo->prepare("SELECT * FROM abonnement WHERE id = :id");
    $plan->bindParam(':id', $plan_id, PDO::PARAM_INT);
    $plan->execute();
    $selected_plan = $plan->fetch(PDO::FETCH_ASSOC);

    if ($selected_plan && isset($selected_plan['prix']) && $selected_plan['prix'] > 0) {
        $_SESSION['selected_plan'] = $selected_plan;

        // Rediriger après sélection pour éviter un repost en cas de refresh
        header("Location: abonnement.php");
        exit();
    } else {
        echo "Erreur : Le plan sélectionné est invalide ou n'a pas de prix.";
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Choisissez un Abonnement</title>
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
        <h2 class="text-center mb-4">Choisissez votre abonnement</h2>
        <div class="row justify-content-center g-4">
            <?php foreach ($plans as $plan): ?>
                <?php $isSelected = isset($_SESSION['selected_plan']) && $_SESSION['selected_plan']['id'] == $plan['id']; ?>
                <div class="col-md-4">
                    <div class="card pricing-card shadow-sm border <?= $isSelected ? 'border-success' : ''; ?>">
                        <div class="card-body text-center">
                            <h4 class="card-title">
                                <?= htmlspecialchars($plan['nom']); ?>
                                <?php if ($isSelected): ?>
                                    <span class="popular-badge">Populaire</span>
                                <?php endif; ?>
                            </h4>
                            <h2><?= number_format($plan['prix'], 0, ',', ' '); ?> FCFA <small class="text-muted">/mois</small></h2>
                            <p><?= htmlspecialchars($plan['description']); ?></p>
                            <form method="POST">
                                <input type="hidden" name="plan_id" value="<?= htmlspecialchars($plan['id']); ?>">
                                <button type="submit" class="btn btn-primary">
                                    <?= $isSelected ? 'Abonnement sélectionné' : 'Choisir cet abonnement'; ?>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (isset($_SESSION['selected_plan'])): ?>
            <div class="mt-4">
                <div class="card border-success shadow">
                    <div class="card-body">
                        <h4 class="card-title text-success">Abonnement sélectionné :</h4>
                        <p class="card-text"><strong><?= $_SESSION['selected_plan']['nom']; ?></strong></p>
                        <p class="card-text"><?= $_SESSION['selected_plan']['description']; ?></p>
                        <p class="card-text"><strong><?= number_format($_SESSION['selected_plan']['prix'], 0, ',', ' '); ?> FCFA</strong></p>
                        <button id="pay-button" class="btn btn-success mt-2">Payer maintenant</button>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Script FedaPay -->
    <script src="https://cdn.fedapay.com/checkout.js?v=1.1.7"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const payButton = document.getElementById('pay-button');
            const selectedPlan = <?= json_encode($_SESSION['selected_plan'] ?? null); ?>;

            if (!selectedPlan) {
                console.error("Aucun plan sélectionné !");
                return;
            }

            if (payButton) {
                payButton.addEventListener('click', function (e) {
                    e.preventDefault();

                    if (typeof FedaPay === 'undefined') {
                        alert("Le script FedaPay ne s'est pas chargé !");
                        return;
                    }

                    const widget = FedaPay.init({
                        public_key: 'pk_sandbox_3CwfFvBCzQHvx99YcBij4eMw',
                        transaction: {
                            amount: selectedPlan.prix,
                            description: selectedPlan.nom,
                            currency: {
                                iso: 'XOF'
                            }
                        },
                        onSuccess: function(transaction) {
                            alert('Paiement réussi ! ID : ' + transaction.id);
                            window.location = 'paiement.php?pay=' + transaction.id;
                        },
                        onError: function(error) {
                            alert('Erreur : ' + error.message);
                            console.error("Erreur FedaPay :", error);
                        }
                    });

                    widget.open();
                });
            }
        });
    </script>
</body>
</html>