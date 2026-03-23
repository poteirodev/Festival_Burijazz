<?php
require_once "../config/db.php";

header("Access-Control-Allow-Origin: http://localhost:4321");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=utf-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'ok' => false,
        'message' => 'Método no permitido.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $raw = file_get_contents("php://input");
    $data = json_decode($raw, true);

    if (!is_array($data)) {
        http_response_code(400);
        echo json_encode([
            'ok' => false,
            'message' => 'Datos inválidos.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $slug = trim($data['slug'] ?? '');
    $asientos = $data['asientos'] ?? [];

    if ($slug === '' || !is_array($asientos) || count($asientos) === 0) {
        http_response_code(400);
        echo json_encode([
            'ok' => false,
            'message' => 'Datos inválidos.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $asientos = array_values(array_unique(array_map('intval', $asientos)));
    $asientos = array_filter($asientos, fn($id) => $id > 0);

    if (count($asientos) === 0 || count($asientos) > 6) {
        http_response_code(400);
        echo json_encode([
            'ok' => false,
            'message' => 'Solo puedes reservar entre 1 y 6 asientos.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $conn->beginTransaction();

    $conn->exec("
        UPDATE asientos_evento
        SET estado = 'disponible', reservado_hasta = NULL
        WHERE estado = 'reservado'
          AND reservado_hasta IS NOT NULL
          AND reservado_hasta < NOW()
    ");

    $stmtEvento = $conn->prepare("SELECT id FROM eventos WHERE slug = ? LIMIT 1");
    $stmtEvento->execute([$slug]);
    $evento = $stmtEvento->fetch(PDO::FETCH_ASSOC);

    if (!$evento) {
        $conn->rollBack();
        http_response_code(404);
        echo json_encode([
            'ok' => false,
            'message' => 'Evento no encontrado.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $evento_id = (int)$evento['id'];

    $placeholders = implode(',', array_fill(0, count($asientos), '?'));

    $sqlValidar = "
        SELECT id
        FROM asientos_evento
        WHERE evento_id = ?
          AND id IN ($placeholders)
          AND estado = 'disponible'
    ";

    $stmtValidar = $conn->prepare($sqlValidar);
    $stmtValidar->execute(array_merge([$evento_id], $asientos));
    $disponibles = $stmtValidar->fetchAll(PDO::FETCH_COLUMN);

    if (count($disponibles) !== count($asientos)) {
        $conn->rollBack();
        http_response_code(409);
        echo json_encode([
            'ok' => false,
            'message' => 'Uno o más asientos ya no están disponibles.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $sqlReservar = "
        UPDATE asientos_evento
        SET estado = 'reservado',
            reservado_hasta = DATE_ADD(NOW(), INTERVAL 10 MINUTE)
        WHERE evento_id = ?
          AND id IN ($placeholders)
          AND estado = 'disponible'
    ";

    $stmtReservar = $conn->prepare($sqlReservar);
    $stmtReservar->execute(array_merge([$evento_id], $asientos));

    $conn->commit();

    echo json_encode([
        'ok' => true,
        'message' => 'Asientos reservados correctamente.',
        'reservado_hasta' => date('Y-m-d H:i:s', strtotime('+10 minutes'))
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'message' => 'Error al reservar: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}