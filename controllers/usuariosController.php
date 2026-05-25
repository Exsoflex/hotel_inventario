<?php

require_once __DIR__ . "/../models/usuarios.php";
require_once __DIR__ . "/../config/auth.php";

class UsuariosController {

    public function index(){

        verificarRol(['admin']);

        $usuario = new Usuarios();

        $usuarios = $usuario->obtenerTodo();

        require_once __DIR__ .
        "/../views/usuarios/index.php";
    }

    public function agregar(){

        verificarRol(['admin']);

        if($_SERVER['REQUEST_METHOD'] === 'POST'){

            $nombre = trim($_POST['nombre']);
            $correo = trim($_POST['correo']);
            $password = $_POST['password'];
            $rol = $_POST['rol'];

            $usuario = new Usuarios();

            $idNuevo = $usuario->agregarUsuario(
                $nombre,
                $correo,
                $password,
                $rol
            );

            header(
                "Location: index.php?modulo=usuarios#usuario-$idNuevo"
            );

            exit();
        }
    }

    public function eliminar(){

        verificarRol(['admin']);

        $id = $_GET['id'];

        $usuario = new Usuarios();

        $usuario->eliminarUsuario($id);

        header("Location: index.php?modulo=usuarios");

        exit();
    }

    public function editar(){

        verificarRol(['admin']);

        $usuario = new Usuarios();

        if($_SERVER['REQUEST_METHOD'] === 'GET'){

            $id = $_GET['id'];

            $usuarioEditar =
                $usuario->obtenerPorId($id);

            $usuarios =
                $usuario->obtenerTodo();

            require_once __DIR__ .
            "/../views/usuarios/index.php";
        }

        if($_SERVER['REQUEST_METHOD'] === 'POST'){

            $id = $_POST['id'];

            $nombre = trim($_POST['nombre']);
            $correo = trim($_POST['correo']);
            $rol = $_POST['rol'];
            $activo = $_POST['activo'];

            $usuario->editarUsuario(
                $id,
                $nombre,
                $correo,
                $rol,
                $activo
            );

            header(
                "Location: index.php?modulo=usuarios#usuario-$id"
            );

            exit();
        }
    }

    public function activar()
    {
        verificarRol(['admin']);

        $id = $_GET['id'];

        $usuario = new Usuarios();

        $usuario->cambiarEstado($id, 1);
        

        header("Location: index.php?modulo=usuarios");

        exit();
    }
    
    public function desactivar()
    {

        verificarRol(['admin']);

        $id = $_GET['id'];

        if($id == $_SESSION['usuario']['id']){
            exit('No puedes desactivar tu propia cuenta');
        }

        if(
            $id == $_SESSION['usuario']['id']
            && $_SESSION['usuario']['rol'] != 'admin'
        ){
            exit('No puedes quitarte el rol de administrador');
        }
        $usuario = new Usuarios();
        $usuario->cambiarEstado($id, 0);
        

        header("Location: index.php?modulo=usuarios");
    }
}