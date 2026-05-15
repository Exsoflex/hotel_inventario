<?php

require_once __DIR__ . "/../config/database.php";
class Articulos {
    private $conn;

    public function __construct() {

    $database = new Database();
    $this->conn = $database->conectar();
    }

    public function obtenerTodo() {

        $sql = "SELECT * FROM articulos";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

    public function agregarArticulo($nombre, $descripcion) {

        $sql = "INSERT INTO articulos (nombre, descripcion) VALUES (:nombre, :descripcion)";
        $stmt = $this->conn->prepare($sql);

        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":descripcion", $descripcion);

        $stmt->execute();

        return $this->conn->lastInsertId(); // Devuelve el ID del artículo recién agregado
    }

    public function eliminarArticulo($id) {

        $sql = "DELETE FROM articulos WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":id", $id);

        $stmt->execute();
    }


      public function obtenerPorId($id) {

        $sql = "SELECT * FROM articulos WHERE id = :id";
        $stmt = $this->conn->prepare ($sql);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        return $stmt->fetch (PDO::FETCH_ASSOC);
    }

    public function editarArticulo($id, $nombre, $descripcion) {

        $sql = "UPDATE articulos SET nombre = :nombre, descripcion = :descripcion WHERE id = :id";
        $stmt = $this->conn->prepare($sql);

        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":descripcion", $descripcion);  
        
        $stmt->execute();

    }


}