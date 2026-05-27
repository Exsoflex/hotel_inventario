<?php

require_once __DIR__ . "/../models/movimientos.php";
require_once __DIR__ . '/../config/auth.php';

class MovimientosController {

    public function index() {

        verificarRol(['admin', 'supervisor']);

        $mov = new Movimientos();

        $pagina = isset($_GET['pagina'])
            ? (int)$_GET['pagina']
            : 1;

        $porPagina = 20;

        $offset = ($pagina - 1) * $porPagina;

        $movimientos =
            $mov->obtenerTodo(
                $porPagina,
                $offset
            );

        $totalRegistros =
            $mov->contarTodos();

        $totalPaginas =
            ceil(
                $totalRegistros /
                $porPagina
            );

        require_once __DIR__
            . "/../views/movimientos/index.php";
    }


}