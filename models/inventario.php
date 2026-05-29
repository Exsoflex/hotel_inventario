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
            articulos.usa_codigo_barras,
            inventario.cantidad,
            inventario.estado,
            inventario.comentarios,
            inventario.codigo_barras
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

    public function obtenerArticuloPorId($id) {

        $sql = "SELECT * FROM articulos WHERE id = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function agregarInventario(
        $habitacion_id, 
        $articulo_id,
        $cantidad,
        $estado, 
        $comentarios,
        $codigo
        ) {

        if($codigo === ''){
            $codigo = null;
        }

        $sql = "INSERT INTO inventario 
                    (habitacion_id, articulo_id, cantidad, estado, comentarios, codigo_barras) 
                VALUES 
                    (:habitacion_id, :articulo_id, :cantidad, :estado, :comentarios, :codigo_barras)";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindParam(":habitacion_id", $habitacion_id);
        $stmt->bindParam(":articulo_id", $articulo_id);
        $stmt->bindParam(":cantidad", $cantidad);
        $stmt->bindParam(":estado", $estado);
        $stmt->bindParam(":comentarios", $comentarios);
        $stmt->bindParam(":codigo_barras", $codigo);

        try {

            $stmt->execute();

            return [
                'exito' => true,
                'id' => $this->conn->lastInsertId()
            ];

        } catch(PDOException $e){

            if($e->getCode() == 23000){

                return [
                    'exito' => false,
                    'error' => 'duplicado'
                ];
            }

            return [
                'exito' => false,
                'error' => 'general'
            ];
        }
        }

    public function eliminarInventario($id) {

        $sql = "DELETE FROM inventario WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":id", $id);

        $stmt->execute();
    }

    public function obtenerPorId($id) {

        $sql = "SELECT
                    inventario.*,
                    articulos.usa_codigo_barras
                FROM inventario
                JOIN articulos
                    ON inventario.articulo_id = articulos.id
                WHERE inventario.id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);

    }

    public function editarInventario($id, $habitacion_id, $articulo_id, $cantidad, $estado, $comentarios, $codigo) {

        if($codigo === ''){
            $codigo = null;
        }
        $sql = "UPDATE inventario
                SET habitacion_id = :habitacion_id, 
                articulo_id = :articulo_id, 
                cantidad = :cantidad, 
                estado = :estado, 
                comentarios = :comentarios,
                codigo_barras = :codigo_barras
                WHERE id = :id";
        
        $stmt = $this->conn->prepare($sql);

        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":habitacion_id", $habitacion_id);
        $stmt->bindParam(":articulo_id", $articulo_id);
        $stmt->bindParam(":cantidad", $cantidad);
        $stmt->bindParam(":estado", $estado);
        $stmt->bindParam(":comentarios", $comentarios);
        $stmt->bindParam(":codigo_barras", $codigo);

        try {

            $stmt->execute();

            return [
                'exito' => true
            ];

        } catch(PDOException $e){

            if($e->getCode() == 23000){

                return [
                    'exito' => false,
                    'error' => 'duplicado'
                ];
            }

            return [
                'exito' => false,
                'error' => 'general'
            ];
        }
    }

    public function obtenerNumeroHabitacion($id) {

        $sql = "SELECT numero FROM habitaciones WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado ? $resultado['numero'] : "habitación #$id";
    }

    public function obtenerNombreArticulo($id) {

        $sql = "SELECT nombre FROM articulos WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado ? $resultado['nombre'] : "artículo #$id";
    }

}