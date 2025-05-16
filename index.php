<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <title>Connexion WaterControl</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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

        <h2>Connexion</h2>
        <form action="traitement_connexion.php" method="POST">
          <div class="input-box">
            <i class="fas fa-user"></i>
            <input type="text" name="nom" id="nom" required placeholder=" " />
            <label for="nom">Nom complet</label>
          </div>
      
          <div class="input-box">
            <i class="fas fa-key"></i>
            <input type="text" name="code_connexion" id="code_connexion" required placeholder=" " />
            <label for="code_connexion">Code de connexion</label>
          </div>
      
          <button type="submit" class="btn">Se connecter</button>
        </form>
      
        <div class="exist">
          
        <p>
          <span>Vous n'avez pas un compte ?</span>
          <a href="inscription.php">S'inscrire</a>
        </p>

        </div>
      </div>
      
</div>

</body>
</html>