<?php

require_once __DIR__ . "/../models/inventario.php";
require_once __DIR__ . "/../models/movimientos.php";
require_once __DIR__ . "/../models/historial_articulos.php";
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class InventarioController {

private function obtenerFiltrosDesdeRequest() {

    return [
        'buscar' => trim($_REQUEST['buscar'] ?? ''),
        'estado' => trim($_REQUEST['estado'] ?? ($_REQUEST['estado_filtro'] ?? '')),
        'articulos' => trim($_REQUEST['articulos'] ?? ($_REQUEST['articulos_filtro'] ?? '')),
        'piso' => isset($_REQUEST['piso']) ? (int) $_REQUEST['piso'] : 1,
    ];
}

private function crearQueryInventario($mensaje = null, $filtros = []) {

    $query = [
        'modulo' => 'inventario',
    ];

    if ($mensaje !== null) {
        $query['mensaje'] = $mensaje;
    }

    foreach (['buscar', 'estado', 'articulos', 'piso'] as $filtro) {
        if (!empty($filtros[$filtro])) {
            $query[$filtro] = $filtros[$filtro];
        }
    }

    return http_build_query($query);
}

private function agruparPorHabitacion($inventarios) {

    $agrupado = [];

    foreach ($inventarios as $item) {

        $numero = $item['numero'];

        if (!isset($agrupado[$numero])) {
            $agrupado[$numero] = [
                'numero' => $item['numero'],
                'piso' => $item['piso'],
                'items' => []
            ];
        }

        $agrupado[$numero]['items'][] = $item;
    }

    return array_values($agrupado);
}

public function index() {

    $inventario = new Inventario();
    $filtros = $this->obtenerFiltrosDesdeRequest();
    $inventarios = $inventario->obtenerTodo();
    $habitaciones = $inventario->obtenerHabitaciones();
    $articulos = $inventario->obtenerArticulos();
    $pisos = $inventario->obtenerPisos();
    $piso = $filtros['piso'];

    require_once __DIR__ . "/../views/inventario/index.php";
}

public function ajax() {

    $inventario = new Inventario();
    $filtros = $this->obtenerFiltrosDesdeRequest();
    $piso = $filtros['buscar'] === '' ? $filtros['piso'] : null;

    $inventarios = $inventario->obtenerTodo(
        $piso,
        $filtros['buscar'],
        $filtros['estado'],
        $filtros['articulos']
    );

    header('Content-Type: application/json');
    echo json_encode($this->agruparPorHabitacion($inventarios));
    exit;
}

public function agregar() {

    verificarRol(['admin', 'supervisor']);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $filtros = $this->obtenerFiltrosDesdeRequest();

        $habitacion_id = $_POST['habitacion_id'];
        $articulo_id   = $_POST['articulo_id'];
        $cantidad      = $_POST['cantidad'];
        $estado        = $_POST['estado'];
        $comentarios   = trim($_POST['comentarios'] ?? '');

        $habitacion_id = filter_var($habitacion_id, FILTER_VALIDATE_INT);
        $articulo_id   = filter_var($articulo_id, FILTER_VALIDATE_INT);
        $cantidadInt   = filter_var($cantidad, FILTER_VALIDATE_INT);

        if ($habitacion_id === false || $articulo_id === false || $cantidadInt === false || $cantidadInt < 0 || empty($estado)) {

            $errorFormulario = 'Llena todos los campos con valores válidos por favor';
            $inventarios     = (new Inventario())->obtenerTodo();
            $habitaciones    = (new Inventario())->obtenerHabitaciones();
            $articulos       = (new Inventario())->obtenerArticulos();
            $pisos           = (new Inventario())->obtenerPisos();
            $piso            = $filtros['piso'];

            require_once __DIR__ . "/../views/inventario/index.php";
            return;
        }
        $cantidad = $cantidadInt;

        $inventario = new Inventario();
        $codigo     = $_POST['codigo_barras'] ?? '';
        if ($codigo !== '') {
            $codigo = trim($codigo);
        }
        $articulo   = $inventario->obtenerArticuloPorId($articulo_id);

        if (!$articulo) {
            $errorFormulario = 'El artículo seleccionado no es válido.';
            $inventarios     = $inventario->obtenerTodo();
            $habitaciones    = $inventario->obtenerHabitaciones();
            $articulos       = $inventario->obtenerArticulos();
            $pisos           = $inventario->obtenerPisos();
            $piso            = $filtros['piso'];

            require_once __DIR__ . "/../views/inventario/index.php";
            return;
        }

        if (!$articulo['usa_codigo_barras']) {
            $codigo = null;
        }

        $resultado = $inventario->agregarInventario(
            $habitacion_id, $articulo_id, $cantidad,
            $estado, $comentarios, $codigo
        );

if ($resultado['exito']) {

             $idNuevo     = $resultado['id'];
             $numHab      = $inventario->obtenerNumeroHabitacion($habitacion_id);
             $nomArticulo = $inventario->obtenerNombreArticulo($articulo_id);

             $mov = new Movimientos();
             $mov->registrar(
                 'inventario', 'crear',
                 "Creó inventario en habitación \"$numHab\" con artículo \"$nomArticulo\" (cantidad: $cantidad)",
                 $idNuevo
             );

             $filtros['buscar'] = $numHab;
             $query = $this->crearQueryInventario('agregado', $filtros);

             header("Location: index.php?$query#inventario-$idNuevo");
             exit();

        } else {

            $errorFormulario = $resultado['error'] === 'duplicado'
                ? 'Ya existe ese artículo en esa habitación.'
                : 'Ocurrió un error al guardar.';

            $inventarios  = $inventario->obtenerTodo();
            $habitaciones = $inventario->obtenerHabitaciones();
            $articulos    = $inventario->obtenerArticulos();
            $pisos        = $inventario->obtenerPisos();
            $piso         = $filtros['piso'];

            require_once __DIR__ . "/../views/inventario/index.php";
        }
    }
}

public function eliminar() {

    verificarRol(['admin', 'supervisor']);

    $id           = $_GET['id'];
    $filtros      = $this->obtenerFiltrosDesdeRequest();

    $inventario         = new Inventario();
    $inventarioEliminar = $inventario->obtenerPorId($id);

    if (!$inventarioEliminar) {
        $query = $this->crearQueryInventario('error_no_encontrado', $filtros);
        header("Location: index.php?$query");
        exit();
    }

    $habitacion_id      = $inventarioEliminar['habitacion_id'];
    $articulo_id        = $inventarioEliminar['articulo_id'];

    $inventario->eliminarInventario($id);

    $numHab      = $inventario->obtenerNumeroHabitacion($habitacion_id);
    $nomArticulo = $inventario->obtenerNombreArticulo($articulo_id);

    $mov = new Movimientos();
    $mov->registrar(
        'inventario', 'eliminar',
        "Eliminó el artículo \"$nomArticulo\" de la habitación \"$numHab\"",
        $id
    );

    $query = $this->crearQueryInventario('eliminado', $filtros);

    header("Location: index.php?$query");
    exit();
}

public function editar() {

    verificarRol(['admin', 'supervisor']);

    $modelInventario = new Inventario();

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {

        $id           = $_GET['id'];
        $filtros      = $this->obtenerFiltrosDesdeRequest();

        $inventarioEditar = $modelInventario->obtenerPorId($id);

        if (!$inventarioEditar) {
            $query = $this->crearQueryInventario('error_no_encontrado', $filtros);
            header("Location: index.php?$query");
            exit();
        }

        $inventarios      = $modelInventario->obtenerTodo();
        $habitaciones     = $modelInventario->obtenerHabitaciones();
        $articulos        = $modelInventario->obtenerArticulos();
        $pisos            = $modelInventario->obtenerPisos();
        $piso             = $filtros['piso'];

        require_once __DIR__ . "/../views/inventario/index.php";
        return;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $id           = $_POST['id'];
        $filtros      = $this->obtenerFiltrosDesdeRequest();

        $habitacion_id = filter_var($_POST['habitacion_id'], FILTER_VALIDATE_INT);
        $articulo_id   = filter_var($_POST['articulo_id'], FILTER_VALIDATE_INT);
        $cantidadInt   = filter_var($_POST['cantidad'], FILTER_VALIDATE_INT);
        $estado        = $_POST['estado'];
        $comentarios   = trim($_POST['comentarios'] ?? '');
        $codigo        = trim($_POST['codigo_barras'] ?? '');

        if ($habitacion_id === false || $articulo_id === false || $cantidadInt === false || $cantidadInt < 0 || empty($estado)) {

            $errorFormulario  = 'Llena todos los campos con valores válidos por favor';
            $inventarioEditar = $modelInventario->obtenerPorId($id);
            $inventarios      = $modelInventario->obtenerTodo();
            $habitaciones     = $modelInventario->obtenerHabitaciones();
            $articulos        = $modelInventario->obtenerArticulos();
            $pisos            = $modelInventario->obtenerPisos();
            $piso             = $filtros['piso'];

            require_once __DIR__ . "/../views/inventario/index.php";
            return;
        }
        $cantidad = $cantidadInt;

        $articulo = $modelInventario->obtenerArticuloPorId($articulo_id);

        if (!$articulo) {
            $errorFormulario  = 'El artículo seleccionado no es válido.';
            $inventarioEditar = $modelInventario->obtenerPorId($id);
            $inventarios      = $modelInventario->obtenerTodo();
            $habitaciones     = $modelInventario->obtenerHabitaciones();
            $articulos        = $modelInventario->obtenerArticulos();
            $pisos            = $modelInventario->obtenerPisos();
            $piso             = $filtros['piso'];

            require_once __DIR__ . "/../views/inventario/index.php";
            return;
        }

        if (!$articulo['usa_codigo_barras']) {
            $codigo = null;
        }

        $resultado = $modelInventario->editarInventario(
            $id, $habitacion_id, $articulo_id,
            $cantidad, $estado, $comentarios, $codigo
        );

if ($resultado['exito']) {

             $mov = new Movimientos();

             $numHab = $modelInventario->obtenerNumeroHabitacion($habitacion_id);
             $nomArticulo = $modelInventario->obtenerNombreArticulo($articulo_id);

             $mov->registrar(
                 'inventario',
                 'editar',
                 "Editó inventario de \"$nomArticulo\" en habitación \"$numHab\"",
                 $id
             );

             $numHab = $modelInventario->obtenerNumeroHabitacion($habitacion_id);
             $filtros['buscar'] = $numHab;
             $query = $this->crearQueryInventario('editado', $filtros);

             header("Location: index.php?$query#inventario-$id");
             exit();

        } else {

            $errorFormulario  = $resultado['error'] === 'duplicado'
                ? 'Ya existe ese artículo en esa habitación.'
                : 'Ocurrió un error al guardar.';

            $inventarioEditar = $modelInventario->obtenerPorId($id);
            $inventarios      = $modelInventario->obtenerTodo();
            $habitaciones     = $modelInventario->obtenerHabitaciones();
            $articulos        = $modelInventario->obtenerArticulos();
            $pisos            = $modelInventario->obtenerPisos();
            $piso             = $filtros['piso'];

            require_once __DIR__ . "/../views/inventario/index.php";
        }
    }
}

public function exportar() {

   /* verificarRol(['admin', 'supervisor', 'operador']);*/

    $inventario = new Inventario();
    $filtros = $this->obtenerFiltrosDesdeRequest();

    // El piso corresponde a la página visible en pantalla, no a un filtro
    // de exportación. El reporte siempre debe incluir todas las habitaciones.
    $piso = null;

    $inventarios = $inventario->obtenerTodo(
        $piso,
        $filtros['buscar'],
        $filtros['estado'],
        $filtros['articulos']
    );


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

public function historial() {

    verificarRol(['admin', 'supervisor', 'operador']);

    $inventario_id = filter_var($_GET['inventario_id'] ?? 0, FILTER_VALIDATE_INT);

    if ($inventario_id === false || $inventario_id < 1) {
        http_response_code(400);
        echo json_encode(['error' => 'ID de inventario inválido']);
        return;
    }

    $historial = (new HistorialArticulos())->obtenerPorInventarioId($inventario_id);

    header('Content-Type: application/json');
    echo json_encode($historial);
    exit;
}

public function agregarHistorial() {

    verificarRol(['admin', 'supervisor']);

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit();
    }

    $inventario_id = filter_var($_POST['inventario_id'] ?? 0, FILTER_VALIDATE_INT);
    $fecha         = $_POST['fecha'] ?? '';
    $nota          = trim($_POST['nota'] ?? '');

    if ($inventario_id === false || $inventario_id < 1 || $fecha === '' || $nota === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Todos los campos son obligatorios']);
        return;
    }

    $inventario = new Inventario();
    $item = $inventario->obtenerPorId($inventario_id);
    $numHab = $item ? $inventario->obtenerNumeroHabitacion($item['habitacion_id']) : '#' . $inventario_id;
    $nomArticulo = $item ? $inventario->obtenerNombreArticulo($item['articulo_id']) : 'Desconocido';

    $historial = new HistorialArticulos();
    $id = $historial->agregar($inventario_id, $fecha, $nota);

    $mov = new Movimientos();
    $mov->registrar(
        'historial_articulos',
        'crear',
        "Agregó un registro al historial de \"$nomArticulo\" en habitación \"$numHab\"",
        $id
    );

    header('Content-Type: application/json');
    echo json_encode(['exito' => true, 'id' => $id]);
    exit;
}

public function editarHistorial() {

    verificarRol(['admin', 'supervisor']);

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit();
    }

    $id      = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT);
    $fecha   = $_POST['fecha'] ?? '';
    $nota    = trim($_POST['nota'] ?? '');

    if ($id === false || $id < 1 || $fecha === '' || $nota === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Todos los campos son obligatorios']);
        return;
    }

    $historial = new HistorialArticulos();
    $entry = $historial->obtenerPorId($id);
    $nomArticulo = '';
    $numHab = '';

    if ($entry) {
        $inventario = new Inventario();
        $item = $inventario->obtenerPorId($entry['inventario_id']);
        if ($item) {
            $numHab = $inventario->obtenerNumeroHabitacion($item['habitacion_id']);
            $nomArticulo = $inventario->obtenerNombreArticulo($item['articulo_id']);
        }
    }

    $historial->editar($id, $fecha, $nota);

    $mov = new Movimientos();
    $descripcion = "Editó el registro de historial ID $id";
    if ($nomArticulo && $numHab) {
        $descripcion = "Editó el registro de historial de \"$nomArticulo\" en habitación \"$numHab\"";
    }

    $mov->registrar(
        'historial_articulos',
        'editar',
        $descripcion,
        $id
    );

    header('Content-Type: application/json');
    echo json_encode(['exito' => true]);
    exit;
}

public function eliminarHistorial() {

    verificarRol(['admin']);

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit();
    }

    $id = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT);

    if ($id === false || $id < 1) {
        http_response_code(400);
        echo json_encode(['error' => 'ID inválido']);
        return;
    }

    $historial = new HistorialArticulos();
    $entry = $historial->obtenerPorId($id);
    $numHab = '';
    $nomArticulo = '';

    if ($entry) {
        $inventario = new Inventario();
        $item = $inventario->obtenerPorId($entry['inventario_id']);
        if ($item) {
            $numHab = $inventario->obtenerNumeroHabitacion($item['habitacion_id']);
            $nomArticulo = $inventario->obtenerNombreArticulo($item['articulo_id']);
        }
    }

    $descripcion = "Eliminó el registro de historial ID $id";
    if ($nomArticulo && $numHab) {
        $descripcion = "Eliminó un registro del historial de \"$nomArticulo\" en habitación \"$numHab\"";
    }

    $historial->eliminar($id);

    $mov = new Movimientos();
    $mov->registrar(
        'historial_articulos',
        'eliminar',
        $descripcion,
        $id
    );

    header('Content-Type: application/json');
    echo json_encode(['exito' => true]);
    exit;
}
}
