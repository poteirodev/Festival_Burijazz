<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: /Festivalburijazz-astro/admin/login.php');
    exit;
}

$id = (int)($_POST['id'] ?? 0);

if ($id <= 0) {
    exit('ID de evento no válido.');
}

$stmt = $conn->prepare("DELETE FROM asientos_evento WHERE evento_id = ?");
$stmt->execute([$id]);

$stmt = $conn->prepare("DELETE FROM eventos WHERE id = ?");
$stmt->execute([$id]);

header('Location: /Festivalburijazz-astro/admin/eventos.php');
exit;