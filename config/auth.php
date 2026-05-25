<?php

function verificarRol($rolesPermitidos){

    if(
        !isset($_SESSION['usuario']) ||
        !in_array(
            $_SESSION['usuario']['rol'],
            $rolesPermitidos
        )
    ){

        header("Location: index.php?modulo=dashboard");
        exit();
    }
}