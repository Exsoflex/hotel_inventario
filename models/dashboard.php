<?php

require_once __DIR__ . "/../config/database.php";

class Dashboard {

    private $conn;

    public function __construct() {

        $database = new Database();
        $this->conn = $database->conectar();

    }

    public function obtenerResumen() {

        $sql = "SELECT * FROM vista_dashboard
                WHERE estado_habitacion != 'bloqueada'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

}