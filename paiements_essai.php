<?php
session_start();

// Exemple de données simulées pour test
$_SESSION['selected_plan'] = [
    'id' => 1,
    'nom' => 'Test Plan',
    'description' => 'Plan de test pour FedaPay',
    'prix' => 500
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Test Paiement FedaPay</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h2 class="text-center mb-4">Test Paiement avec FedaPay (Mode Sandbox)</h2>
        <div class="card shadow p-4">
            <h4 class="mb-3">Informations du plan</h4>
            <p><strong>Nom :</strong> <?= $_SESSION['selected_plan']['nom']; ?></p>
            <p><strong>Description :</strong> <?= $_SESSION['selected_plan']['description']; ?></p>
            <p><strong>Prix :</strong> <?= number_format($_SESSION['selected_plan']['prix'], 0, ',', ' '); ?> FCFA</p>
            <button id="pay-button" class="btn btn-success">Payer maintenant (Test)</button>
        </div>
    </div>
    <script src="https://cdn.fedapay.com/checkout.js?v=1.1.7"></script>
<script>
const payButton = document.getElementById('pay-button');

payButton.addEventListener('click', function () {
    const widget = FedaPay.init({
        public_key: 'pk_sandbox_3CwfFvBCzQHvx99YcBij4eMw', // Mets ta vraie clé ici
        transaction: {
            amount: 500,
            description: "Test FedaPay",
            currency: { iso: 'XOF' },
            customer: {
                firstname: "Jean",
                lastname: "Test",
                email: "test@example.com",
                phone_number: {
                    number: "97979797",
                    country: "BJ"
                }
            }
        },
        onSuccess: function (transaction) {
            alert('✅ Paiement réussi : ' + transaction.id);
        },
        onError: function (error) {
            alert("❌ Paiement échoué : " + error.message);
            console.error(error);
        }
    });
    widget.open();
});
</script>

</body>
</html>
