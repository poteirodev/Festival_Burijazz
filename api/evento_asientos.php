<?php
require_once "../config/db.php";

header('Content-Type: application/json; charset=utf-8');

$slug = trim($_GET['slug'] ?? '');
$evento_id = isset($_GET['evento_id']) ? (int)$_GET['evento_id'] : 0;

try {
    if ($slug !== '') {
        $stmtEvento = $conn->prepare("SELECT id FROM eventos WHERE slug = ? LIMIT 1");
        $stmtEvento->execute([$slug]);
        $evento = $stmtEvento->fetch(PDO::FETCH_ASSOC);

        if (!$evento) {
            http_response_code(404);
            echo json_encode([]);
            exit;
        }

        $evento_id = (int)$evento['id'];
    }

    if ($evento_id <= 0) {
        http_response_code(400);
        echo json_encode([]);
        exit;
    }

    $stmt = $conn->prepare("
        SELECT
            id,
            evento_id,
            plantilla_asiento_id,
            fila,
            numero,
            codigo,
            estado,
            reservado_hasta,
            comprador_id,
            precio,
            created_at,
            updated_at
        FROM asientos_evento
        WHERE evento_id = ?
        ORDER BY fila ASC, numero ASC
    ");
    $stmt->execute([$evento_id]);
    $asientos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($asientos, JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}