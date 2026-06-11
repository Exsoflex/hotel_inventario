<?php

require_once __DIR__ . "/../models/usuarios.php";
require_once __DIR__ . "/../models/movimientos.php";
require_once __DIR__ . "/../config/auth.php";

class PerfilController {

    public function index() {

        $usuario = new Usuarios();
        $perfil  = $usuario->obtenerPorId($_SESSION['usuario']['id']);

        // Cargar movimientos del usuario actual
        $mov            = new Movimientos();
        $usuario_id     = $_SESSION['usuario']['id'];
        $movimientos    = $mov->obtenerTodo(50, 0, $usuario_id);

        require_once __DIR__ . "/../views/perfil/index.php";
    }

    public function editar() {

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $id       = $_SESSION['usuario']['id'];
            $nombre   = trim($_POST['nombre']);
            $correo   = trim($_POST['correo']);
            $password = trim($_POST['password']);

            if (empty($nombre) || empty($correo)) {
                $errorFormulario = 'Llena todos los campos por favor';
                $usuario         = new Usuarios();
                $perfil          = $usuario->obtenerPorId($id);
                $mov             = new Movimientos();
                $movimientos     = $mov->obtenerTodo(50, 0, $id);
                require_once __DIR__ . "/../views/perfil/index.php";
                return;
            }

            $usuario   = new Usuarios();
            $resultado = $usuario->editarPerfil($id, $nombre, $correo, $password);

            if ($resultado['exito']) {

                $_SESSION['usuario']['nombre'] = $nombre;

                $mov = new Movimientos();
                $mov->registrar(
                    'perfil',
                    'editar',
                    "Actualizó su perfil: nombre \"$nombre\", correo \"$correo\".",
                    $id
                );

                header("Location: index.php?modulo=perfil&exito=1");
                exit();

            } else {

                $errorFormulario = $resultado['error'] === 'duplicado'
                    ? "Ese nombre o correo ya está en uso."
                    : "Ocurrió un error al guardar. Intenta de nuevo.";

                $perfil      = $usuario->obtenerPorId($id);
                $mov         = new Movimientos();
                $movimientos = $mov->obtenerTodo(50, 0, $id);

                require_once __DIR__ . "/../views/perfil/index.php";
            }
        }
    }
}