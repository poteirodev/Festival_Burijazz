<?php
session_start();
require_once __DIR__ . '/../config/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($usuario === '' || $password === '') {
        $error = 'Completa usuario y contraseña.';
    } else {
        $stmt = $conn->prepare("SELECT id, nombre, usuario, password_hash, rol, activo FROM usuarios_admin WHERE usuario = ? LIMIT 1");
        $stmt->execute([$usuario]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$admin) {
            $error = 'Usuario no encontrado.';
        } elseif ((int)$admin['activo'] !== 1) {
            $error = 'Usuario inactivo.';
        } elseif (!password_verify($password, $admin['password_hash'])) {
            $error = 'Contraseña incorrecta.';
        } else {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_nombre'] = $admin['nombre'];
            $_SESSION['admin_usuario'] = $admin['usuario'];
            $_SESSION['admin_rol'] = $admin['rol'];

            header('Location: /Festivalburijazz-astro/admin/index.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Login Admin</title>
  <style>
    body { font-family: Arial, sans-serif; background: #111; color: #fff; display:flex; justify-content:center; align-items:center; height:100vh; }
    .box { background:#1c1c1c; padding:30px; border-radius:12px; width:350px; }
    input { width:100%; padding:10px; margin:8px 0; border-radius:8px; border:1px solid #444; }
    button { width:100%; padding:12px; border:none; border-radius:8px; background:#d4af37; font-weight:bold; cursor:pointer; }
    .error { color:#ff6b6b; margin-bottom:10px; }
  </style>
</head>
<body>
  <div class="box">
    <h2>Acceso Admin</h2>

    <?php if ($error): ?>
      <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST">
      <input type="text" name="usuario" placeholder="Usuario" required>
      <input type="password" name="password" placeholder="Contraseña" required>
      <button type="submit">Ingresar</button>
    </form>
  </div>
</body>
</html>