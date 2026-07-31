<?php

require_once __DIR__ . "/../models/movimientos.php";
require_once __DIR__ . '/../config/auth.php';

class MovimientosController {

public function index() {

    $mov = new Movimientos();

    $rol = $_SESSION['usuario']['rol'];
    $usuario_id = $_SESSION['usuario']['id'];

    $pagina = max(1, (int)($_GET['pagina'] ?? 1));
    $buscar = trim($_GET['buscar'] ?? '');

    $porPagina = 20;

    if ($rol === 'admin') {
        $totalRegistros = $mov->contarTodos(null, $buscar);

    } else {
        $totalRegistros = $mov->contarTodos($usuario_id, $buscar);
    }

    $totalPaginas = max(1, (int)ceil($totalRegistros / $porPagina));
    $pagina = min($pagina, $totalPaginas);
    $offset = ($pagina - 1) * $porPagina;

    $movimientos = $mov->obtenerTodo(
        $porPagina,
        $offset,
        $rol === 'admin' ? null : $usuario_id,
        $buscar
    );

    require_once __DIR__
        . "/../views/movimientos/index.php";
}


}
