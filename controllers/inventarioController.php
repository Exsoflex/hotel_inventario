<?php

require_once __DIR__ . "/../models/inventario.php";
require_once __DIR__ . "/../models/movimientos.php";
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class InventarioController {

    public function index() {

    $inventario = new Inventario();
    $inventarios = $inventario->obtenerTodo();

    $habitaciones = $inventario->obtenerHabitaciones();
    $articulos = $inventario->obtenerArticulos();

    require_once __DIR__ . "/../views/inventario/index.php";

    }

    public function agregar () {

        verificarRol(
        ['admin', 'supervisor']
        );

        if($_SERVER['REQUEST_METHOD'] === 'POST') {

            $habitacion_id = $_POST['habitacion_id'];
            $articulo_id = $_POST['articulo_id'];
            $cantidad = $_POST['cantidad'];
            $estado = $_POST['estado'];
            $comentarios = $_POST['comentarios'];

            $inventario = new Inventario();

            $codigo = $_POST['codigo_barras'] ?? null;

            $articulo = $inventario->obtenerArticuloPorId($articulo_id);

            if(!$articulo['usa_codigo_barras']){
                $codigo = null;
            }

            if (
                empty($habitacion_id) || 
                empty($articulo_id) || 
                $cantidad === '' || 
                empty($estado))
            {
                $errorFormulario = 'Llena todos los campos por favor';

                $inventarios = $inventario->obtenerTodo();
                $habitaciones = $inventario->obtenerHabitaciones();
                $articulos = $inventario->obtenerArticulos();

                require_once __DIR__ . "/../views/inventario/index.php";
                return;
            }

            $inventario = new Inventario();

            $resultado = $inventario->agregarInventario(
                $habitacion_id,
                $articulo_id,
                $cantidad,
                $estado,
                $comentarios,
                $codigo
            );

            if($resultado['exito']){
                $idNuevo = $resultado['id'];
                            $habitaciones = $inventario->obtenerNumeroHabitacion($habitacion_id);
            $articulos = $inventario->obtenerNombreArticulo($articulo_id);

            // Registrar movimiento
                $mov = new Movimientos();
                $mov->registrar(
                    'inventario',
                    'crear',
                    "Creó un nuevo inventario a la habitación \"$habitaciones\" con el artículos \"$articulos\" (cantidad: $cantidad)",
                    $idNuevo
                );

            header("Location: index.php?modulo=inventario&mensaje=agregado#inventario-$idNuevo");
            exit();
            }else{

                $errorFormulario =
                    $resultado['error'] === 'duplicado'
                    ? 'Ya existe ese artículo en esa habitación.'
                    : 'Ocurrió un error al guardar.';

                $inventarios = $inventario->obtenerTodo();
                $habitaciones = $inventario->obtenerHabitaciones();
                $articulos = $inventario->obtenerArticulos();

                require_once __DIR__ . "/../views/inventario/index.php";
            }
        }  
    }

    public function eliminar() {

        verificarRol(
        ['admin', 'supervisor']
        );

        $id = $_GET['id'];
        $inventario = new Inventario();
        $inventarioEliminar = $inventario->obtenerPorId($id);
        $habitacion_id = $inventarioEliminar['habitacion_id'];
        $articulo_id = $inventarioEliminar['articulo_id'];
        $idNuevo = $inventarioEliminar['id'];

        $inventario = new Inventario();
        $inventario->eliminarInventario($id);

        $habitaciones = $inventario->obtenerNumeroHabitacion($habitacion_id);
        $articulos = $inventario->obtenerNombreArticulo($articulo_id);

            // Registrar movimiento
                $mov = new Movimientos();
                $mov->registrar(
                    'inventario',
                    'eliminar',
                    "Eliminó el artículo \"$articulos\" de la habitación \"$habitaciones\"",
                    $idNuevo
                );

        header("Location: index.php?modulo=inventario&mensaje=eliminado");
        exit();

    }

public function editar() {

    verificarRol(['admin', 'supervisor']);

    $modelInventario = new Inventario();

    // ======================
    // MOSTRAR FORMULARIO
    // ======================
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {

        $id = $_GET['id'];

        $inventarioEditar = $modelInventario->obtenerPorId($id);
        $inventarios = $modelInventario->obtenerTodo();
        $habitaciones = $modelInventario->obtenerHabitaciones();
        $articulos = $modelInventario->obtenerArticulos();

        require_once __DIR__ . "/../views/inventario/index.php";
        return;
    }

    // ======================
    // GUARDAR CAMBIOS
    // ======================
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $id = $_POST['id'];

        $habitacion_id = trim($_POST['habitacion_id']);
        $articulo_id = trim($_POST['articulo_id']);
        $cantidad = trim($_POST['cantidad']);
        $estado = trim($_POST['estado']);
        $comentarios = trim($_POST['comentarios']);
        $codigo = trim($_POST['codigo_barras'] ?? '');

        // VALIDACIÓN AQUÍ (NO ARRIBA)
        if (
            empty($habitacion_id) ||
            empty($articulo_id) ||
            $cantidad === '' ||
            empty($estado)
        ) {
            exit("Llena todos los campos por favor");
        }

        $articulo = $modelInventario->obtenerArticuloPorId($articulo_id);

        if (!$articulo['usa_codigo_barras']) {
            $codigo = null;
        }

        $resultado = $modelInventario->editarInventario(
            $id,
            $habitacion_id,
            $articulo_id,
            $cantidad,
            $estado,
            $comentarios,
            $codigo
        );

        if ($resultado['exito']) {

            header("Location: index.php?modulo=inventario&mensaje=editado#inventario-$id");
            exit();

        } else {

            $inventarioEditar = $modelInventario->obtenerPorId($id);
            $inventarios = $modelInventario->obtenerTodo();
            $habitaciones = $modelInventario->obtenerHabitaciones();
            $articulos = $modelInventario->obtenerArticulos();

            require_once __DIR__ . "/../views/inventario/index.php";
        }
    }
}

public function exportar() {

$buscar = $_GET['buscar'] ?? '';
$estado = $_GET['estado'] ?? '';
$articulos = $_GET['articulos'] ?? '';

$inventario = new Inventario();
$inventarios = $inventario->obtenerTodo();

$inventariosFiltrados = [];

$articulosSeleccionados = [];

if(!empty($articulos)){
    $articulosSeleccionados =
        explode(',', strtolower($articulos));
}

foreach($inventarios as $i){

    $coincideBuscar =
        empty($buscar)
        || stripos($i['nombre'], $buscar) !== false
        || stripos($i['numero'], $buscar) !== false;

    $coincideEstado =
        empty($estado)
        || $i['estado'] === $estado;

    $coincideArticulo =
        empty($articulosSeleccionados)
        || in_array(
            strtolower($i['nombre']),
            $articulosSeleccionados
        );

    if(
        $coincideBuscar &&
        $coincideEstado &&
        $coincideArticulo
    ){
        $inventariosFiltrados[] = $i;
    }
}

$inventarios = $inventariosFiltrados;


$inventarioAgrupado = [];

foreach($inventarios as $i){

    $inventarioAgrupado[$i['numero']][] = $i;

}
ksort($inventarioAgrupado);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

$sheet->setCellValue(
    'A1',
    'REPORTE DE INVENTARIO'
);

$sheet->mergeCells('A1:E1');

date_default_timezone_set('America/Matamoros');

$sheet->setCellValue(
    'A2',
    'Fecha de exportación: ' . date('d/m/Y - h:i:s A')
);

$sheet->mergeCells('A2:E2');

$fila = 4;

foreach($inventarioAgrupado as $numero => $items){

$sheet->setCellValue(
    'A' . $fila,
    'HABITACIÓN ' . $numero
);

$sheet->mergeCells(
    'A' . $fila . ':E' . $fila
);

$sheet->getStyle(
    'A' . $fila . ':E' . $fila
)->applyFromArray([

    'font' => [
        'bold' => true,
        'size' => 14
    ],

    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => [
            'rgb' => 'B6D7A8'
        ]
    ]
]);

$fila++;

$sheet->setCellValue('A' . $fila, 'Artículo');
$sheet->setCellValue('B' . $fila, 'Cantidad');
$sheet->setCellValue('C' . $fila, 'Estado');
$sheet->setCellValue('D' . $fila, 'Comentarios');
$sheet->setCellValue('E' . $fila, 'Código');

$sheet->getStyle(
    'A' . $fila . ':E' . $fila
)->applyFromArray([

    'font' => [
        'bold' => true
    ],

    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => [
            'rgb' => 'D9EAD3'
        ]
    ],

    'alignment' => [
    'horizontal' => Alignment::HORIZONTAL_CENTER
    ]
]);

$fila++;

foreach($items as $i){

$sheet->setCellValue(
    'A' . $fila,
    $i['nombre']
);

$sheet->setCellValue(
    'B' . $fila,
    $i['cantidad']
);

$sheet->setCellValue(
    'C' . $fila,
    $i['estado']
);

$sheet->setCellValue(
    'D' . $fila,
    $i['comentarios']
);

$sheet->setCellValue(
    'E' . $fila,
    $i['codigo_barras']
);

$fila++;
}
$fila += 2;
}


foreach(range('A', 'E') as $columna){

    $sheet->getColumnDimension($columna)
        ->setAutoSize(true);
}

$sheet->getColumnDimension('D')
    ->setWidth(40);

    $sheet->getStyle('D:D')
    ->getAlignment()
    ->setWrapText(true);

    $sheet->getStyle(
    'A1:E' . ($fila - 1)
)->applyFromArray([

    'borders' => [
        'allBorders' => [
            'borderStyle' =>
                \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
        ]
    ]
]);

$sheet->getStyle('A1:E1')->applyFromArray([
    'font' => [
        'bold' => true,
        'size' => 16
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER
    ]
]);

$sheet->getStyle('A2:E2')->applyFromArray([
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER
    ]
]);

$writer = new Xlsx($spreadsheet);

$mov = new Movimientos();

$mov->registrar(
    'inventario',
    'exportar',
    'Exportó la lista de inventario a Excel',
    null
);

header(
    'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
);

header(
    'Content-Disposition: attachment;filename="inventario.xlsx"'
);

header(
    'Cache-Control: max-age=0'
);

$writer->save('php://output');
exit;
}
}



