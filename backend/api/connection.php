<?php

class Conexion {
    private static $instancia = null;
    private $pdo;

    private function __construct() {
        try {
            $this->pdo = new PDO("mysql:host=localhost;dbname=computadoras", "dev", "Dev1001!");
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }

    public static function getConexion() {
        if (self::$instancia === null) {
            self::$instancia = new Conexion();
        }
        return self::$instancia->pdo;
    }
}
