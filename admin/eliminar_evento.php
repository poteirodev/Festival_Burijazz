<?php
session_start();
require_once "../config/db.php";

if (!isset($_SESSION['admin_id'])) {
    header('Location: /Festivalburijazz-astro/admin/login.php');
    exit;
}

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    exit('ID de evento no válido.');
}

try {
    $conn->beginTransaction();

    // Primero borrar los asientos relacionados con el evento
    $stmt = $conn->prepare("DELETE FROM asientos_evento WHERE evento_id = ?");
    $stmt->execute([$id]);

    // Luego borrar el evento
    $stmt = $conn->prepare("DELETE FROM eventos WHERE id = ?");
    $stmt->execute([$id]);

    $conn->commit();

    header('Location: /Festivalburijazz-astro/admin/eventos.php');
    exit;
} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    exit('Error al eliminar el evento: ' . $e->getMessage());
}