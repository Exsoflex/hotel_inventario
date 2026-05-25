<?php

require_once __DIR__ . "/../config/database.php";

class Usuario {

    private $conn;

    public function __construct() {

        $database = new Database();
        $this->conn = $database->conectar();
    }

      // ===========================||
     // BUSCAR USUARIO POR USERNAME ||
    // =============================||

    public function obtenerPorLogin($login) {

    $sql = "SELECT * FROM usuarios
            WHERE (
                nombre = :login
                OR correo = :login
            )
            AND activo = 1
            LIMIT 1";

    $stmt = $this->conn->prepare($sql);

    $stmt->bindParam(":login", $login);

    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
    }

}