<?php

require_once __DIR__ . "/../models/habitacion.php";
require_once __DIR__ . '/../config/auth.php';

class HabitacionesController {

    public function index() {

        $habitacion = new Habitacion();
        $habitaciones = $habitacion->obtenerTodo();
        require_once __DIR__ . "/../views/habitaciones/index.php";
    }

    public function agregar() {

        verificarRol(
        ['admin', 'supervisor']
        );

        if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Verificar si la solicitud es de tipo POST

            $piso = trim($_POST['piso']);
            $numero = trim($_POST['numero']);
            $tipo = trim($_POST['tipo']);
            $descripcion = trim($_POST['descripcion']);

              if (
                empty($piso) ||
                empty($numero) || 
                empty($tipo)
                ) {
                $errorFormulario = 'Llena todos los campos por favor';
                $modelhabitacion = new Habitacion();
                $habitaciones = $modelhabitacion->obtenerTodo();
                require_once __DIR__ . "/../views/habitaciones/index.php";
                return;
                }       

            $modelhabitacion = new Habitacion();
            $resultado = $modelhabitacion->agregarHabitacion(
                $piso, 
                $numero, 
                $tipo, 
                $descripcion);

            if ($resultado['exito']) {
                $idNuevo = $resultado['id'];
                header("Location: index.php?modulo=habitaciones#habitacion-$idNuevo");
                exit();
            } else {
                // Pasar error a la view
                $errorFormulario = $resultado['error'] === 'duplicado'
                    ? "Ya existe una habitación con ese numero."
                    : "Ocurrió un error al guardar. Intenta de nuevo.";

                $modelhabitacion = new Habitacion();
                $habitaciones = $modelhabitacion->obtenerTodo();
                require_once __DIR__ . "/../views/habitaciones/index.php";
            }
        }
    }

    public function eliminar() {

        verificarRol(
        ['admin', 'supervisor']
        );

        $id = $_GET['id'];
        $modelhabitacion = new Habitacion();
        $modelhabitacion->eliminarHabitacion($id);
        header("Location: index.php?modulo=habitaciones"); // Redirigir a la página principal después de eliminar la habitación

    }

    public function editar() {

        verificarRol(
        ['admin', 'supervisor']
        );

        $modelhabitacion = new Habitacion();

        // === MOSTRAR FORMULARIO === ///
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {

            $id = $_GET['id'];
            $habitacionEditar = $modelhabitacion->obtenerPorId($id);
            $habitaciones = $modelhabitacion->obtenerTodo();
            require_once __DIR__ . "/../views/habitaciones/index.php";

        }

        // === GUARDAR CAMBIOS === ///
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Verificar si la solicitud es de tipo POST

            $id = $_POST['id'];

            $piso = trim($_POST['piso']);
            $numero = trim($_POST['numero']);
            $tipo = trim($_POST['tipo']);
            $descripcion = trim($_POST['descripcion']);

              if (
                empty($id) ||
                empty($piso) ||
                empty($numero) || 
                empty($tipo)
              )  {
                $errorFormulario = 'Llena todos los campos por favor';
                $habitacionEditar = $modelhabitacion->obtenerPorId($id);
                $habitaciones = $modelhabitacion->obtenerTodo();
                require_once __DIR__ . "/../views/habitaciones/index.php";
                return;
            }

            $modelhabitacion = new Habitacion();

            $resultado = $modelhabitacion->editarHabitacion(
                $id, 
                $piso, 
                $numero, 
                $tipo, 
                $descripcion
            );

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
