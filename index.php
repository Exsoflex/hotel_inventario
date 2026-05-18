<?php

$modulo = $_GET['modulo'] ?? 'home';
$action = $_GET['accion'] ?? 'index';

switch($modulo) {

    case 'home':

        require_once __DIR__ . "/views/home.php";
        exit();

    case 'habitaciones':

        require_once __DIR__ . "/controllers/habitacionesController.php";
        $controller = new HabitacionesController();
        break;

    case 'articulos':

        require_once __DIR__ . "/controllers/articulosController.php";
        $controller = new ArticulosController();
        break;

    case 'inventario':

        require_once __DIR__ . "/controllers/inventarioController.php";
        $controller = new InventarioController();
        break;

    case 'inventario_base':

        require_once __DIR__ . "/controllers/inventario_baseController.php";
        $controller = new InventariobaseController();
        break;

    case 'revision':

        require_once __DIR__ . "/controllers/revisionController.php";
        $controller = new RevisionController();
        break;

    default:

        require_once __DIR__ . "/controllers/habitacionesController.php";
        $controller = new HabitacionesController();
        break;
}

switch($action) {

    case 'agregar':
        $controller->agregar();
        break;

    case 'eliminar':
        $controller->eliminar();
        break;

    case 'editar':
        $controller->editar();
        break;

    default:
        $controller->index();
        break;
}

?>