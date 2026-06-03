<?php


//Para verificar la instalacion de la libreria de PhpSpreadsheet
require_once __DIR__ . '/../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;

$spreadsheet = new Spreadsheet();

echo "PhpSpreadsheet instalado correctamente";



//Este codigo es para encriptar contraseñas

//Remplaza 777 por tu contraseña

/*$password = 777;
echo password_hash("$password", PASSWORD_DEFAULT);*/