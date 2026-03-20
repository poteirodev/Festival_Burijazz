<?php
require_once "../config/db.php";

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf-8");

/* Liberar reservas vencidas */
$conn->exec("
    UPDATE asientos_evento
    SET estado = 'disponible',
        reservado_hasta = NULL
    WHERE estado = 'reservado'
      AND reservado_hasta IS NOT NULL
      AND reservado_hasta < NOW()
");

$slug = $_GET['slug'] ?? null;

if (!$slug) {
    echo json_encode([
        "evento" => null,
        "asientos" => []
    ]);
    exit;
}

$stmtEvento = $conn->prepare("
    SELECT id, slug, nombre, fecha, hora, lugar, descripcion
    FROM eventos
    WHERE slug = ?
");
$stmtEvento->execute([$slug]);
$evento = $stmtEvento->fetch(PDO::FETCH_ASSOC);

if (!$evento) {
    echo json_encode([
        "evento" => null,
        "asientos" => []
    ]);
    exit;
}

$stmtAsientos = $conn->prepare("
    SELECT id, fila, numero, codigo, estado, reservado_hasta
    FROM asientos_evento
    WHERE evento_id = ?
");
$stmtAsientos->execute([$evento['id']]);
$asientos = $stmtAsientos->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    "evento" => $evento,
    "asientos" => $asientos
]);