<?php

require_once __DIR__ . "/../config/database.php";
class Habitacion {
    private $conn;

    public function __construct() {

    $database = new Database();
    $this->conn = $database->conectar();
    }

    public function obtenerTodo() {

        $sql = "SELECT * FROM habitaciones";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

    public function agregarHabitacion($piso,$numero, $tipo, $descripcion) {

        $sql = "INSERT INTO habitaciones (piso, numero, tipo, descripcion) VALUES (:piso,:numero, :tipo, :descripcion)";
        $stmt = $this->conn->prepare($sql);

        $stmt->bindParam(":piso", $piso);
        $stmt->bindParam(":numero", $numero);
        $stmt->bindParam(":tipo", $tipo);
        $stmt->bindParam(":descripcion", $descripcion);

        $stmt->execute();

        return $this->conn->lastInsertId();
    }

    public function eliminarHabitacion($id) {

        $sql = "DELETE FROM habitaciones WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":id", $id);

        $stmt->execute();
    }

    public function obtenerPorId($id) {

        $sql = "SELECT * FROM habitaciones WHERE id = :id";
        $stmt = $this->conn->prepare ($sql);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        return $stmt->fetch (PDO::FETCH_ASSOC);
    }

    public function editarHabitacion($id, $piso, $numero, $tipo, $descripcion) {

        $sql = "UPDATE habitaciones SET piso = :piso, numero = :numero, tipo = :tipo, descripcion = :descripcion WHERE id = :id";
        $stmt = $this->conn->prepare($sql);

        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":piso", $piso);
        $stmt->bindParam(":numero", $numero);
        $stmt->bindParam(":tipo", $tipo);
        $stmt->bindParam(":descripcion", $descripcion);  
        
        $stmt->execute();

    }


}