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

            if (
                empty($nombre) ||
                empty($correo) || 
                empty($password) || 
                empty($rol)
                ) {
                $errorFormulario = 'Llena todos los campos por favor';
                $modelusuario = new Usuarios();
                $usuarios = $modelusuario->obtenerTodo();
                require_once __DIR__ . "/../views/usuarios/index.php";
                return;
                }  

            $usuario = new Usuarios();
            $resultado = $idNuevo = $usuario->agregarUsuario(
                $nombre,
                $correo,
                $password,
                $rol
            );

            if ($resultado['exito']) {
                $idNuevo = $resultado['id'];
                header("Location: index.php?modulo=usuarios#usuario-$idNuevo");
                exit();
            } else {
                // Pasar error a la view
                $errorFormulario = $resultado['error'] === 'duplicado'
                    ? "Ya existe un usuario con ese nombre o correo."
                    : "Ocurrió un error al guardar. Intenta de nuevo.";

                $modelusuario = new Usuarios();
                $usuarios = $modelusuario->obtenerTodo();
                require_once __DIR__ . "/../views/usuarios/index.php";
            }
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

            if (
                empty($nombre) ||
                empty($correo) || 
                empty($password) || 
                empty($rol)
                ) {
                $errorFormulario = 'Llena todos los campos por favor';
                $modelusuario = new Usuarios();
                $usuarios = $modelusuario->obtenerTodo();
                require_once __DIR__ . "/../views/usuarios/index.php";
                return;
            } 

            $resultado = $usuario->editarUsuario(
                $id,
                $nombre,
                $correo,
                $rol,
                $activo
            );

            if ($resultado['exito']) {
                header("Location: index.php?modulo=usuarios#usuario-$id");
                exit();
            } else {
                $errorFormulario = $resultado['error'] === 'duplicado'
                    ? "Ya existe un usuario con ese nombre o correo."
                    : "Ocurrió un error al guardar. Intenta de nuevo.";

                $usuarioEditar = $modelusuario->obtenerPorId($id);
                $usuarios = $modelusuario->obtenerTodo();
                require_once __DIR__ . "/../views/usuarios/index.php";
            }
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

        $usuario = new Usuarios();
        $usuario->cambiarEstado($id, 0);
        

        header("Location: index.php?modulo=usuarios");
        exit();
    }
}