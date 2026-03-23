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

$stmt = $conn->query("SELECT id, nombre, usuario, email, rol, activo, created_at FROM usuarios_admin ORDER BY id DESC");
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Usuarios Admin</title>
</head>
<body>
  <h1>Usuarios Administradores</h1>
  <p>
    <a href="/Festivalburijazz-astro/admin/crear_usuario.php">Crear usuario</a> |
    <a href="/Festivalburijazz-astro/admin/index.php">Volver al panel</a> |
    <a href="/Festivalburijazz-astro/admin/logout.php">Cerrar sesión</a>
  </p>

  <table border="1" cellpadding="8" cellspacing="0">
    <tr>
      <th>ID</th>
      <th>Nombre</th>
      <th>Usuario</th>
      <th>Email</th>
      <th>Rol</th>
      <th>Activo</th>
      <th>Creado</th>
    </tr>

    <?php foreach ($usuarios as $fila): ?>
      <tr>
        <td><?php echo $fila['id']; ?></td>
        <td><?php echo htmlspecialchars($fila['nombre']); ?></td>
        <td><?php echo htmlspecialchars($fila['usuario']); ?></td>
        <td><?php echo htmlspecialchars($fila['email'] ?? ''); ?></td>
        <td><?php echo htmlspecialchars($fila['rol']); ?></td>
        <td><?php echo (int)$fila['activo'] === 1 ? 'Sí' : 'No'; ?></td>
        <td><?php echo htmlspecialchars($fila['created_at']); ?></td>
      </tr>
    <?php endforeach; ?>
  </table>
</body>
</html>