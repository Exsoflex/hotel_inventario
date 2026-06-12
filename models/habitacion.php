<?php

require_once __DIR__ . "/../config/database.php";
class Habitacion {
    private $conn;

    public function __construct() {

    $database = new Database();
    $this->conn = $database->conectar();
    }

    public function obtenerTodo() {

        $sql = "SELECT * FROM habitaciones
                ORDER BY piso, numero ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

    public function agregarHabitacion($piso,$numero, $tipo, $descripcion, $estado) {

        $sql = "INSERT INTO habitaciones (piso, numero, tipo, descripcion, estado) VALUES (:piso,:numero, :tipo, :descripcion, :estado)";
        $stmt = $this->conn->prepare($sql);

        $stmt->bindParam(":piso", $piso);
        $stmt->bindParam(":numero", $numero);
        $stmt->bindParam(":tipo", $tipo);
        $stmt->bindParam(":descripcion", $descripcion);
        $stmt->bindParam(":estado", $estado);

        try {
            $stmt->execute();
            return ['exito' => true, 'id' => $this->conn->lastInsertId()];
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Violación de unique
                return ['exito' => false, 'error' => 'duplicado'];
            }
            return ['exito' => false, 'error' => 'general'];
        }
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

    public function editarHabitacion($id, $piso, $numero, $tipo, $descripcion, $estado) {

        $sql = "UPDATE habitaciones SET piso = :piso, numero = :numero, tipo = :tipo, descripcion = :descripcion, estado = :estado WHERE id = :id";
        $stmt = $this->conn->prepare($sql);

        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":piso", $piso);
        $stmt->bindParam(":numero", $numero);
        $stmt->bindParam(":tipo", $tipo);
        $stmt->bindParam(":descripcion", $descripcion);  
        $stmt->bindParam(":estado", $estado);

        try {
            $stmt->execute();
            return ['exito' => true];
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                return ['exito' => false, 'error' => 'duplicado'];
            }
            return ['exito' => false, 'error' => 'general'];
        }

    }


}