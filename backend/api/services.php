<?php

header("Access-Control-Allow-Origin: http://192.168.1.199:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type, X-Client-Timestamp, X-Client-Signature");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");

session_start();
require_once "connection.php";
require_once "computers.php";

// ------------------------------
// 🌐 Preflight para CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// ------------------------------
// 🔐 Validación de firma del cliente

$path = $_SERVER['REQUEST_URI'];
$timestampCliente = $_SERVER['HTTP_X_CLIENT_TIMESTAMP'] ?? 0;
$signature = $_SERVER['HTTP_X_CLIENT_SIGNATURE'] ?? '';
$body = file_get_contents("php://input");

// Validar timestamp (máximo 2 minutos de diferencia)
$timestampActual = round(microtime(true) * 1000);
if (abs($timestampActual - $timestampCliente) > 120000) {
    http_response_code(403);
    exit("Timestamp inválido o expirado");
}

// Validar existencia de clave
if (!isset($_SESSION['signing_key'])) {
    http_response_code(403);
    exit("No hay clave de firma en sesión");
}

// Validar firma del cliente
$expectedClientSignature = hash("sha256", "$path|$timestampCliente|$body|{$_SESSION['signing_key']}");
if ($signature !== $expectedClientSignature) {
    http_response_code(403);
    exit("Firma del cliente inválida");
}

// ------------------------------
// 🧠 Lógica del servicio
$serviceName = $_POST['serviceName'] ?? $_GET['serviceName'] ?? '';
$response = '';

switch ($serviceName) {
    case 'get_all_computers':
        $response = json_encode(get_all_computers());
        break;

    case 'get_computer_by_id':
        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $response = json_encode(get_computer_by_id($id));
        } else {
            $response = json_encode(["error" => "Parámetro 'id' requerido"]);
        }
        break;

    case 'add_computer':
        if (
            isset($_POST['marca']) && isset($_POST['cpu']) &&
            isset($_POST['gpu']) && isset($_POST['ram']) && isset($_POST['disco'])
        ) {
            $data = [
                "marca" => $_POST['marca'],
                "cpu" => $_POST['cpu'],
                "gpu" => $_POST['gpu'],
                "ram" => $_POST['ram'],
                "disco" => $_POST['disco']
            ];
            $response = json_encode(add_computer($data));
        } else {
            $response = json_encode(["error" => "Faltan parámetros en add_computer"]);
        }
        break;

    case 'update_computer':
        if (
            isset($_POST['id']) && isset($_POST['marca']) &&
            isset($_POST['cpu']) && isset($_POST['gpu']) &&
            isset($_POST['ram']) && isset($_POST['disco'])
        ) {
            $id = intval($_POST['id']);
            $data = [
                "marca" => $_POST['marca'],
                "cpu" => $_POST['cpu'],
                "gpu" => $_POST['gpu'],
                "ram" => $_POST['ram'],
                "disco" => $_POST['disco']
            ];
            $response = json_encode(update_computer($id, $data));
        } else {
            $response = json_encode(["error" => "Faltan parámetros en update_computer"]);
        }
        break;

    case 'delete_computer':
        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $response = json_encode(delete_computer($id));
        } else {
            $response = json_encode(["error" => "Parámetro 'id' requerido"]);
        }
        break;

    default:
        http_response_code(400);
        $response = json_encode(["error" => "Servicio no reconocido: '$serviceName'"]);
        break;
}

// ------------------------------
// ✍️ Firma del backend sobre la respuesta
$serverSignature = hash("sha256", "$path|$timestampCliente|$response|{$_SESSION['signing_key']}");
header("X-Server-Signature: $serverSignature");

// Enviar la respuesta
echo $response;
