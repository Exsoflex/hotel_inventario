<?php

//Este codigo es para encriptar contraseñas

//Remplaza 777 por tu contraseña
$password = 777;
echo password_hash("$password", PASSWORD_DEFAULT);