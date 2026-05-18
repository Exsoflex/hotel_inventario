<?php

require_once __DIR__ . "/../models/dashboard.php";

class DashboardController {

    public function index() {

        $dashboard = new Dashboard();
        $habitaciones = $dashboard->obtenerResumen();

        require_once __DIR__ . "/../views/dashboard/index.php";

    }

}