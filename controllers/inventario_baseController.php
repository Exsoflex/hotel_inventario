<?php

require_once __DIR__ . "/../models/inventario_base.php";
require_once __DIR__ . "/../models/movimientos.php";
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class InventariobaseController {

    public function index() {

        verificarRol(
        ['admin', 'supervisor']
        );

    $inventario_base = new Inventario_base();
    $inventarios_base = $inventario_base->obtenerTodo();

    $articulos = $inventario_base->obtenerArticulos();

    require_once __DIR__ . "/../views/inventario_base/index.php";

    }

    public function agregar() {

        verificarRol(['admin', 'supervisor']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $tipo        = $_POST['tipo'];
            $articulo_id = $_POST['articulo_id'];
            $cantidad    = $_POST['cantidad'];

            $inventario_base = new Inventario_base();

            if (empty($tipo) || empty($articulo_id) || $cantidad === '') {
                $errorFormulario = 'Llena todos los campos por favor';
                $inventarios_base = $inventario_base->obtenerTodo();
                $articulos = $inventario_base->obtenerArticulos();
                require_once __DIR__ . "/../views/inventario_base/index.php";
                return;
            }

            $resultado = $inventario_base->agregarInventario_base(
                $tipo,
                $articulo_id,
                $cantidad
            );

            if ($resultado['exito']) {

                $idNuevo = $resultado['id'];

                $nombreArticulo = $inventario_base->obtenerNombreArticulo($articulo_id);

                $mov = new Movimientos();
                $mov->registrar(
                    'inventario_base',
                    'crear',
                    "Agregó \"$nombreArticulo\" (cantidad: $cantidad) al inventario base de habitación $tipo",
                    $idNuevo
                );

                header("Location: index.php?modulo=inventario_base#inventario_base-$idNuevo");
                exit();

            }else {

                $errorFormulario = $resultado['error'] === 'duplicado'
                    ? 'Ya existe ese artículo para ese tipo de habitación.'
                    : 'Ocurrió un error al guardar.';

                $inventarios_base = $inventario_base->obtenerTodo();
                $articulos = $inventario_base->obtenerArticulos();

                require_once __DIR__ . "/../views/inventario_base/index.php";
            }
        }
    }

    public function eliminar() {

        verificarRol(['admin', 'supervisor']);

        $id = $_GET['id'];

        $inventario_base = new Inventario_base();

        // Obtener datos ANTES de eliminar
        $registro = $inventario_base->obtenerPorId($id);
        $nombreArticulo = $inventario_base->obtenerNombreArticulo($registro['articulo_id']);

        $inventario_base->eliminarInventario_base($id);

        $mov = new Movimientos();
        $mov->registrar(
            'inventario_base',
            'eliminar',
            "Eliminó \"$nombreArticulo\" del inventario base de habitación {$registro['tipo_habitacion']}",
            $id
        );

        header("Location: index.php?modulo=inventario_base");
        exit();
    }

    public function editar() {

        verificarRol(['admin', 'supervisor']);

        $modelInventario_base = new Inventario_base();

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {

            $id = $_GET['id'];
            $inventario_baseEditar = $modelInventario_base->obtenerPorId($id);
            $inventarios_base = $modelInventario_base->obtenerTodo();
            $articulos = $modelInventario_base->obtenerArticulos();
            require_once __DIR__ . "/../views/inventario_base/index.php";
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $id          = $_POST['id'];
            $tipo        = trim($_POST['tipo']);
            $articulo_id = trim($_POST['articulo_id']);
            $cantidad    = trim($_POST['cantidad']);

            if (empty($id) || empty($tipo) || empty($articulo_id) || $cantidad === '') {
                $errorFormulario = 'Llena todos los campos por favor';
                $inventario_baseEditar = $modelInventario_base->obtenerPorId($id);
                $inventarios_base = $modelInventario_base->obtenerTodo();
                $articulos = $modelInventario_base->obtenerArticulos();
                require_once __DIR__ . "/../views/inventario_base/index.php";
                return;
            }

            // Obtener nombre del artículo para el log
        $resultado = $modelInventario_base->editarInventario_base(
            $id,
            $tipo,
            $articulo_id,
            $cantidad
        );

            if ($resultado['exito']) {

                $nombreArticulo =
                    $modelInventario_base->obtenerNombreArticulo($articulo_id);

                $mov = new Movimientos();

                $mov->registrar(
                    'inventario_base',
                    'editar',
                    "Editó \"$nombreArticulo\" en inventario base de habitación $tipo: cantidad $cantidad",
                    $id
                );

                header(
                    "Location: index.php?modulo=inventario_base#inventario_base-$id"
                );
                exit();

            } else {

                $errorFormulario =
                    $resultado['error'] === 'duplicado'
                    ? 'Ya existe ese artículo para ese tipo de habitación.'
                    : 'Ocurrió un error al guardar.';

                $inventario_baseEditar =
                    $modelInventario_base->obtenerPorId($id);

                $inventarios_base =
                    $modelInventario_base->obtenerTodo();

                $articulos =
                    $modelInventario_base->obtenerArticulos();

                require_once __DIR__ . "/../views/inventario_base/index.php";
            }
        }
    }

        public function exportar() {

        $inventario_base = new Inventario_base();
        $inventario_base = $inventario_base->obtenerTodo();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue(
            'A1',
            'REPORTE DE INVENTARIO BASE'
        );
        $sheet->mergeCells('A1:D1');

        date_default_timezone_set('America/Matamoros');

        $sheet->setCellValue(
            'A2',
            'Fecha de exportación: ' . date('d/m/Y - h:i:s A')
        );

        $sheet->mergeCells('A2:D2');

        $sheet->setCellValue('A4', 'ID');
        $sheet->setCellValue('B4', 'Tipo de habitación');
        $sheet->setCellValue('C4', 'Artículo');
        $sheet->setCellValue('D4', 'Cantidad');

        $fila = 5;

        foreach($inventario_base as $i){

            $sheet->setCellValue(
                'A' . $fila,
                $i['id']
            );

            $sheet->setCellValue(
                'B' . $fila,
                $i['tipo_habitacion']
            );

            $sheet->setCellValue(
                'C' . $fila,
                $i['nombre']
            );

            $sheet->setCellValue(
                'D' . $fila,
                $i['cantidad']
            );

            $fila++;
        }
//------------------- ESTILOS ----------------------------------------------//
         $sheet->getStyle('A4:D4')->applyFromArray([
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

        foreach (range('A', 'D') as $columna) {

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
            'A4:D' . ($fila - 1)
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
            'inventario_base',
            'exportar',
            'Exportó la lista de inventario base a Excel',
            null
        );

        header(
            'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );

        header(
            'Content-Disposition: attachment;filename="inventario_base.xlsx"'
        );

        header(
            'Cache-Control: max-age=0'
        );

        $writer->save('php://output');
        exit;
        }

}
