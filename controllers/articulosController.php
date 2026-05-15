<?php

require_once __DIR__ . "/../models/articulos.php";

class ArticulosController {

    public function index() {

        $articulo = new Articulos();
        $articulos = $articulo->obtenerTodo();
        require_once __DIR__ . "/../views/articulos/index.php";
    }

    public function agregar() {

        if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Verificar si la solicitud es de tipo POST

            $nombre = trim($_POST['nombre']);
            $descripcion = trim($_POST['descripcion']);

              if (
                empty($nombre)
                ) exit ("Llena todos los campos por favor");

            $modelarticulo = new Articulos();
            $modelarticulo->agregarArticulo($nombre, $descripcion);

            header("Location: index.php?modulo=articulos"); // Redirigir a la página principal después de agregar la habitación
            exit();
        }
    }

    public function eliminar() {

        $id = $_GET['id'];
        $modelarticulo = new Articulos();
        $modelarticulo->eliminarArticulo($id);
        header("Location: index.php?modulo=articulos");
    }


    public function editar() {

        $modelarticulo = new Articulos();

        // === MOSTRAR FORMULARIO === ///
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {

            $id = $_GET['id'];
            $articuloEditar = $modelarticulo->obtenerPorId($id);
            $articulos = $modelarticulo->obtenerTodo();
            require_once __DIR__ . "/../views/articulos/index.php";

        }

        // === GUARDAR CAMBIOS === ///
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Verificar si la solicitud es de tipo POST

            $id = $_POST['id'];

            $nombre = trim($_POST['nombre']);
            $descripcion = trim($_POST['descripcion']);

              if (
                empty($id) ||
                empty($nombre)
              ) {
                exit ("Llena todos los campos por favor");
                }

            $modelarticulo = new Articulos();

            $modelarticulo->editarArticulo(
                $id, 
                $nombre,
                $descripcion
            );

            header("Location: index.php?modulo=articulos"); // Redirigir a la página principal después de editar la habitación
            exit();
        }
    }
}
