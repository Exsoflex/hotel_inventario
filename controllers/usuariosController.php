<?php
require_once __DIR__ . "/../models/usuarios.php";
require_once __DIR__ . "/../models/movimientos.php";
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
            $resultado = $usuario->agregarUsuario(
                $nombre,
                $correo,
                $password,
                $rol
            );

            if ($resultado['exito']) {
                $idNuevo = $resultado['id'];

                $mov = new Movimientos();
                $mov->registrar(
                    'usuarios',
                    'crear',
                    "Creó el usuario \"$nombre\"",
                    $idNuevo
                );
                header("Location: index.php?modulo=usuarios#usuario-$idNuevo");
                exit();
            } else {
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

        $usuarios = $usuario->obtenerNombreUsuario($id);

        $mov = new Movimientos();
        $mov->registrar(
            'usuarios',
            'eliminar',
            "Eliminó el usuario \"$usuarios\"",
            $id
        );

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
            $rol = $_POST['rol'] ?? '';
            $activo = $_POST['activo'] ?? '';

            $usuarioActualizado = $usuario->obtenerPorId($id);

            if (!$usuarioActualizado) {
                header("Location: index.php?modulo=usuarios");
                exit();
            }

            if ((int)$id === (int)$_SESSION['usuario']['id']) {
                // Evita que un administrador pierda accidentalmente su acceso.
                $rol = $usuarioActualizado['rol'];
                $activo = $usuarioActualizado['activo'];
            }

            $dejaDeSerAdminActivo = $usuarioActualizado['rol'] === 'admin'
                && (int)$usuarioActualizado['activo'] === 1
                && ($rol !== 'admin' || (int)$activo !== 1);

            if ($dejaDeSerAdminActivo && $usuario->contarAdministradoresActivos() <= 1) {
                $errorFormulario = 'Debe permanecer al menos un administrador activo.';
                $usuarioEditar = array_merge($usuarioActualizado, [
                    'nombre' => $nombre,
                    'correo' => $correo,
                    'rol' => $rol,
                    'activo' => $activo,
                ]);
                $usuarios = $usuario->obtenerTodo();
                require_once __DIR__ . "/../views/usuarios/index.php";
                return;
            }

            if (
                empty($nombre) ||
                empty($correo) || 
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

                $mov = new Movimientos();
                $estadoTexto = $activo == 1 ? 'Activo' : 'Inactivo';

                $mov->registrar(
                    'usuarios',
                    'editar',
                    "Editó el usuario \"$nombre\" con el rol de \"$rol\" y estado \"$estadoTexto\"",
                    $id
                );
                header("Location: index.php?modulo=usuarios#usuario-$id");
                exit();
            } else {
                $errorFormulario = $resultado['error'] === 'duplicado'
                    ? "Ya existe un usuario con ese nombre o correo."
                    : "Ocurrió un error al guardar. Intenta de nuevo.";

                $usuarioEditar = array_merge($usuario->obtenerPorId($id), [
                    'nombre' => $nombre,
                    'correo' => $correo,
                    'rol' => $rol,
                    'activo' => $activo,
                ]);
                $usuarios = $usuario->obtenerTodo();
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

        $nombre = $usuario->obtenerNombreUsuario($id);

        $mov = new Movimientos();
                $mov->registrar(
                    'usuarios',
                    'editar',
                    "Se activó el usuario \"$nombre\"",
                    $id
                );
        

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

        $usuarioDesactivar = $usuario->obtenerPorId($id);

        if (!$usuarioDesactivar) {
            header("Location: index.php?modulo=usuarios");
            exit();
        }

        if ($usuarioDesactivar['rol'] === 'admin'
            && (int)$usuarioDesactivar['activo'] === 1
            && $usuario->contarAdministradoresActivos() <= 1
        ) {
            header("Location: index.php?modulo=usuarios&error=ultimo_admin");
            exit();
        }

        $usuario->cambiarEstado($id, 0);

        $nombre = $usuario->obtenerNombreUsuario($id);
        
        $mov = new Movimientos();
                $mov->registrar(
                    'usuarios',
                    'editar',
                    "Se desactivó el usuario \"$nombre\"",
                    $id
                );
        

        header("Location: index.php?modulo=usuarios");
        exit();
    }
}
