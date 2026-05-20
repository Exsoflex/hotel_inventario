<?php

require_once __DIR__ . "/../models/inventario.php";

class InventarioController {

    public function index() {

    $inventario = new Inventario();
    $inventarios = $inventario->obtenerTodo();

    $habitaciones = $inventario->obtenerHabitaciones();
    $articulos = $inventario->obtenerArticulos();

    require_once __DIR__ . "/../views/inventario/index.php";

    }

    public function agregar () {

        if($_SERVER['REQUEST_METHOD'] === 'POST') {

            $habitacion_id = $_POST['habitacion_id'];
            $articulo_id = $_POST['articulo_id'];
            $cantidad = $_POST['cantidad'];
            $estado = $_POST['estado'];
            $comentarios = $_POST['comentarios'];

            $inventario = new Inventario();

            $idNuevo =$inventario->agregarInventario(
                $habitacion_id,
                $articulo_id, 
                $cantidad, 
                $estado,
                $comentarios
            );

            header("Location: index.php?modulo=inventario&mensaje=agregado#inventario-$idNuevo");
            exit();
        }

    }

    public function eliminar() {

        $id = $_GET['id'];
        $inventario = new Inventario();
        $inventario->eliminarInventario($id);

        header("Location: index.php?modulo=inventario&mensaje=eliminado");
        exit();

    }

    public function editar() {

    $modelInventario = new Inventario();

        // === MOSTRAR FORMULARIO === ///
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {

            $id = $_GET['id'];
            $inventarioEditar = $modelInventario->obtenerPorId($id);
            $inventarios = $modelInventario->obtenerTodo();
            $habitaciones = $modelInventario->obtenerHabitaciones();
            $articulos = $modelInventario->obtenerArticulos();
            require_once __DIR__ . "/../views/inventario/index.php";

        }

        // === GUARDAR CAMBIOS === ///
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Verificar si la solicitud es de tipo POST

            $id = $_POST['id'];

            $habitacion_id = trim($_POST['habitacion_id']);
            $articulo_id = trim($_POST['articulo_id']);
            $cantidad = trim($_POST['cantidad']);
            $estado = trim($_POST['estado']);
            $comentarios = trim($_POST['comentarios']);

            if (
                empty($habitacion_id) ||
                empty($articulo_id) ||
                empty($cantidad) ||
                empty($estado)
            ) {
                exit ("Llena todos los campos por favor");
            }

            $modelInventario->editarInventario( 
                $id,
                $habitacion_id,
                $articulo_id, 
                $cantidad, 
                $estado,
                $comentarios
            );

            header("Location: index.php?modulo=inventario&mensaje=editado#inventario-$id");
            exit();

        }
    }


}
