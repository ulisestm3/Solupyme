<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$errorMsg = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : null;
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>AWFerreteria · Iniciar sesión</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../public/css/custom.css">
  <style>
    body {
      background-color: #f0f2f5;
      font-family: 'Segoe UI', sans-serif;
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      font-size: 16px;
      padding: 10px;
    }
    .card-login {
      width: 100%;
      max-width: 400px;
      padding: 2rem;
      border-radius: 12px;
      box-shadow: 0 0 25px rgba(0, 0, 0, 0.1);
      background-color: white;
    }
    .card-login h2 {
      text-align: center;
      color: #004080;
      margin-bottom: 1.5rem;
      font-weight: bold;
    }
    .form-control {
      border-radius: 8px;
    }
    .btn-login {
      background-color: #004080;
      color: white;
      border-radius: 8px;
      font-weight: 500;
    }
    .btn-login:hover {
      background-color: #0066cc;
    }
    .error-box {
      background-color: #f8d7da;
      color: #842029;
      padding: .75rem;
      border-radius: 6px;
      margin-bottom: 1rem;
      text-align: center;
    }
    @media (min-width: 768px) {
      body {
        font-size: 18px;
        padding: 20px;
      }
    }
  </style>
</head>
<body>
  <div class="card-login">
    <h2>AWFerreteria</h2>

    <?php if ($errorMsg): ?>
      <div class="error-box"><?= $errorMsg ?></div>
    <?php endif; ?>

    <form method="POST" action="auth/login.php">
      <div class="mb-3">
        <label for="usuario" class="form-label">Usuario</label>
        <input type="text" class="form-control" id="usuario" name="usuario" required autofocus>
      </div>
      <div class="mb-3">
        <label for="password" class="form-label">Contraseña</label>
        <div class="input-group">
          <input type="password" class="form-control" id="password" name="password" required>
          <span class="input-group-text" id="togglePassword" style="cursor:pointer;">👁️</span>
        </div>
      </div>
      <button type="submit" class="btn btn-login w-100">Entrar</button>
    </form>

    <div class="text-center mt-3">
      ¿No tienes cuenta? <a href="/views/auth/registro.php" style="color:#004080; font-weight:bold;">Regístrate</a>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.getElementById("togglePassword").addEventListener("click", function () {
      const input = document.getElementById("password");
      input.type = input.type === "password" ? "text" : "password";
    });
  </script>
</body>
</html>
