<?php
require_once __DIR__ . "/../config/database.php";

class Inventario_base {

    private $conn;

    public function __construct(){

        $database = new Database();
        $this->conn = $database->conectar();

    }

    public function obtenerTodo() {

        $sql = "SELECT 
            inventario_base.id,
            inventario_base.tipo_habitacion,
            articulos.nombre,
            inventario_base.cantidad
        FROM inventario_base
        
        JOIN articulos
            ON inventario_base.articulo_id = articulos.id";

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

    public function agregarInventario_base(
        $tipo, 
        $articulo_id,
        $cantidad
        ) {

        $sql = "INSERT INTO inventario_base 
                    (tipo_habitacion, articulo_id, cantidad) 
                VALUES 
                    (:tipo_habitacion, :articulo_id, :cantidad)";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindParam(":tipo_habitacion", $tipo);
        $stmt->bindParam(":articulo_id", $articulo_id);
        $stmt->bindParam(":cantidad", $cantidad);

        $stmt->execute();

        return $this->conn->lastInsertId();

        }

    public function eliminarInventario_base($id) {


        $sql = "DELETE FROM inventario_base WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":id", $id);

        $stmt->execute();
    }

    public function obtenerPorId($id) {

        $sql = "SELECT * FROM inventario_base WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);

    }

    public function editarInventario_base($id, $tipo, $articulo_id, $cantidad) {

        $sql = "UPDATE inventario_base
                SET tipo_habitacion = :tipo_habitacion, 
                articulo_id = :articulo_id, 
                cantidad = :cantidad
                WHERE id = :id";
        
        $stmt = $this->conn->prepare($sql);

        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":tipo_habitacion", $tipo);
        $stmt->bindParam(":articulo_id", $articulo_id);
        $stmt->bindParam(":cantidad", $cantidad);

        $stmt->execute();
    }

    public function obtenerNombreArticulo($articulo_id) {

    $sql = "SELECT nombre FROM articulos WHERE id = :id";
    $stmt = $this->conn->prepare($sql);
    $stmt->bindParam(":id", $articulo_id);
    $stmt->execute();

    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    return $resultado ? $resultado['nombre'] : "artículo #$articulo_id";
}

}