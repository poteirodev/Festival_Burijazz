<?php
require_once "../config/db.php";

header('Content-Type: application/json; charset=utf-8');

$sql = "SELECT id, slug, nombre, fecha, hora, lugar, descripcion
        FROM eventos
        ORDER BY fecha ASC, hora ASC";
$stmt = $conn->query($sql);
$eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($eventos);