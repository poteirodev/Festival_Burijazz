<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: /Festivalburijazz-astro/admin/login.php');
    exit;
}

if ($_SESSION['admin_rol'] !== 'superadmin') {
    exit('No tienes permisos para entrar aquí.');
}

$error = '';
$ok = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $usuario = trim($_POST['usuario'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $rol = $_POST['rol'] ?? 'operador';

    if ($nombre === '' || $usuario === '' || $password === '') {
        $error = 'Completa los campos obligatorios.';
    } else {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $conn->prepare("INSERT INTO usuarios_admin (nombre, usuario, email, password_hash, rol, activo) VALUES (?, ?, ?, ?, ?, 1)");
            $stmt->execute([$nombre, $usuario, $email, $password_hash, $rol]);
            $ok = 'Usuario creado correctamente.';
        } catch (PDOException $e) {
            $error = 'No se pudo crear el usuario. Puede que el usuario ya exista.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Crear Usuario</title>
</head>
<body>
  <h1>Crear Usuario Admin</h1>
  <p>
    <a href="/Festivalburijazz-astro/admin/usuarios.php">Volver a usuarios</a> |
    <a href="/Festivalburijazz-astro/admin/index.php">Panel</a>
  </p>

  <?php if ($error): ?>
    <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
  <?php endif; ?>

  <?php if ($ok): ?>
    <p style="color:green;"><?php echo htmlspecialchars($ok); ?></p>
  <?php endif; ?>

  <form method="POST">
    <p><input type="text" name="nombre" placeholder="Nombre completo" required></p>
    <p><input type="text" name="usuario" placeholder="Usuario" required></p>
    <p><input type="email" name="email" placeholder="Email"></p>
    <p><input type="password" name="password" placeholder="Contraseña" required></p>
    <p>
      <select name="rol">
        <option value="operador">Operador</option>
        <option value="superadmin">Superadmin</option>
      </select>
    </p>
    <p><button type="submit">Crear usuario</button></p>
  </form>
</body>
</html>