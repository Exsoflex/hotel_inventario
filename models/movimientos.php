<?php

require_once __DIR__ . "/../config/database.php";

class Movimientos {

    private $conn;

    public function __construct() {

        $database = new Database();
        $this->conn = $database->conectar();
    }

    // ===========================
    // REGISTRAR UN MOVIMIENTO
    // ===========================

    public function registrar($modulo, $accion, $descripcion, $registro_id = null) {

        $usuario_id = $_SESSION['usuario']['id'] ?? null;

        if (!$usuario_id) return;

        $sql = "INSERT INTO movimientos 
                    (usuario_id, modulo, accion, registro_id, descripcion) 
                VALUES 
                    (:usuario_id, :modulo, :accion, :registro_id, :descripcion)";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindParam(":usuario_id",  $usuario_id);
        $stmt->bindParam(":modulo",      $modulo);
        $stmt->bindParam(":accion",      $accion);
        $stmt->bindParam(":registro_id", $registro_id);
        $stmt->bindParam(":descripcion", $descripcion);

        $stmt->execute();
    }

    // ===========================
    // OBTENER TODOS
    // ===========================

    public function obtenerTodo($limite = 200) {

        $sql = "SELECT 
                    m.id,
                    u.nombre AS usuario,
                    u.rol,
                    m.modulo,
                    m.accion,
                    m.descripcion,
                    m.fecha
                FROM movimientos m
                JOIN usuarios u ON m.usuario_id = u.id
                ORDER BY m.fecha DESC
                LIMIT :limite";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":limite", $limite, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ===========================
    // OBTENER POR MÓDULO
    // ===========================

    public function obtenerPorModulo($modulo) {

        $sql = "SELECT 
                    m.id,
                    u.nombre AS usuario,
                    u.rol,
                    m.modulo,
                    m.accion,
                    m.descripcion,
                    m.fecha
                FROM movimientos m
                JOIN usuarios u ON m.usuario_id = u.id
                WHERE m.modulo = :modulo
                ORDER BY m.fecha DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":modulo", $modulo);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}