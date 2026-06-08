<?php

require_once __DIR__ . "/../models/habitacion.php";
require_once __DIR__ . "/../models/movimientos.php";
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;


class HabitacionesController {

    public function index() {

        $habitacion = new Habitacion();
        $habitaciones = $habitacion->obtenerTodo();
        require_once __DIR__ . "/../views/habitaciones/index.php";
    }

    public function agregar() {

        verificarRol(
        ['admin', 'supervisor']
        );

        if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Verificar si la solicitud es de tipo POST

            $piso = trim($_POST['piso']);
            $numero = trim($_POST['numero']);
            $tipo = trim($_POST['tipo']);
            $descripcion = trim($_POST['descripcion']);
            $estado = trim($_POST['estado']);

              if (
                empty($piso) ||
                empty($numero) || 
                empty($tipo)
                ) {
                $errorFormulario = 'Llena todos los campos por favor';
                $modelhabitacion = new Habitacion();
                $habitaciones = $modelhabitacion->obtenerTodo();
                require_once __DIR__ . "/../views/habitaciones/index.php";
                return;
                }       

            $modelhabitacion = new Habitacion();
            $resultado = $modelhabitacion->agregarHabitacion(
                $piso, 
                $numero, 
                $tipo, 
                $descripcion,
                $estado);

            if ($resultado['exito']) {
                $idNuevo = $resultado['id'];

                // Registrar movimiento
                $mov = new Movimientos();
                $mov->registrar(
                    'habitaciones',
                    'crear',
                    "Creó la habitación \"$numero\"",
                    $idNuevo
                );

                header("Location: index.php?modulo=habitaciones#habitacion-$idNuevo");
                exit();
            } else {
                // Pasar error a la view
                $errorFormulario = $resultado['error'] === 'duplicado'
                    ? "Ya existe una habitación con ese numero."
                    : "Ocurrió un error al guardar. Intenta de nuevo.";

                $modelhabitacion = new Habitacion();
                $habitaciones = $modelhabitacion->obtenerTodo();
                require_once __DIR__ . "/../views/habitaciones/index.php";
            }
        }
    }

    public function eliminar() {

        verificarRol(
        ['admin', 'supervisor']
        );

        $id = $_GET['id'];
        $modelhabitacion = new Habitacion();

        // Obtener nombre antes de eliminar para el log
        $numero = $modelhabitacion->obtenerPorId($id);
        $modelhabitacion->eliminarHabitacion($id);

                // Registrar movimiento
        $mov = new Movimientos();
        $mov->registrar(
            'habitaciones',
            'eliminar',
            "Eliminó la habitación \"{$numero['numero']}\"",
            $id
        );

        header("Location: index.php?modulo=habitaciones"); // Redirigir a la página principal después de eliminar la habitación

    }

    public function editar() {

        verificarRol(
        ['admin', 'supervisor']
        );

        $modelhabitacion = new Habitacion();

        // === MOSTRAR FORMULARIO === ///
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {

            $id = $_GET['id'];
            $habitacionEditar = $modelhabitacion->obtenerPorId($id);
            $habitaciones = $modelhabitacion->obtenerTodo();
            require_once __DIR__ . "/../views/habitaciones/index.php";

        }

        // === GUARDAR CAMBIOS === ///
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Verificar si la solicitud es de tipo POST

            $id = $_POST['id'];

            $piso = trim($_POST['piso']);
            $numero = trim($_POST['numero']);
            $tipo = trim($_POST['tipo']);
            $descripcion = trim($_POST['descripcion']);
            $estado = trim($_POST['estado']);

              if (
                empty($id) ||
                empty($piso) ||
                empty($numero) || 
                empty($tipo)
              )  {
                $errorFormulario = 'Llena todos los campos por favor';
                $habitacionEditar = $modelhabitacion->obtenerPorId($id);
                $habitaciones = $modelhabitacion->obtenerTodo();
                require_once __DIR__ . "/../views/habitaciones/index.php";
                return;
            }

            $modelhabitacion = new Habitacion();

            $resultado = $modelhabitacion->editarHabitacion(
                $id, 
                $piso, 
                $numero, 
                $tipo, 
                $descripcion, 
                $estado
            );

            if ($resultado['exito']) {

                // Registrar movimiento
                $mov = new Movimientos();
                $mov->registrar(
                    'habitaciones',
                    'editar',
                    "Editó la habitación \"$numero\"",
                    $id
                );
            
                header("Location: index.php?modulo=habitaciones#habitacion-$id");
                exit();
            } else {
                $errorFormulario = $resultado['error'] === 'duplicado'
                    ? "Ya existe una habitación con ese nombre."
                    : "Ocurrió un error al guardar. Intenta de nuevo.";

                $articuloEditar = $modelarticulo->obtenerPorId($id);
                $articulos = $modelarticulo->obtenerTodo();
                require_once __DIR__ . "/../views/habitaciones/index.php";
            }
        }
    }

     public function exportar() {

        $habitacion = new Habitacion();
        $habitaciones = $habitacion->obtenerTodo();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue(
            'A1',
            'REPORTE DE HABITACIONES'
        );
        $sheet->mergeCells('A1:E1');

        date_default_timezone_set('America/Matamoros');

        $sheet->setCellValue(
            'A2',
            'Fecha de exportación: ' . date('d/m/Y - h:i:s A')
        );

        $sheet->mergeCells('A2:F2');

        $sheet->setCellValue('A4', 'ID');
        $sheet->setCellValue('B4', 'Piso');
        $sheet->setCellValue('C4', 'Número');
        $sheet->setCellValue('D4', 'Tipo');
        $sheet->setCellValue('E4', 'Descripción');
        $sheet->setCellValue('F4', 'Estado');

        $fila = 5;

        foreach($habitaciones as $h){

            $sheet->setCellValue(
                'A' . $fila,
                $h['id']
            );

            $sheet->setCellValue(
                'B' . $fila,
                $h['piso']
            );

            $sheet->setCellValue(
                'C' . $fila,
                $h['numero']
            );

            $sheet->setCellValue(
                'D' . $fila,
                $h['tipo']
            );

            $sheet->setCellValue(
                'E' . $fila,
                $h['descripcion']
            );

            $sheet->setCellValue(
                'F' . $fila,
                $h['estado']
            );

            $fila++;
        }
//------------------- ESTILOS ----------------------------------------------//
         $sheet->getStyle('A4:F4')->applyFromArray([
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

        foreach (range('A', 'F') as $columna) {

            $sheet->getColumnDimension($columna)
                ->setAutoSize(true);

        }

        $sheet->getStyle('F:F')
            ->getAlignment()
            ->setWrapText(true);


        for($i = 5; $i < $fila; $i++){
            $sheet->getRowDimension($i)
                ->setRowHeight(30);
        }

        $sheet->getStyle(
            'A4:F' . ($fila - 1)
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

        $sheet->getStyle('A:C')->applyFromArray([
            'alignment' => [
                'horizontal' =>
                    Alignment::HORIZONTAL_CENTER
            ]
        ]);

        $writer = new Xlsx($spreadsheet);

        // Registrar movimiento
        $mov = new Movimientos();
        $mov->registrar(
            'habitaciones',
            'exportar',
            'Exportó la lista de habitaciones a Excel',
            null
        );

        header(
            'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );

        header(
            'Content-Disposition: attachment;filename="habitaciones.xlsx"'
        );

        header(
            'Cache-Control: max-age=0'
        );

        $writer->save('php://output');
        exit;
        }
}
