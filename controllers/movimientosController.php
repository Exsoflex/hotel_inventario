<?php

require_once __DIR__ . "/../models/movimientos.php";
require_once __DIR__ . '/../config/auth.php';

class MovimientosController {

public function index() {

    $mov = new Movimientos();

    $rol = $_SESSION['usuario']['rol'];
    $usuario_id = $_SESSION['usuario']['id'];

    $pagina = isset($_GET['pagina'])
        ? (int)$_GET['pagina']
        : 1;

    $porPagina = 20;

    $offset = ($pagina - 1) * $porPagina;

    if ($rol === 'admin') {

        $movimientos =
            $mov->obtenerTodo(
                $porPagina,
                $offset
            );

        $totalRegistros =
            $mov->contarTodos();

    } else {

        $movimientos =
            $mov->obtenerTodo(
                $porPagina,
                $offset,
                $usuario_id
            );

        $totalRegistros =
            $mov->contarTodos(
                $usuario_id
            );
    }

    $totalPaginas =
        ceil(
            $totalRegistros /
            $porPagina
        );

    require_once __DIR__
        . "/../views/movimientos/index.php";
}


}