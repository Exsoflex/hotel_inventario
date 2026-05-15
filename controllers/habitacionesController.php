<?php

require_once __DIR__ . "/../models/habitacion.php";

class HabitacionesController {

    public function index() {

        $habitacion = new Habitacion();
        $habitaciones = $habitacion->obtenerTodo();
        require_once __DIR__ . "/../views/habitaciones/index.php";
    }

    public function agregar() {

        if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Verificar si la solicitud es de tipo POST

            $piso = trim($_POST['piso']);
            $numero = trim($_POST['numero']);
            $tipo = trim($_POST['tipo']);
            $descripcion = trim($_POST['descripcion']);

              if (
                empty($piso) ||
                empty($numero) || 
                empty($tipo)
                ) exit ("Llena todos los campos por favor");

            $modelhabitacion = new Habitacion();
            $idNuevo = $modelhabitacion->agregarHabitacion($piso, $numero, $tipo, $descripcion);

            header("Location: index.php?modulo=habitaciones#habitacion-$idNuevo"); // Redirigir a la página principal después de agregar la habitación
            exit();
        }
    }

    public function eliminar() {

        $id = $_GET['id'];
        $modelhabitacion = new Habitacion();
        $modelhabitacion->eliminarHabitacion($id);
        header("Location: index.php?modulo=habitaciones"); // Redirigir a la página principal después de eliminar la habitación

    }

    public function editar() {

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
              ) {
                exit ("Llena todos los campos por favor");
                }

            $modelhabitacion = new Habitacion();

            $modelhabitacion->editarHabitacion(
                $id, 
                $piso, 
                $numero, 
                $tipo, 
                $descripcion
            );

            header("Location: index.php?modulo=habitaciones#habitacion-$id"); // Redirigir a la página principal después de editar la habitación
            exit();
        }
    }
}
