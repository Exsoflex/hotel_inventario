<?php

require_once __DIR__ . "/../models/articulos.php";
require_once __DIR__ . "/../models/movimientos.php";
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ArticulosController {

    public function index() {

        verificarRol(['admin', 'supervisor']);

        $articulo = new Articulos();
        $articulos = $articulo->obtenerTodo();
        require_once __DIR__ . "/../views/articulos/index.php";
    }

    public function agregar() {

        verificarRol(['admin', 'supervisor']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $nombre     = trim($_POST['nombre']);
            $descripcion = trim($_POST['descripcion']);

            if (empty($nombre)) {
                $errorFormulario = 'Llena todos los campos por favor';
                $modelarticulo = new Articulos();
                $articulos = $modelarticulo->obtenerTodo();
                require_once __DIR__ . "/../views/articulos/index.php";
                return;
            }

            $modelarticulo = new Articulos();
            $resultado = $modelarticulo->agregarArticulo($nombre, $descripcion);

            if ($resultado['exito']) {

                $idNuevo = $resultado['id'];

                // Registrar movimiento
                $mov = new Movimientos();
                $mov->registrar(
                    'articulos',
                    'crear',
                    "Creó el artículo \"$nombre\"",
                    $idNuevo
                );

                header("Location: index.php?modulo=articulos#articulo-$idNuevo");
                exit();

            } else {

                $errorFormulario = $resultado['error'] === 'duplicado'
                    ? "Ya existe un artículo con ese nombre."
                    : "Ocurrió un error al guardar. Intenta de nuevo.";

                $modelarticulo2 = new Articulos();
                $articulos = $modelarticulo2->obtenerTodo();
                require_once __DIR__ . "/../views/articulos/index.php";
            }
        }
    }

    public function eliminar() {

        verificarRol(['admin', 'supervisor']);

        $id = $_GET['id'];
        $modelarticulo = new Articulos();

        // Obtener nombre antes de eliminar para el log
        $articulo = $modelarticulo->obtenerPorId($id);

        $modelarticulo->eliminarArticulo($id);

        // Registrar movimiento
        $mov = new Movimientos();
        $mov->registrar(
            'articulos',
            'eliminar',
            "Eliminó el artículo \"{$articulo['nombre']}\"",
            $id
        );

        header("Location: index.php?modulo=articulos");
        exit();
    }

    public function editar() {

        verificarRol(['admin', 'supervisor']);

        $modelarticulo = new Articulos();

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {

            $id = $_GET['id'];
            $articuloEditar = $modelarticulo->obtenerPorId($id);
            $articulos = $modelarticulo->obtenerTodo();
            require_once __DIR__ . "/../views/articulos/index.php";
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $id          = $_POST['id'];
            $nombre      = trim($_POST['nombre']);
            $descripcion = trim($_POST['descripcion']);

            if (empty($id) || empty($nombre)) {
                $errorFormulario = 'Llena todos los campos por favor';
                $articuloEditar = $modelarticulo->obtenerPorId($id);
                $articulos = $modelarticulo->obtenerTodo();
                require_once __DIR__ . "/../views/articulos/index.php";
                return;
            }

            $resultado = $modelarticulo->editarArticulo($id, $nombre, $descripcion);

            if ($resultado['exito']) {

                // Registrar movimiento
                $mov = new Movimientos();
                $mov->registrar(
                    'articulos',
                    'editar',
                    "Editó el artículo \"$nombre\"",
                    $id
                );

                header("Location: index.php?modulo=articulos#articulo-$id");
                exit();

            } else {

                $errorFormulario = $resultado['error'] === 'duplicado'
                    ? "Ya existe un artículo con ese nombre."
                    : "Ocurrió un error al guardar. Intenta de nuevo.";

                $articuloEditar = $modelarticulo->obtenerPorId($id);
                $articulos = $modelarticulo->obtenerTodo();
                require_once __DIR__ . "/../views/articulos/index.php";
            }
        }
    }

    public function exportar() {

        $modelo = new Articulos();
        $articulos = $modelo->obtenerTodo();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue(
            'A1',
            'REPORTE DE ARTÍCULOS'
        );
        $sheet->mergeCells('A1:C1');

        date_default_timezone_set('America/Matamoros');

        $sheet->setCellValue(
            'A2',
            'Fecha de exportación: ' . date('d/m/Y - h:i:s A')
        );

        $sheet->mergeCells('A2:C2');

        $sheet->setCellValue('A4', 'ID');
        $sheet->setCellValue('B4', 'Nombre');
        $sheet->setCellValue('C4', 'Descripción');

        $fila = 5;

        foreach($articulos as $a){

            $sheet->setCellValue(
                'A' . $fila,
                $a['id']
            );

            $sheet->setCellValue(
                'B' . $fila,
                $a['nombre']
            );

            $sheet->setCellValue(
                'C' . $fila,
                $a['descripcion']
            );

            $fila++;
        }
//------------------- ESTILOS ----------------------------------------------//
         $sheet->getStyle('A4:C4')->applyFromArray([
            'font' => [
                'bold' => true
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => 'D9EAD3'
                ]
            ]
        ]);

        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER
            ]
        ]);

        foreach (range('A', 'C') as $columna) {

            $sheet->getColumnDimension($columna)
                ->setAutoSize(true);

        }

        $sheet->getStyle('C:C')
            ->getAlignment()
            ->setWrapText(true);


        for($i = 5; $i < $fila; $i++){
            $sheet->getRowDimension($i)
                ->setRowHeight(30);
        }

        $sheet->getStyle(
            'A4:C' . ($fila - 1)
        )->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' =>
                        \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                ]
            ]
        ]);

        $sheet->getStyle('A2')->applyFromArray([
            'alignment' => [
                'horizontal' =>
                    Alignment::HORIZONTAL_CENTER
            ]
        ]);

        $sheet->freezePane('A5');

        $sheet->getStyle('A:A')->applyFromArray([
            'alignment' => [
                'horizontal' =>
                    Alignment::HORIZONTAL_CENTER
            ]
        ]);

        $writer = new Xlsx($spreadsheet);

        // Registrar movimiento
        $mov = new Movimientos();
        $mov->registrar(
            'articulos',
            'exportar',
            'Exportó la lista de artículos a Excel',
            null
        );

        header(
            'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );

        header(
            'Content-Disposition: attachment;filename="articulos.xlsx"'
        );

        header(
            'Cache-Control: max-age=0'
        );

        $writer->save('php://output');
        exit;
        }

}