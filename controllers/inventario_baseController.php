<?php

require_once __DIR__ . "/../models/inventario_base.php";

class InventariobaseController {

    public function index() {

    $inventario_base = new Inventario_base();
    $inventarios_base = $inventario_base->obtenerTodo();

    $articulos = $inventario_base->obtenerArticulos();

    require_once __DIR__ . "/../views/inventario_base/index.php";

    }

    public function agregar () {

        if($_SERVER['REQUEST_METHOD'] === 'POST') {

            $tipo = $_POST['tipo'];
            $articulo_id = $_POST['articulo_id'];
            $cantidad = $_POST['cantidad'];

            $inventario_base = new Inventario_base();

            $idNuevo =$inventario_base->agregarInventario_base(
                $tipo,
                $articulo_id, 
                $cantidad
            );

            header("Location: index.php?modulo=inventario_base#inventario_base-$idNuevo");
            exit();
        }

    }

    public function eliminar() {

        $id = $_GET['id'];
        $inventario_base = new Inventario_base();
        $inventario_base->eliminarInventario_base($id);
        header("Location: index.php?modulo=inventario_base");

    }

    public function editar() {

    $modelInventario_base = new Inventario_base();

        // === MOSTRAR FORMULARIO === ///
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {

            $id = $_GET['id'];
            $inventario_baseEditar = $modelInventario_base->obtenerPorId($id);
            $inventarios_base = $modelInventario_base->obtenerTodo();
            $articulos = $modelInventario_base->obtenerArticulos();
            require_once __DIR__ . "/../views/inventario_base/index.php";

        }

        // === GUARDAR CAMBIOS === ///
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Verificar si la solicitud es de tipo POST

            $id = $_POST['id'];

            $tipo = trim($_POST['tipo']);
            $articulo_id = trim($_POST['articulo_id']);
            $cantidad = trim($_POST['cantidad']);

            if (
                empty($id) ||
                empty($tipo) ||
                empty($articulo_id) ||
                $cantidad === ''
            ) {
                exit ("Llena todos los campos por favor");
            }

            $modelInventario_base->editarInventario_base( 
                $id,
                $tipo,
                $articulo_id, 
                $cantidad
            );

            header("Location: index.php?modulo=inventario_base#inventario_base-$id"); // Redirigir a la página principal después de editar el inventario
            exit();

        }
    }


}
