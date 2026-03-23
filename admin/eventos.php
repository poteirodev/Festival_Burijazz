<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: /Festivalburijazz-astro/admin/login.php');
    exit;
}
?>
<?php
require_once "../config/db.php";

$sql = "SELECT e.*, p.nombre AS plantilla_nombre
        FROM eventos e
        LEFT JOIN plantillas_asientos p ON e.plantilla_id = p.id
        ORDER BY e.fecha ASC, e.hora ASC";
$stmt = $conn->query($sql);
$eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Admin - Eventos</title>
</head>
<body>
    <h1>Gestión de Eventos</h1>
    <p>
    <a href="crear_evento.php">Crear evento</a> |
    <a href="plantillas.php">Ver plantillas</a>
</p>    

    <table border="1" cellpadding="10" cellspacing="0">
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Fecha</th>
            <th>Hora</th>
            <th>Lugar</th>
            <th>Plantilla</th>
            <th>Acciones</th>
        </tr>

        <?php foreach ($eventos as $evento): ?>
        <tr>
            <td><?= $evento['id'] ?></td>
            <td><?= htmlspecialchars($evento['nombre']) ?></td>
            <td><?= $evento['fecha'] ?></td>
            <td><?= $evento['hora'] ?></td>
            <td><?= htmlspecialchars($evento['lugar']) ?></td>
            <td><?= htmlspecialchars($evento['plantilla_nombre'] ?? 'Sin plantilla') ?></td>
            <td>
                <a href="editar_evento.php?id=<?= $evento['id'] ?>">Editar</a>
                |
                <a href="eliminar_evento.php?id=<?= $evento['id'] ?>" onclick="return confirm('¿Eliminar este evento?')">Eliminar</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>