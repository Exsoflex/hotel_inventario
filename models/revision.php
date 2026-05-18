<?php

require_once __DIR__ . "/../config/database.php";

class Revision {

    private $conn;

    public function __construct() {

        $database = new Database();
        $this->conn = $database->conectar();

    }

    public function obtenerFaltantes() {

        $sql = "SELECT *
        FROM faltantes_por_habitacion";

        $stmt = $this->conn->prepare($sql);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

}