<?php
require_once "../config/db.php";

$stmtPlantillas = $conn->query("SELECT id, nombre FROM plantillas_asientos ORDER BY nombre ASC");
$plantillas = $stmtPlantillas->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear evento</title>
</head>
<body>
    <h1>Crear evento</h1>

    <form action="../actions/crear_evento_action.php" method="POST">
        <p>
            <label>Nombre:</label><br>
            <input type="text" name="nombre" required>
        </p>

        <p>
            <label>Fecha:</label><br>
            <input type="date" name="fecha" required>
        </p>

        <p>
            <label>Hora:</label><br>
            <input type="time" name="hora" required>
        </p>

        <p>
            <label>Lugar:</label><br>
            <input type="text" name="lugar" required>
        </p>

        <p>
            <label>Descripción:</label><br>
            <textarea name="descripcion"></textarea>
        </p>

        <p>
            <label>Plantilla de asientos:</label><br>
            <select name="plantilla_id">
                <option value="">-- Sin plantilla por ahora --</option>
                <?php foreach ($plantillas as $plantilla): ?>
                    <option value="<?= $plantilla['id'] ?>">
                        <?= htmlspecialchars($plantilla['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>

        <button type="submit">Guardar evento</button>
    </form>

    <p><a href="eventos.php">Volver</a></p>
</body>
</html>