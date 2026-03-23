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
    <title>Crear plantilla</title>
</head>
<body>
    <h1>Crear plantilla</h1>

    <form action="../actions/crear_plantilla_action.php" method="POST">
        <p>
            <label>Nombre:</label><br>
            <input type="text" name="nombre" required>
        </p>

        <p>
            <label>Descripción:</label><br>
            <textarea name="descripcion"></textarea>
        </p>

        <p>
            <label>Nombre o ruta de imagen del mapa:</label><br>
            <input type="text" name="imagen_mapa" placeholder="ej: mapa-teatro.jpg">
        </p>

        <button type="submit">Guardar plantilla</button>
    </form>

    <p><a href="plantillas.php">Volver</a></p>
</body>
</html>