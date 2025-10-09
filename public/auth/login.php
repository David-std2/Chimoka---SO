<?php
// public/auth/login.php
require_once __DIR__ . '/../../includes/auth.php';

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['username'] ?? '');
    $p = $_POST['password'] ?? '';

    if ($u === '' || $p === '') {
        $err = 'Usuario y contraseña son obligatorios.';
    } else {
        try {
            $ok = login_user($u, $p);
        } catch (Throwable $e) {
            error_log('Login error: ' . $e->getMessage());
            $ok = false;
        }

        if ($ok) {
            header('Location: ../index.php');
            exit;
        } else {
            $err = 'Credenciales incorrectas o error de conexión.';
        }
    }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Login — Chimoka</title>
  <link href="../css/login.css" rel="stylesheet">
</head>
<body>
  <div class="login-card">
    <img src="../assets/LogoChimoka.png" alt="Logo Chimoka" class="login-logo">
    <h4>Iniciar sesión</h4>

    <?php if ($err): ?>
      <div class="alert"><?= htmlspecialchars($err, ENT_QUOTES | ENT_SUBSTITUTE) ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php" novalidate>
      <div class="form-row">
        <label class="form-label">Usuario:</label>
        <input name="username" class="form-control" autofocus required>
      </div>

      <div class="form-row">
        <label class="form-label">Contraseña:</label>
        <input name="password" type="password" class="form-control" required>
      </div>

      <button class="btn-login">Entrar</button>
    </form>

    <div class="login-footer">
      <a href="../index.php">← Volver al inicio</a>
    </div>
  </div>
</body>
</html>




