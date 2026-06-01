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

    public function obtenerTodo(
        $limite,
        $offset,
        $usuario_id = null
    ) {

        $sql = "SELECT
            m.id,
            u.nombre AS usuario,
            u.rol,
            m.modulo,
            m.accion,
            m.descripcion,
            m.fecha
        FROM movimientos m
        JOIN usuarios u
            ON m.usuario_id = u.id";

        if ($usuario_id !== null) {
            $sql .= "
                WHERE m.usuario_id = :usuario_id";
        }

        $sql .= "
            ORDER BY m.fecha DESC
            LIMIT :limite
            OFFSET :offset";

        $stmt = $this->conn->prepare($sql);

        if ($usuario_id !== null) {

            $stmt->bindParam(
                ':usuario_id',
                $usuario_id,
                PDO::PARAM_INT
            );
        }

        $stmt->bindParam(
            ':limite',
            $limite,
            PDO::PARAM_INT
        );

        $stmt->bindParam(
            ':offset',
            $offset,
            PDO::PARAM_INT
        );

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

    public function contarTodos($usuario_id = null) {

        $sql = "SELECT COUNT(*) as total
                FROM movimientos";

        if ($usuario_id !== null) {
            $sql .= "
                WHERE usuario_id = :usuario_id";
        }
        
        $stmt = $this->conn->prepare($sql);

        if ($usuario_id !== null) {

            $stmt->bindParam(
                ':usuario_id',
                $usuario_id,
                PDO::PARAM_INT
            );
        }

        $stmt->execute();

        $resultado =
            $stmt->fetch(PDO::FETCH_ASSOC);

        return $resultado['total'];
    }
}