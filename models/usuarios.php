<?php

require_once __DIR__ . "/../config/database.php";

class Usuarios {

    private $conn;

    public function __construct(){

        $database = new Database();
        $this->conn = $database->conectar();
    }

    public function obtenerTodo(){

        $sql = "SELECT * FROM usuarios ORDER BY nombre";

        $stmt = $this->conn->prepare($sql);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId($id){

        $sql = "SELECT * FROM usuarios WHERE id = :id";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindParam(":id", $id);

        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function agregarUsuario(
        $nombre,
        $correo,
        $password,
        $rol
    ){

        $passwordHash = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        $sql = "INSERT INTO usuarios
                (
                    nombre,
                    correo,
                    password,
                    rol
                )
                VALUES
                (
                    :nombre,
                    :correo,
                    :password,
                    :rol
                )";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":correo", $correo);
        $stmt->bindParam(":password", $passwordHash);
        $stmt->bindParam(":rol", $rol);

        $stmt->execute();

        return $this->conn->lastInsertId();
    }

    public function editarUsuario(
        $id,
        $nombre,
        $correo,
        $rol,
        $activo
    ){

        $sql = "UPDATE usuarios
                SET
                    nombre = :nombre,
                    correo = :correo,
                    rol = :rol,
                    activo = :activo
                WHERE id = :id";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":correo", $correo);
        $stmt->bindParam(":rol", $rol);
        $stmt->bindParam(":activo", $activo);

        $stmt->execute();
    }

    public function eliminarUsuario($id){

        $sql = "DELETE FROM usuarios
                WHERE id = :id";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindParam(":id", $id);

        $stmt->execute();
    }

    public function cambiarEstado($id, $activo)
    {
        $sql = "UPDATE usuarios
                SET activo = :activo
                WHERE id = :id";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':activo', $activo);

        $stmt->execute();
    }
}