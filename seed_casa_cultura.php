<?php
require_once "config/db.php";

$nombrePlantilla = "Casa de la Cultura";
$descripcion = "Plantilla base con escenario arriba";
$imagenMapa = "casa-cultura.png";

/* Verificar si ya existe */
$check = $conn->prepare("SELECT id FROM plantillas_asientos WHERE nombre = ?");
$check->execute([$nombrePlantilla]);
$plantillaExistente = $check->fetch(PDO::FETCH_ASSOC);

if ($plantillaExistente) {
    die("La plantilla ya existe con ID: " . $plantillaExistente['id']);
}

/* Crear plantilla */
$stmt = $conn->prepare("
    INSERT INTO plantillas_asientos (nombre, descripcion, imagen_mapa)
    VALUES (?, ?, ?)
");
$stmt->execute([$nombrePlantilla, $descripcion, $imagenMapa]);

$plantillaId = $conn->lastInsertId();

/* Filas */
$filas = range('A', 'P');

/* Números izquierda y derecha */
$izquierda = [16, 14, 12, 10, 8, 6, 4, 2];
$derecha = [1, 3, 5, 7, 9, 11, 13, 15];

/* Insertar asientos */
$sqlDetalle = $conn->prepare("
    INSERT INTO plantilla_asientos_detalle (plantilla_id, fila, numero, codigo)
    VALUES (?, ?, ?, ?)
");

foreach ($filas as $fila) {
    foreach ($izquierda as $numero) {
        $codigo = $numero . $fila;
        $sqlDetalle->execute([$plantillaId, $fila, $numero, $codigo]);
    }

    foreach ($derecha as $numero) {
        $codigo = $numero . $fila;
        $sqlDetalle->execute([$plantillaId, $fila, $numero, $codigo]);
    }
}

echo "Plantilla creada correctamente. ID: " . $plantillaId;
?>