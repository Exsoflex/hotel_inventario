<?php

class Database {


/*
    private $host = "localhost";
    private $db_name = "hotel_inventario_prueba";
    private $username = "root";
    private $password = "root123";
*/

    private $host = "localhost";
    private $db_name = "hotel_inventario";
    private $username = "root";
    private $password = "root123";

    public $conn;

    public function conectar() {

        $this->conn = null;

        $host = getenv('DB_HOST') ?: $this->host;
        $dbName = getenv('DB_NAME') ?: $this->db_name;
        $username = getenv('DB_USER') ?: $this->username;
        $password = getenv('DB_PASS') ?: $this->password;

        try {

            $this->conn = new PDO(
                "mysql:host=" . $host . ";dbname=" . $dbName . ";charset=utf8",
                $username,
                $password
            );

            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        } catch(PDOException $e) {

            die("Error de conexión: " . $e->getMessage());

        }

        return $this->conn;
    }
}