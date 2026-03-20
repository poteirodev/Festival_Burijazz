<?php
require_once "../config/db.php";

function crearSlug($texto) {
    $texto = trim($texto);
    $texto = mb_strtolower($texto, 'UTF-8');

    $reemplazos = [
        'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
        'ä' => 'a', 'ë' => 'e', 'ï' => 'i', 'ö' => 'o', 'ü' => 'u',
        'ñ' => 'n'
    ];

    $texto = strtr($texto, $reemplazos);
    $texto = preg_replace('/[^a-z0-9]+/u', '-', $texto);
    $texto = trim($texto, '-');

    return $texto ?: 'evento';
}

function slugDisponible($conn, $slugBase) {
    $slug = $slugBase;
    $i = 2;

    while (true) {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM eventos WHERE slug = ?");
        $stmt->execute([$slug]);

        if ($stmt->fetchColumn() == 0) {
            return $slug;
        }

        $slug = $slugBase . '-' . $i;
        $i++;
    }
}

$nombre = $_POST['nombre'] ?? '';
$fecha = $_POST['fecha'] ?? '';
$hora = $_POST['hora'] ?? '';
$lugar = $_POST['lugar'] ?? '';
$descripcion = $_POST['descripcion'] ?? '';
$plantilla_id = $_POST['plantilla_id'] ?? null;

if ($plantilla_id === '') {
    $plantilla_id = null;
}

if (!$nombre || !$fecha || !$hora || !$lugar) {
    die("Faltan datos obligatorios.");
}

$slugBase = crearSlug($nombre);
$slug = slugDisponible($conn, $slugBase);

$sql = "INSERT INTO eventos (nombre, slug, fecha, hora, lugar, descripcion, plantilla_id)
        VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->execute([$nombre, $slug, $fecha, $hora, $lugar, $descripcion, $plantilla_id]);

$evento_id = $conn->lastInsertId();

if ($plantilla_id) {
    $stmtPlantilla = $conn->prepare("
        SELECT id, fila, numero, codigo
        FROM plantilla_asientos_detalle
        WHERE plantilla_id = ?
        ORDER BY fila ASC, numero ASC
    ");
    $stmtPlantilla->execute([$plantilla_id]);
    $asientosPlantilla = $stmtPlantilla->fetchAll(PDO::FETCH_ASSOC);

    $stmtInsertAsiento = $conn->prepare("
        INSERT INTO asientos_evento
        (evento_id, plantilla_asiento_id, fila, numero, codigo, estado, precio)
        VALUES (?, ?, ?, ?, ?, 'disponible', 0.00)
    ");

    foreach ($asientosPlantilla as $asiento) {
        $stmtInsertAsiento->execute([
            $evento_id,
            $asiento['id'],
            $asiento['fila'],
            $asiento['numero'],
            $asiento['codigo']
        ]);
    }
}

header("Location: ../admin/eventos.php");
exit;