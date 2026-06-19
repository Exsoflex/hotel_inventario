<?php

require_once __DIR__ . "/../models/historial_codigos.php";
require_once __DIR__ . "/../models/movimientos.php";
require_once __DIR__ . '/../config/auth.php';

class HistorialCodigosController {

    public function index() {

        $modelo = new HistorialCodigos();

        $rol       = $_SESSION['usuario']['rol'];
        $usuario_id = $_SESSION['usuario']['id'];

        // Admin ve todo, los demás solo lo suyo
        if ($rol === 'admin') {
            $historial = $modelo->obtenerTodo();
        } else {
            $historial = $modelo->obtenerTodo($usuario_id);
        }

        require_once __DIR__ . "/../views/historial_codigos/index.php";
    }

    public function buscar() {

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?modulo=historial_codigos");
            exit();
        }

        $codigo = trim($_POST['codigo'] ?? '');

        if (empty($codigo)) {
            header("Location: index.php?modulo=historial_codigos&error=vacio");
            exit();
        }

        $modelo = new HistorialCodigos();
        $resultado = $modelo->buscarPorCodigo($codigo);

        // Código no encontrado en inventario
        if (!$resultado) {
            header("Location: index.php?modulo=historial_codigos&error=no_encontrado&codigo=" . urlencode($codigo));
            exit();
        }

        // Registrar en historial
        $usuario_id = $_SESSION['usuario']['id'];
        $modelo->registrar($usuario_id, $resultado['id']);

        // Redirigir a inventario con el código en la barra de búsqueda
        header("Location: index.php?modulo=inventario&buscar=" . urlencode($codigo));
        exit();
    }

    public function eliminar() {

        verificarRol(['admin', 'supervisor']);

        $id = $_GET['id'] ?? null;

        if (!$id) {
            header("Location: index.php?modulo=historial_codigos");
            exit();
        }

        $modelo = new HistorialCodigos();
        $modelo->eliminar($id);

        $mov = new Movimientos();
        $mov->registrar(
            'historial_codigos',
            'eliminar',
            'Eliminó un código',
            $id
        );

        header("Location: index.php?modulo=historial_codigos&mensaje=eliminado");
        exit();
    }
}