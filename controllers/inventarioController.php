<?php

require_once __DIR__ . "/../models/inventario.php";
require_once __DIR__ . "/../models/movimientos.php";
require_once __DIR__ . '/../config/auth.php';

class InventarioController {

    public function index() {

    $inventario = new Inventario();
    $inventarios = $inventario->obtenerTodo();

    $habitaciones = $inventario->obtenerHabitaciones();
    $articulos = $inventario->obtenerArticulos();

    require_once __DIR__ . "/../views/inventario/index.php";

    }

    public function agregar () {

        verificarRol(
        ['admin', 'supervisor']
        );

        if($_SERVER['REQUEST_METHOD'] === 'POST') {

            $habitacion_id = $_POST['habitacion_id'];
            $articulo_id = $_POST['articulo_id'];
            $cantidad = $_POST['cantidad'];
            $estado = $_POST['estado'];
            $comentarios = $_POST['comentarios'];

            if (
                empty($habitacion_id) || 
                empty($articulo_id) || 
                $cantidad === '' || 
                empty($estado))
            {
                 exit ("Llena todos los campos por favor");
            }

            $inventario = new Inventario();

            $idNuevo =$inventario->agregarInventario(
                $habitacion_id,
                $articulo_id, 
                $cantidad, 
                $estado,
                $comentarios
            );

            $habitaciones = $inventario->obtenerNumeroHabitacion($habitacion_id);
            $articulos = $inventario->obtenerNombreArticulo($articulo_id);

            // Registrar movimiento
                $mov = new Movimientos();
                $mov->registrar(
                    'inventario',
                    'crear',
                    "Creó un nuevo inventario a la habitación \"$habitaciones\" con el artículos \"$articulos\" (cantidad: $cantidad)",
                    $idNuevo
                );

            header("Location: index.php?modulo=inventario&mensaje=agregado#inventario-$idNuevo");
            exit();
        }

    }

    public function eliminar() {

        verificarRol(
        ['admin', 'supervisor']
        );

        $id = $_GET['id'];
        $inventario = new Inventario();
        $inventarioEliminar = $inventario->obtenerPorId($id);
        $habitacion_id = $inventarioEliminar['habitacion_id'];
        $articulo_id = $inventarioEliminar['articulo_id'];
        $idNuevo = $inventarioEliminar['id'];

        $inventario = new Inventario();
        $inventario->eliminarInventario($id);

        $habitaciones = $inventario->obtenerNumeroHabitacion($habitacion_id);
        $articulos = $inventario->obtenerNombreArticulo($articulo_id);

            // Registrar movimiento
                $mov = new Movimientos();
                $mov->registrar(
                    'inventario',
                    'eliminar',
                    "Eliminó el artículo \"$articulos\" de la habitación \"$habitaciones\"",
                    $idNuevo
                );

        header("Location: index.php?modulo=inventario&mensaje=eliminado");
        exit();

    }

    public function editar() {

        verificarRol(
        ['admin', 'supervisor']
        );
        
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
                $cantidad === '' ||
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

            $habitaciones = $modelInventario->obtenerNumeroHabitacion($habitacion_id);
            $articulos = $modelInventario->obtenerNombreArticulo($articulo_id);

            // Registrar movimiento
                $mov = new Movimientos();
                $mov->registrar(
                    'inventario',
                    'editar',
                    "Editó el inventario a la habitación \"$habitaciones\" con el artículos \"$articulos\" (cantidad: $cantidad)",
                    $id
                );

            header("Location: index.php?modulo=inventario&mensaje=editado#inventario-$id");
            exit();

        }
    }


}
