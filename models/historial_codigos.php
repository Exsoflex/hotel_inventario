<?php

require_once __DIR__ . "/../config/database.php";

class HistorialCodigos {

    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->conectar();
    }

    // Buscar artículo de inventario por código de barras
    public function buscarPorCodigo($codigo) {

        $sql = "SELECT 
                    i.id,
                    i.codigo_barras,
                    i.cantidad,
                    i.estado,
                    i.comentarios,
                    a.nombre AS articulo,
                    h.numero AS habitacion
                FROM inventario i
                JOIN articulos a ON i.articulo_id = a.id
                JOIN habitaciones h ON i.habitacion_id = h.id
                WHERE i.codigo_barras = :codigo
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':codigo', $codigo);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Registrar consulta en el historial
    public function registrar($usuario_id, $inventario_id) {

        $sql = "INSERT INTO historial_codigos 
                    (usuario_id, inventario_id) 
                VALUES 
                    (:usuario_id, :inventario_id)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->bindParam(':inventario_id', $inventario_id);

        try {
            $stmt->execute();
            return ['exito' => true, 'id' => $this->conn->lastInsertId()];
        } catch (PDOException $e) {
            return ['exito' => false, 'error' => 'general'];
        }
    }

    // Obtener todo el historial con joins
public function obtenerTodo($usuario_id = null) {

    $sql = "SELECT 
                hc.id,
                hc.fecha_hora,
                u.nombre AS usuario,
                i.codigo_barras,
                a.nombre AS articulo,
                h.numero AS habitacion,
                i.estado,
                i.id AS inventario_id
            FROM historial_codigos hc
            JOIN usuarios u ON hc.usuario_id = u.id
            JOIN inventario i ON hc.inventario_id = i.id
            JOIN articulos a ON i.articulo_id = a.id
            JOIN habitaciones h ON i.habitacion_id = h.id";

    // Si se pasa un usuario_id, filtrar solo sus registros
    if ($usuario_id !== null) {
        $sql .= " WHERE hc.usuario_id = :usuario_id";
    }

    $sql .= " ORDER BY hc.fecha_hora DESC LIMIT 50";

    $stmt = $this->conn->prepare($sql);

    if ($usuario_id !== null) {
        $stmt->bindParam(':usuario_id', $usuario_id);
    }

    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    // Eliminar registro del historial
    public function eliminar($id) {

        $sql = "DELETE FROM historial_codigos WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
    }
}