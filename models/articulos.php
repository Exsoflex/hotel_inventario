<?php

require_once __DIR__ . "/../config/database.php";
class Articulos {
    private $conn;

    public function __construct() {

    $database = new Database();
    $this->conn = $database->conectar();
    }

    public function obtenerTodo() {

        $sql = "SELECT * FROM articulos
        ORDER BY nombre ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

    public function agregarArticulo($nombre, $descripcion, $usa_codigo_barras) {

        $sql = "INSERT INTO articulos (nombre, descripcion, usa_codigo_barras) VALUES (:nombre, :descripcion, :usa_codigo_barras)";
        $stmt = $this->conn->prepare($sql);

        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":descripcion", $descripcion);
        $stmt->bindParam(":usa_codigo_barras", $usa_codigo_barras, PDO::PARAM_INT);

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

    public function editarArticulo($id, $nombre, $descripcion, $usa_codigo_barras) {

        $sql = "UPDATE articulos SET nombre = :nombre, descripcion = :descripcion, usa_codigo_barras = :usa_codigo_barras WHERE id = :id";
        $stmt = $this->conn->prepare($sql);

        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":descripcion", $descripcion);
        $stmt->bindParam(":usa_codigo_barras", $usa_codigo_barras, PDO::PARAM_INT);

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