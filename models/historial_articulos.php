<?php

require_once __DIR__ . "/../config/database.php";

class HistorialArticulos {

    private $conn;

    public function __construct() {

        $database = new Database();
        $this->conn = $database->conectar();

    }

    public function obtenerPorInventarioId($inventario_id) {

        $sql = "SELECT id, inventario_id, fecha, nota, created_at, updated_at
                FROM historial_articulos
                WHERE inventario_id = :inventario_id
                ORDER BY fecha DESC, created_at DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":inventario_id", $inventario_id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

    public function agregar($inventario_id, $fecha, $nota) {

        $sql = "INSERT INTO historial_articulos (inventario_id, fecha, nota)
                VALUES (:inventario_id, :fecha, :nota)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":inventario_id", $inventario_id, PDO::PARAM_INT);
        $stmt->bindParam(":fecha", $fecha);
        $stmt->bindParam(":nota", $nota);
        $stmt->execute();

        return $this->conn->lastInsertId();

    }

    public function editar($id, $fecha, $nota) {

        $sql = "UPDATE historial_articulos
                SET fecha = :fecha, nota = :nota
                WHERE id = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->bindParam(":fecha", $fecha);
        $stmt->bindParam(":nota", $nota);
        $stmt->execute();

        return $stmt->rowCount();

    }

    public function eliminar($id) {

        $sql = "DELETE FROM historial_articulos WHERE id = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount();

    }

    public function obtenerPorId($id) {

        $sql = "SELECT id, inventario_id, fecha, nota, created_at, updated_at
                FROM historial_articulos
                WHERE id = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);

    }

}