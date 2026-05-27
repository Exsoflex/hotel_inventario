<?php
session_start();

$modulo = $_GET['modulo'] ?? 'auth';

/* módulos públicos */
$modulosPublicos = ['auth'];

/* protección de login */
if (
    !isset($_SESSION['usuario']) &&
    !in_array($modulo, $modulosPublicos)
) {

    header("Location: index.php?modulo=auth");
    exit();
}

/* ========================= */
/* PERMISOS POR ROL */
/* ========================= */

$permisos = [

    'admin' => [
        'dashboard',
        'revision',
        'inventario',
        'habitaciones',
        'articulos',
        'inventario_base',
        'usuarios',
        'movimientos'
    ],

    'supervisor' => [
        'dashboard',
        'revision',
        'inventario',
        'habitaciones',
        'articulos',
        'inventario_base'
    ],

    'operador' => [
        'dashboard',
        'revision',
        'inventario',
        'habitaciones'
    ]
];

/* validar permisos */

if(isset($_SESSION['usuario'])){

    $rol = $_SESSION['usuario']['rol'];

    if(
        isset($permisos[$rol]) &&
        !in_array($modulo, $permisos[$rol]) &&
        !in_array($modulo, $modulosPublicos)
    ){

        header("Location: index.php?modulo=dashboard");
        exit();
    }
}

$modulo = $_GET['modulo'] ?? 'home';
$action = $_GET['accion'] ?? 'index';
switch($modulo) {

    case 'auth':

        require_once __DIR__ . "/controllers/authController.php";
        $controller = new AuthController();
        break;

    case 'dashboard':

        require_once __DIR__ . "/controllers/dashboardController.php";
        $controller = new DashboardController();
        break;

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

    case 'usuarios':

        require_once __DIR__ . "/controllers/usuariosController.php";
        $controller = new UsuariosController();
        break;

    case 'movimientos':

        require_once __DIR__ . "/controllers/movimientosController.php";
        $controller = new MovimientosController();
        break;

    default:

        require_once __DIR__ . "/controllers/authController.php";
        $controller = new AuthController();
        break;
}

switch($action) {

    case 'login':
        $controller->login();
        break;

    case 'logout':
        $controller->logout();
        break;

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

    case 'activar':
    $controller->activar();
    break;

    case 'desactivar':
    $controller->desactivar();
    break;
}

?>