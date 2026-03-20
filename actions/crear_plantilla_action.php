<?php
require_once "../config/db.php";

$nombre = $_POST['nombre'] ?? '';
$descripcion = $_POST['descripcion'] ?? '';
$imagen_mapa = $_POST['imagen_mapa'] ?? '';

if (!$nombre) {
    die("El nombre es obligatorio.");
}

$sql = "INSERT INTO plantillas_asientos (nombre, descripcion, imagen_mapa)
        VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->execute([$nombre, $descripcion, $imagen_mapa]);

header("Location: ../admin/plantillas.php");
exit;