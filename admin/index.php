<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: /Festivalburijazz-astro/admin/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Panel Admin</title>
</head>
<body>
  <h1>Panel de Administración</h1>
  <p>Bienvenido, <?php echo htmlspecialchars($_SESSION['admin_nombre']); ?>.</p>
  <p>Rol: <?php echo htmlspecialchars($_SESSION['admin_rol']); ?></p>

  <ul>
    <li><a href="eventos.php">Eventos</a></li>
    <li><a href="plantillas.php">Plantillas</a></li>
    <li><a href="usuarios.php">Usuarios</a></li>
    <li><a href="logout.php">Cerrar sesión</a></li>
  </ul>
</body>
</html>