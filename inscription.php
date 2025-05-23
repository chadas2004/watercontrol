<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <title>Inscription WaterControl</title>
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

    .register-box {
      width: 420px;
      padding: 30px 35px;
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
      border: 1px solid var(--medium-gray);
    }

    .register-box h2 {
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
      right: 10px; /* Positionner à droite */
      transform: translateY(-50%);
      color: var(--dark-gray);
      font-size: 16px;
    }

    .input-box input {
      width: 100%;
      padding: 10px 25px 10px 10px; /* Ajuster padding pour laisser espace à droite */
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

<div class="register-box">
<img src="logo.png" alt="Logo WaterControl" class="logo">
  <h2>Inscription</h2>
  <form action="traitement_inscription.php" method="POST">
    <div class="input-box">
      <i class="fas fa-user"></i>
      <input type="text" name="nom" id="nom" required placeholder=" " />
      <label for="nom">Nom</label>
    </div>
    
    <div class="input-box">
      <i class="fas fa-user"></i>
      <input type="text" name="prenoms" id="prenoms" required placeholder=" " />
      <label for="prenoms">Prénoms</label>
    </div>

    <div class="input-box">
    <i class="fas fa-phone"></i>
    <input type="tel" name="telephone" id="telephone" required placeholder=" " 
           pattern="\+22901[0-9]{8}"  
           title="Le numéro doit commencer par +22901 suivi de 8 chiffres." /> 
    <label for="telephone">Téléphone</label>
</div>


    
    <div class="input-box">
      <i class="fas fa-location-dot"></i>
      <input type="text" name="adresse" id="adresse" required placeholder=" " />
      <label for="adresse">Adresse</label>
    </div>
  
    <div class="input-box">
      <i class="fas fa-envelope"></i>
      <input type="email" name="mail" id="mail" required placeholder=" " />
      <label for="mail">Email</label>
    </div>

   
    
    <button type="submit" class="btn">S'inscrire</button>
    <div class="exist">
        <p>
          <span>Avez-vous déjà un compte ?</span>
          <a href="index.php">Se connecter</a>
        </p>

    </div>
       
  </form>
</div>


</body>
</html>