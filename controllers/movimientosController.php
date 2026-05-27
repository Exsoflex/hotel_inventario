<?php

require_once __DIR__ . "/../models/movimientos.php";
require_once __DIR__ . '/../config/auth.php';

class MovimientosController {

    public function index() {

        verificarRol(['admin', 'supervisor']);

        $mov = new Movimientos();
        $movimientos = $mov->obtenerTodo();

        require_once __DIR__ . "/../views/movimientos/index.php";
    }
}