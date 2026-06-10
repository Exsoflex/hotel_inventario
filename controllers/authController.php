<?php

require_once __DIR__ . "/../models/usuario.php";
require_once __DIR__ . '/../models/movimientos.php';

class AuthController {

    // =========================
    // MOSTRAR LOGIN
    // =========================

    public function index(){

    if(isset($_SESSION['usuario'])){

        header("Location: index.php?modulo=dashboard");
        exit();
    }

    require_once __DIR__ . '/../views/login.php';
}

      // =========================||                        ||=========================//
     // INICIAR SESION           //===========()===========//--------( -o- )--------- //
    // =========================||                        ||=========================//

    public function login() {

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $login = trim($_POST['login'] ?? '');
        $password = $_POST['password'] ?? '';

            if(empty($login) || empty($password)){

                header("Location: index.php?modulo=auth&error=campos");
                exit();
            }
            
            $usuarioModel = new Usuario();

            $usuarioDB = $usuarioModel->obtenerPorLogin($login);

            // Verificar usuario
            if (!$usuarioDB) {

                header("Location: index.php?modulo=auth&error=usuario");
                exit();
            }

            // Verificar contraseña
            if (!password_verify($password, $usuarioDB['password'])) {

                header("Location: index.php?modulo=auth&error=password");
                exit();
            }

            // Actualizar último acceso
            $usuarioModel->actualizarUltimoLogin(
                $usuarioDB['id']
            );

            require_once __DIR__ . "/../models/movimientos.php";


              // =========================
             // CREAR SESION
            // =========================

            //session_start();
            session_regenerate_id(true);
            $_SESSION['usuario'] = [

                'id' => $usuarioDB['id'],
                'nombre' => $usuarioDB['nombre'],
                'usuario' => $usuarioDB['usuario'],
                'rol' => $usuarioDB['rol']

            ];

             // Después de crear la sesión, antes del redirect:
            $mov = new Movimientos();
            $mov->registrar(
                'auth',
                'login',
                "Inició sesión",
                $usuarioDB['id']
            );

            // Redireccionar al dashboard
            header("Location: index.php?modulo=dashboard");

            exit();
        }
    }

    // =========================
    // CERRAR SESION
    // =========================

    public function logout() {

        $mov = new Movimientos();

        $mov->registrar(
            'auth',
            'logout',
            "Cerró sesión"
        );

        $_SESSION = [];

        session_unset();

        session_destroy();

        header("Location: index.php?modulo=auth");

        exit();
    }

}