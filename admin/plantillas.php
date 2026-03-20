<?php
require_once "../config/db.php";

$stmt = $conn->query("SELECT * FROM plantillas_asientos ORDER BY id DESC");
$plantillas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Plantillas de asientos</title>
</head>
<body>
    <h1>Plantillas de asientos</h1>

    <p>
        <a href="eventos.php">Volver a eventos</a> |
        <a href="crear_plantilla.php">Crear plantilla</a>
    </p>

    <table border="1" cellpadding="10" cellspacing="0">
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Descripción</th>
            <th>Imagen mapa</th>
        </tr>

        <?php foreach ($plantillas as $plantilla): ?>
        <tr>
            <td><?= $plantilla['id'] ?></td>
            <td><?= htmlspecialchars($plantilla['nombre']) ?></td>
            <td><?= htmlspecialchars($plantilla['descripcion'] ?? '') ?></td>
            <td><?= htmlspecialchars($plantilla['imagen_mapa'] ?? '') ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>