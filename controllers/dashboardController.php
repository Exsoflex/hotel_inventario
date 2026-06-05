<?php

require_once __DIR__ . "/../models/dashboard.php";
require_once __DIR__ . '/../vendor/autoload.php'; 
use PhpOffice\PhpSpreadsheet\Spreadsheet; 
use PhpOffice\PhpSpreadsheet\Writer\Xlsx; 
use PhpOffice\PhpSpreadsheet\Style\Fill; 
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class DashboardController {

    public function index() {

        $dashboard = new Dashboard();
        $habitaciones = $dashboard->obtenerResumen();

        require_once __DIR__ . "/../views/dashboard/index.php";

    }

public function exportar() {

$dashboard = new Dashboard();

$habitaciones = $dashboard->obtenerResumen();

$buscar = trim($_GET['buscar'] ?? '');

if($buscar !== ''){

    $habitaciones = array_filter(
        $habitaciones,
        function($h) use ($buscar){

            $texto = strtolower($buscar);

            return
                str_contains(
                    strtolower($h['numero']),
                    $texto
                ) ||

                str_contains(
                    strtolower($h['tipo']),
                    $texto
                );
        }
    );
}

$porPiso = [];

foreach($habitaciones as $h){

    $porPiso[$h['piso']][] = $h;

}

ksort($porPiso);

$spreadsheet = new Spreadsheet();

$sheet = $spreadsheet->getActiveSheet();

$sheet->setCellValue(
    'A1',
    'REPORTE DASHBOARD'
);

$sheet->mergeCells('A1:G1');

date_default_timezone_set('America/Matamoros');


$sheet->setCellValue(
    'A2',
    'Fecha de exportación: ' . date('d/m/Y - h:i:s A')
);

$sheet->mergeCells('A2:G2');

$fila = 4;

foreach($porPiso as $piso => $habitacionesPiso){
   $sheet->setCellValue(
    'A' . $fila,
    'PISO ' . $piso
);

$sheet->mergeCells(
    'A' . $fila . ':D' . $fila
); 

$sheet->getStyle(
    'A' . $fila . ':D' . $fila
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

$sheet->setCellValue('A' . $fila, 'Habitación');
$sheet->setCellValue('B' . $fila, 'Tipo');
$sheet->setCellValue('C' . $fila, 'Artículos faltantes');
$sheet->setCellValue('D' . $fila, 'Cantidad faltante');

$sheet->getStyle(
    'A' . $fila . ':D' . $fila
)->applyFromArray([

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

$fila++;

foreach($habitacionesPiso as $h){

$articulosFaltantes = [];

if(!empty($h['articulos_faltantes'])){

    if(is_array($h['articulos_faltantes'])){

        foreach($h['articulos_faltantes'] as $faltante){

            if(
                is_array($faltante) &&
                isset($faltante['articulo'])
            ){

                $articulosFaltantes[] =
                    $faltante['articulo'];

            } else {

                $articulosFaltantes[] =
                    $faltante;

            }
        }

    } else {

        $articulosFaltantes[] =
            $h['articulos_faltantes'];
    }
}

$sheet->setCellValue(
    'A' . $fila,
    $h['numero']
);

$sheet->setCellValue(
    'B' . $fila,
    $h['tipo']
);

$sheet->setCellValue(
    'C' . $fila,
    empty($articulosFaltantes)
        ? 'Completo ✓'
        : implode(", ", $articulosFaltantes)
);

$sheet->setCellValue(
    'D' . $fila,
    $h['total_faltantes']
);

$sheet->getColumnDimension('C')
    ->setWidth(45);

$sheet->getStyle('C:C')
    ->getAlignment()
    ->setWrapText(true);



$fila++;
}
$fila += 2;
}

$completas = count(array_filter(
    $habitaciones,
    fn($h) =>
        $h['total_base'] > 0 &&
        $h['total_faltantes'] == 0
));

$conFaltantes = count(array_filter(
    $habitaciones,
    fn($h) =>
        $h['total_faltantes'] > 0
));

$sinBase = count(array_filter(
    $habitaciones,
    fn($h) =>
        $h['total_base'] == 0
));

$sheet->setCellValue('F4', 'RESUMEN');

$sheet->setCellValue('F5', 'Total habitaciones');
$sheet->setCellValue('G5', count($habitaciones));

$sheet->setCellValue('F6', 'Completas');
$sheet->setCellValue('G6', $completas);

$sheet->setCellValue('F7', 'Con faltantes');
$sheet->setCellValue('G7', $conFaltantes);

$sheet->setCellValue('F8', 'Sin inventario base');
$sheet->setCellValue('G8', $sinBase);

$criticas = array_filter(
    $habitaciones,
    fn($h) =>
        $h['total_faltantes'] <= 9
);

usort(
    $criticas,
    fn($a, $b) =>
        $b['total_faltantes'] - $a['total_faltantes']
);

$criticas = array_slice($criticas, 0, 5);

$sheet->setCellValue(
    'F10',
    'HABITACIONES CRÍTICAS'
);

$filaCritica = 11;

foreach($criticas as $c){

    $sheet->setCellValue(
        'F' . $filaCritica,
        'Habitación ' . $c['numero']
    );

    $sheet->setCellValue(
        'G' . $filaCritica,
        $c['total_faltantes'] . ' faltantes'
    );

    $filaCritica++;
}

foreach(['A','B','D','E','F','G'] as $columna){

    $sheet->getColumnDimension($columna)
        ->setAutoSize(true);
}

$sheet->getStyle(
    'A1:G' . ($fila - 1)
)->applyFromArray([

    'borders' => [
        'allBorders' => [
            'borderStyle' =>
                \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
        ]
    ]
]);

$sheet->getStyle('A1:G1')->applyFromArray([

    'font' => [
        'bold' => true,
        'size' => 16
    ],

    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER
    ]

]);

$sheet->getStyle('A2:G2')->applyFromArray([

    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER
    ]

]);

$sheet->getStyle('F4:G4')->applyFromArray([

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

$sheet->mergeCells('F4:G4');

$sheet->getStyle('F10:G10')->applyFromArray([

    'font' => [
        'bold' => true,
        'size' => 14
    ],

    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => [
            'rgb' => 'D9EAD3'
        ]
    ]

]);

$sheet->mergeCells('F10:G10');

$sheet->getStyle('A:C')->applyFromArray([

    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER
    ]

]);

$writer = new Xlsx($spreadsheet);

header(
    'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
);

header(
    'Content-Disposition: attachment;filename="dashboard.xlsx"'
);

header(
    'Cache-Control: max-age=0'
);

for($i = 5; $i < $fila; $i++){

    $sheet->getRowDimension($i)
        ->setRowHeight(35);

}

$writer->save('php://output');

exit;
}

}