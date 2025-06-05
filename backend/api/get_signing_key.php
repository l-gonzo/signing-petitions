<?php

header("Access-Control-Allow-Origin: http://192.168.1.199:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: X-Requested-With, X-Client-Signature, X-Client-Timestamp, Content-Type");

session_start();

// Duración de la clave en segundos (por seguridad)
define('KEY_EXPIRATION_TIME', 300); // 5 minutos

// Si no existe la clave o ya expiró, genera una nueva
if (!isset($_SESSION['signing_key']) || !isset($_SESSION['key_time']) || (time() - $_SESSION['key_time']) > KEY_EXPIRATION_TIME) {
    $_SESSION['signing_key'] = bin2hex(random_bytes(16));
    $_SESSION['key_time'] = time();
}

// Devuelve la clave al frontend
echo json_encode([
    "signing_key" => $_SESSION['signing_key']
]);
