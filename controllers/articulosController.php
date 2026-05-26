<?php

require_once __DIR__ . "/../models/articulos.php";
require_once __DIR__ . '/../config/auth.php';

class ArticulosController {

    public function index() {
        
        verificarRol(
        ['admin', 'supervisor']
        );

        $articulo = new Articulos();
        $articulos = $articulo->obtenerTodo();
        require_once __DIR__ . "/../views/articulos/index.php";
    }

    public function agregar() {

        verificarRol(['admin', 'supervisor']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $nombre = trim($_POST['nombre']);
            $descripcion = trim($_POST['descripcion']);

            if (empty($nombre)) {
                $errorFormulario = 'Llena todos los campos por favor';
                $modelarticulo = new Articulos();
                $articulos = $modelarticulo->obtenerTodo();
                require_once __DIR__ . "/../views/articulos/index.php";
                return;
            }

            $modelarticulo = new Articulos();
            $resultado = $modelarticulo->agregarArticulo($nombre, $descripcion);

            if ($resultado['exito']) {
                $idNuevo = $resultado['id'];
                header("Location: index.php?modulo=articulos#articulo-$idNuevo");
                exit();
            } else {
                // Pasar error a la view
                $errorFormulario = $resultado['error'] === 'duplicado'
                    ? "Ya existe un artículo con ese nombre."
                    : "Ocurrió un error al guardar. Intenta de nuevo.";

                $modelarticulo2 = new Articulos();
                $articulos = $modelarticulo2->obtenerTodo();
                require_once __DIR__ . "/../views/articulos/index.php";
            }
        }
    }

    public function eliminar() {

        verificarRol(
        ['admin', 'supervisor']
        );

        $id = $_GET['id'];
        $modelarticulo = new Articulos();
        $modelarticulo->eliminarArticulo($id);
        header("Location: index.php?modulo=articulos");
    }


    public function editar() {

        verificarRol(['admin', 'supervisor']);

        $modelarticulo = new Articulos();

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $id = $_GET['id'];
            $articuloEditar = $modelarticulo->obtenerPorId($id);
            $articulos = $modelarticulo->obtenerTodo();
            require_once __DIR__ . "/../views/articulos/index.php";
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $id = $_POST['id'];
            $nombre = trim($_POST['nombre']);
            $descripcion = trim($_POST['descripcion']);

            if (empty($id) || empty($nombre)) {
                $errorFormulario = 'Llena todos los campos por favor';
                $articuloEditar = $modelarticulo->obtenerPorId($id);
                $articulos = $modelarticulo->obtenerTodo();
                require_once __DIR__ . "/../views/articulos/index.php";
                return;
            }

            $resultado = $modelarticulo->editarArticulo($id, $nombre, $descripcion);

            if ($resultado['exito']) {
                header("Location: index.php?modulo=articulos#articulo-$id");
                exit();
            } else {
                $errorFormulario = $resultado['error'] === 'duplicado'
                    ? "Ya existe un artículo con ese nombre."
                    : "Ocurrió un error al guardar. Intenta de nuevo.";

                $articuloEditar = $modelarticulo->obtenerPorId($id);
                $articulos = $modelarticulo->obtenerTodo();
                require_once __DIR__ . "/../views/articulos/index.php";
            }
        }
    }
}
