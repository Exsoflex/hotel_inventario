<?php

require_once __DIR__ . "/../models/usuario.php";

class AuthController {

    // =========================
    // MOSTRAR LOGIN
    // =========================

    public function index() {

        require_once __DIR__ . "/../views/login.php";
    }

    // =========================
    // INICIAR SESION
    // =========================

    public function login() {

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $usuario = trim($_POST['usuario']);
            $password = trim($_POST['password']);

            // Validar campos vacíos
            if (empty($usuario) || empty($password)) {

                header("Location: index.php?modulo=auth&error=campos");
                exit();
            }

            $usuarioModel = new Usuario();

            $usuarioDB = $usuarioModel->obtenerPorUsuario($usuario);

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

            // =========================
            // CREAR SESION
            // =========================

            session_start();

            $_SESSION['usuario'] = [

                'id' => $usuarioDB['id'],
                'nombre' => $usuarioDB['nombre'],
                'usuario' => $usuarioDB['usuario'],
                'rol' => $usuarioDB['rol']

            ];

            // Redireccionar al dashboard
            header("Location: index.php?modulo=dashboard");

            exit();
        }
    }

    // =========================
    // CERRAR SESION
    // =========================

    public function logout() {

        session_start();

        session_destroy();

        header("Location: index.php?modulo=auth");

        exit();
    }

}