<?php
require_once __DIR__ . "/../config/database.php";

class Inventario {

    private $conn;

    public function __construct(){

        $database = new Database();
        $this->conn = $database->conectar();

    }

    public function obtenerTodo() {

        $sql = "SELECT 
            inventario.id,
            habitaciones.numero,
            articulos.nombre,
            inventario.cantidad,
            inventario.estado,
            inventario.comentarios
        FROM inventario
        
        JOIN habitaciones 
            ON inventario.habitacion_id = habitaciones.id
        JOIN articulos
            ON inventario.articulo_id = articulos.id";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerHabitaciones() {

        $sql = "SELECT * FROM habitaciones";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerArticulos() {

        $sql = "SELECT * FROM articulos";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function agregarInventario(
        $habitacion_id, 
        $articulo_id,
        $cantidad,
        $estado, 
        $comentarios
        ) {

        $sql = "INSERT INTO inventario 
                    (habitacion_id, articulo_id, cantidad, estado, comentarios) 
                VALUES 
                    (:habitacion_id, :articulo_id, :cantidad, :estado, :comentarios)";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindParam(":habitacion_id", $habitacion_id);
        $stmt->bindParam(":articulo_id", $articulo_id);
        $stmt->bindParam(":cantidad", $cantidad);
        $stmt->bindParam(":estado", $estado);
        $stmt->bindParam(":comentarios", $comentarios);

        $stmt->execute();

        return $this->conn->lastInsertId();

        }

    public function eliminarInventario($id) {


        $sql = "DELETE FROM inventario WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":id", $id);

        $stmt->execute();
    }

    public function obtenerPorId($id) {

        $sql = "SELECT * FROM inventario WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);

    }

    public function editarInventario($id, $habitacion_id, $articulo_id, $cantidad, $estado, $comentarios) {

        $sql = "UPDATE inventario
                SET habitacion_id = :habitacion_id, 
                articulo_id = :articulo_id, 
                cantidad = :cantidad, 
                estado = :estado, 
                comentarios = :comentarios
                WHERE id = :id";
        
        $stmt = $this->conn->prepare($sql);

        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":habitacion_id", $habitacion_id);
        $stmt->bindParam(":articulo_id", $articulo_id);
        $stmt->bindParam(":cantidad", $cantidad);
        $stmt->bindParam(":estado", $estado);
        $stmt->bindParam(":comentarios", $comentarios);

        $stmt->execute();
    }

}