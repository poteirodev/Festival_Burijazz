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
    $nombre = trim($data['nombre'] ?? '');
    $email = trim($data['email'] ?? '');
    $telefono = trim($data['telefono'] ?? '');

    if ($slug === '' || !is_array($asientos) || count($asientos) === 0 || $nombre === '' || $email === '' || $telefono === '') {
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
            'message' => 'Solo puedes comprar entre 1 y 6 asientos.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $conn->beginTransaction();

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

    $stmtComprador = $conn->prepare("
        INSERT INTO compradores (nombre, email, telefono)
        VALUES (?, ?, ?)
    ");
    $stmtComprador->execute([$nombre, $email, $telefono]);
    $comprador_id = (int)$conn->lastInsertId();

    $placeholders = implode(',', array_fill(0, count($asientos), '?'));

    $sqlValidar = "
        SELECT id
        FROM asientos_evento
        WHERE evento_id = ?
          AND id IN ($placeholders)
          AND (estado = 'disponible' OR estado = 'reservado')
    ";

    $stmtValidar = $conn->prepare($sqlValidar);
    $stmtValidar->execute(array_merge([$evento_id], $asientos));
    $validos = $stmtValidar->fetchAll(PDO::FETCH_COLUMN);

    if (count($validos) !== count($asientos)) {
        $conn->rollBack();
        http_response_code(409);
        echo json_encode([
            'ok' => false,
            'message' => 'Uno o más asientos ya no están disponibles.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $sqlComprar = "
        UPDATE asientos_evento
        SET estado = 'ocupado',
            comprador_id = ?,
            reservado_hasta = NULL
        WHERE evento_id = ?
          AND id IN ($placeholders)
          AND (estado = 'disponible' OR estado = 'reservado')
    ";

    $stmtComprar = $conn->prepare($sqlComprar);
    $stmtComprar->execute(array_merge([$comprador_id, $evento_id], $asientos));

    $conn->commit();

    echo json_encode([
        'ok' => true,
        'message' => 'Compra realizada correctamente.'
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'message' => 'Error al comprar: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}