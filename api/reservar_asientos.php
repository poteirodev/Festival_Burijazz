<?php
require_once "../config/db.php";

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=utf-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

/* Liberar reservas vencidas */
$conn->exec("
    UPDATE asientos_evento
    SET estado = 'disponible',
        reservado_hasta = NULL
    WHERE estado = 'reservado'
      AND reservado_hasta IS NOT NULL
      AND reservado_hasta < NOW()
");

$input = json_decode(file_get_contents("php://input"), true);

$evento_slug = $input['evento_slug'] ?? null;
$seat_ids = $input['seat_ids'] ?? [];

if (!$evento_slug || !is_array($seat_ids) || count($seat_ids) < 1) {
    echo json_encode([
        "success" => false,
        "message" => "Datos inválidos."
    ]);
    exit;
}

if (count($seat_ids) > 6) {
    echo json_encode([
        "success" => false,
        "message" => "Solo puedes reservar hasta 6 asientos."
    ]);
    exit;
}

try {
    $conn->beginTransaction();

    $stmtEvento = $conn->prepare("SELECT id FROM eventos WHERE slug = ?");
    $stmtEvento->execute([$evento_slug]);
    $evento = $stmtEvento->fetch(PDO::FETCH_ASSOC);

    if (!$evento) {
        $conn->rollBack();
        echo json_encode([
            "success" => false,
            "message" => "Evento no encontrado."
        ]);
        exit;
    }

    $evento_id = (int)$evento['id'];

    $placeholders = implode(',', array_fill(0, count($seat_ids), '?'));

    $params = $seat_ids;
    array_unshift($params, $evento_id);

    $stmtSeats = $conn->prepare("
        SELECT id, estado, reservado_hasta
        FROM asientos_evento
        WHERE evento_id = ?
          AND id IN ($placeholders)
        FOR UPDATE
    ");
    $stmtSeats->execute($params);
    $rows = $stmtSeats->fetchAll(PDO::FETCH_ASSOC);

    if (count($rows) !== count($seat_ids)) {
        $conn->rollBack();
        echo json_encode([
            "success" => false,
            "message" => "Uno o más asientos no existen."
        ]);
        exit;
    }

    foreach ($rows as $row) {
        if ($row['estado'] !== 'disponible') {
            $conn->rollBack();
            echo json_encode([
                "success" => false,
                "message" => "Uno o más asientos ya no están disponibles."
            ]);
            exit;
        }
    }

    $stmtReserve = $conn->prepare("
        UPDATE asientos_evento
        SET estado = 'reservado',
            reservado_hasta = DATE_ADD(NOW(), INTERVAL 10 MINUTE)
        WHERE id = ?
    ");

    foreach ($seat_ids as $seat_id) {
        $stmtReserve->execute([$seat_id]);
    }

    $conn->commit();

    echo json_encode([
        "success" => true,
        "message" => "Asientos reservados por 10 minutos.",
        "expires_in_minutes" => 10
    ]);
} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    echo json_encode([
        "success" => false,
        "message" => "Error interno al reservar."
    ]);
}