<?php
session_start();
$conn = new mysqli("localhost", "root", "", "watercontrol");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $mail = $_POST['mail'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, password, role FROM utilisateur WHERE mail = ?");
    $stmt->bind_param("s", $mail);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 1) {
        $user = $res->fetch_assoc();
        if (password_verify($password, $user['password']) && $user['role'] === 'admin') {
            $_SESSION['admin_id'] = $user['id'];
            header("Location: dashboard_admin.php");
            exit;
        } else {
            $error = "Identifiants incorrects ou accès refusé.";
        }
    } else {
        $error = "Utilisateur non trouvé.";
    }
}
?>

<!-- HTML -->
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h2 class="text-center">Connexion Administrateur</h2>
        <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
        <form method="POST" class="mt-4">
            <div class="mb-3">
                <label>Email</label>
                <input type="mail" name="mail" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Mot de passe</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Se connecter</button>
        </form>
    </div>
</body>
</html>
