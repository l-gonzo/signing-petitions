<?php
require_once "connection.php";

function get_all_computers() {
    $pdo = Conexion::getConexion();
    $stmt = $pdo->query("SELECT * FROM computadoras");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_computer_by_id($id) {
    $pdo = Conexion::getConexion();
    $stmt = $pdo->prepare("SELECT * FROM computadoras WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function add_computer($data) {
    $pdo = Conexion::getConexion();
    $sql = "INSERT INTO computadoras (marca, cpu, gpu, ram, disco) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $data['marca'],
        $data['cpu'],
        $data['gpu'],
        $data['ram'],
        $data['disco']
    ]);
    return ["status" => "ok", "id" => $pdo->lastInsertId()];
}

function update_computer($id, $data) {
    $pdo = Conexion::getConexion();
    $sql = "UPDATE computadoras SET marca = ?, cpu = ?, gpu = ?, ram = ?, disco = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $data['marca'],
        $data['cpu'],
        $data['gpu'],
        $data['ram'],
        $data['disco'],
        $id
    ]);
    return ["status" => "ok"];
}

function delete_computer($id) {
    $pdo = Conexion::getConexion();
    $stmt = $pdo->prepare("DELETE FROM computadoras WHERE id = ?");
    $stmt->execute([$id]);
    return ["status" => "ok"];
}
