<?php
session_start();
$conn = new mysqli("localhost", "root", "", "watercontrol");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $mail = $_POST['mail'];
    $password = $_POST['password'];

    // On récupère les infos de l'admin
    $stmt = $conn->prepare("SELECT id, nom, password FROM admin WHERE mail = ?");
    $stmt->bind_param("s", $mail);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 1) {
        $user = $res->fetch_assoc();

        // Comparaison directe du mot de passe (non sécurisé)
        if ($password === $user['password']) {
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_nom'] = $user['nom'];
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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Connexion Admin - WaterControl</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet"> <!-- Pour les icônes -->

  <style>
    :root {
      --primary: #318998;
      --blue: #007BFF;
      --light-gray: #f1f1f1;
      --medium-gray: #ccc;
      --dark-gray: #444;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }

    body {
      background: var(--light-gray);
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }

    .login-box {
      width: 420px;
      padding: 30px 35px;
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
      border: 1px solid var(--medium-gray);
    }

    .login-box h2 {
      text-align: center;
      margin-bottom: 25px;
      color: var(--primary);
    }

    .input-box {
      position: relative;
      margin-bottom: 30px;
    }

    .input-box i {
      position: absolute;
      top: 50%;
      right: 10px;
      transform: translateY(-50%);
      color: var(--dark-gray);
      font-size: 16px;
    }

    .input-box input {
      width: 100%;
      padding: 10px 25px 10px 10px;
      font-size: 15px;
      border: none;
      border-bottom: 2px solid var(--medium-gray);
      background: transparent;
      color: #333;
      outline: none;
    }

    .input-box label {
      position: absolute;
      top: 10px;
      left: 0;
      color: var(--dark-gray);
      font-size: 15px;
      pointer-events: none;
      transition: 0.3s ease all;
    }

    .input-box input:focus + label,
    .input-box input:not(:placeholder-shown) + label {
      top: -12px;
      font-size: 12px;
      color: var(--blue);
    }

    .btn {
      width: 100%;
      padding: 12px;
      background: var(--blue);
      color: white;
      font-weight: 600;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      transition: background 0.3s ease-in-out;
    }

    .btn:hover {
      background: #0056b3;
    }

    .exist {
      text-align: center;
      margin-top: 15px;
    }

    .exist a {
      color: var(--blue);
      text-decoration: none;
    }

    .exist a:hover {
      text-decoration: underline;
    }
    .logo {
  display: block;
  margin: 0 auto 15px;
  width: 80px; /* ajustez selon votre besoin */
  height: auto;
}
  </style>
</head>
<body>

  <div class="login-box">
    <img src="logo.png" alt="Logo WaterControl" class="logo">

    <h2>Connexion Admin</h2>

    <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

    <form method="POST">
      <div class="input-box">
        <input type="email" id="mail" name="mail" class="form-control" required placeholder=" ">
        <label for="mail">Email</label>
        <i class="fas fa-user" aria-hidden="true"></i>
      </div>

      <div class="input-box">
        <input type="password" id="password" name="password" class="form-control" required placeholder=" ">
        <label for="password">Mot de passe</label>
        <i class="fas fa-lock" aria-hidden="true"></i>
      </div>

      <button type="submit" class="btn">Se connecter</button>
    </form>

    <div class="exist">
      <!-- Espace pour message ou lien futur -->
    </div>
  </div>

</body>
</html>
