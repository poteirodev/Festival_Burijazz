<?php
header("Content-Type: application/json; charset=utf-8");

$url = "https://appcobranzacert.redenlace.com.bo/cobranza-0.0.1/atc/generarQr";
$token = "rZvkNMqDpvN4p1fi5vcFN0vVbuBN25kDp0j6sQrTKU141MKSksuQTlEFFkqKRtd3nTZYiXPIO3Zq5hLM/YT7Ln4eEwuoxaKU4y997GFxbTk=";

$payload = [
    "numeroReferencia" => rand(100000, 999999),
    "glosa" => "298414|BURI JAZZ|EVENTOS|PRUEBA QR",
    "monto" => 10.00,
    "moneda" => "BOB",
    "canal" => "WEB",
    "tiempoQr" => "00:10:00",
    "campoExtra" => ""
];

$ch = curl_init($url);

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        "x-api-key: " . $token
    ],
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

curl_close($ch);

echo json_encode([
    "httpCode" => $httpCode,
    "error" => $error,
    "payload" => $payload,
    "response" => json_decode($response, true),
    "raw" => $response
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);