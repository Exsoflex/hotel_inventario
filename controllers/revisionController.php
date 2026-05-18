<?php

require_once __DIR__ . "/../models/revision.php";

class RevisionController {

    public function index() {

        $revision = new Revision();

        $faltantes = $revision->obtenerFaltantes();

        require_once __DIR__ . "/../views/revision/index.php";

    }

}