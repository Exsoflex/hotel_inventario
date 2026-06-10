<?php

require_once __DIR__ . "/../models/dashboard.php";
require_once __DIR__ . '/../vendor/autoload.php'; 
use PhpOffice\PhpSpreadsheet\Spreadsheet; 
use PhpOffice\PhpSpreadsheet\Writer\Xlsx; 
use PhpOffice\PhpSpreadsheet\Style\Fill; 
use PhpOffice\PhpSpreadsheet\Style\Alignment;

use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Title;

class DashboardController {

    public function index() {

        $dashboard = new Dashboard();
        $habitaciones = $dashboard->obtenerResumen();
        $estadisticas = $dashboard->obtenerEstadisticasArticulos();
        $faltantesPorPiso = $dashboard->obtenerFaltantesPorPiso();
        $estadisticasPisos = $dashboard->obtenerEstadisticasPisos();

        require_once __DIR__ . "/../views/dashboard/index.php";

    }

public function exportar() {

    $dashboard = new Dashboard();
    $estadisticas      = $dashboard->obtenerEstadisticasArticulos();
    $faltantesPorPiso  = $dashboard->obtenerFaltantesPorPiso();
    $habitaciones      = $dashboard->obtenerResumen();
    $estadisticasPisos = $dashboard->obtenerEstadisticasPisos();

    $buscar = trim($_GET['buscar'] ?? '');
    if ($buscar !== '') {
        $habitaciones = array_filter($habitaciones, function($h) use ($buscar) {
            $texto = strtolower($buscar);
            return str_contains(strtolower($h['numero']), $texto)
                || str_contains(strtolower($h['tipo']), $texto);
        });
    }

    $porPiso = [];
    foreach ($habitaciones as $h) {
        $porPiso[$h['piso']][] = $h;
    }
    ksort($porPiso);

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    date_default_timezone_set('America/Matamoros');

    // ============================================================
    // ENCABEZADO
    // ============================================================
    $sheet->setCellValue('A1', 'REPORTE DASHBOARD');
    $sheet->mergeCells('A1:G1');
    $sheet->setCellValue('A2', 'Fecha de exportación: ' . date('d/m/Y - h:i:s A'));
    $sheet->mergeCells('A2:G2');

    $sheet->getStyle('A1:G1')->applyFromArray([
        'font'      => ['bold' => true, 'size' => 16],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    ]);
    $sheet->getStyle('A2:G2')->applyFromArray([
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    ]);

    // ============================================================
    // TABLA PRINCIPAL POR PISO (columnas A-D)
    // ============================================================
    $fila = 4;
    foreach ($porPiso as $piso => $habitacionesPiso) {

        $sheet->setCellValue('A' . $fila, 'PISO ' . $piso);
        $sheet->mergeCells('A' . $fila . ':D' . $fila);
        $sheet->getStyle('A' . $fila . ':D' . $fila)->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'B6D7A8']],
        ]);
        $fila++;

        $sheet->setCellValue('A' . $fila, 'Habitación');
        $sheet->setCellValue('B' . $fila, 'Tipo');
        $sheet->setCellValue('C' . $fila, 'Artículos faltantes');
        $sheet->setCellValue('D' . $fila, 'Cantidad faltante');
        $sheet->getStyle('A' . $fila . ':D' . $fila)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9EAD3']],
        ]);
        $fila++;

        foreach ($habitacionesPiso as $h) {
            $articulosFaltantes = [];
            if (!empty($h['articulos_faltantes'])) {
                if (is_array($h['articulos_faltantes'])) {
                    foreach ($h['articulos_faltantes'] as $faltante) {
                        $articulosFaltantes[] = is_array($faltante) && isset($faltante['articulo'])
                            ? $faltante['articulo']
                            : $faltante;
                    }
                } else {
                    $articulosFaltantes[] = $h['articulos_faltantes'];
                }
            }

            $sheet->setCellValue('A' . $fila, $h['numero']);
            $sheet->setCellValue('B' . $fila, $h['tipo']);
            $sheet->setCellValue('C' . $fila, empty($articulosFaltantes) ? 'Completo ✓' : implode(', ', $articulosFaltantes));
            $sheet->setCellValue('D' . $fila, $h['total_faltantes']);
            $fila++;
        }
        $fila += 2;
    }

    // Bordes tabla principal
    $sheet->getStyle('A1:D' . ($fila - 1))->applyFromArray([
        'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
    ]);
    $sheet->getColumnDimension('C')->setWidth(45);
    $sheet->getStyle('C')->getAlignment()->setWrapText(true);
    foreach (['A', 'B', 'D'] as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // ============================================================
    // RESUMEN Y HABTIACIONES CRÍTICAS (columnas F-G)
    // ============================================================
    $completas    = count(array_filter($habitaciones, fn($h) => $h['total_base'] > 0 && $h['total_faltantes'] == 0));
    $conFaltantes = count(array_filter($habitaciones, fn($h) => $h['total_faltantes'] > 0));
    $sinBase      = count(array_filter($habitaciones, fn($h) => $h['total_base'] == 0));

    $sheet->setCellValue('F4', 'RESUMEN');
    $sheet->mergeCells('F4:G4');
    $sheet->getStyle('F4:G4')->applyFromArray([
        'font' => ['bold' => true, 'size' => 14],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'B6D7A8']],
    ]);

    $sheet->setCellValue('F5', 'Total habitaciones'); $sheet->setCellValue('G5', count($habitaciones));
    $sheet->setCellValue('F6', 'Completas');          $sheet->setCellValue('G6', $completas);
    $sheet->setCellValue('F7', 'Con faltantes');      $sheet->setCellValue('G7', $conFaltantes);
    $sheet->setCellValue('F8', 'Sin inventario base'); $sheet->setCellValue('G8', $sinBase);

    $criticas = array_filter($habitaciones, fn($h) => $h['total_faltantes'] > 0);
    usort($criticas, fn($a, $b) => $b['total_faltantes'] - $a['total_faltantes']);
    $criticas = array_slice($criticas, 0, 5);

    $sheet->setCellValue('F10', 'HABITACIONES CRÍTICAS');
    $sheet->mergeCells('F10:G10');
    $sheet->getStyle('F10:G10')->applyFromArray([
        'font' => ['bold' => true, 'size' => 14],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9EAD3']],
    ]);

    $filaCritica = 11;
    foreach ($criticas as $c) {
        $sheet->setCellValue('F' . $filaCritica, 'Habitación ' . $c['numero']);
        $sheet->setCellValue('G' . $filaCritica, $c['total_faltantes'] . ' faltantes');
        $filaCritica++;
    }

    foreach (['F', 'G'] as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // ============================================================
    // DATOS GRÁFICA 1: ESTADO INVENTARIO (col I-J, filas 2-5)
    // Separado en su propia zona para no mezclar con artículos
    // ============================================================
    $sheet->setCellValue('I1', 'Estado');
    $sheet->setCellValue('J1', 'Cantidad');
    $sheet->setCellValue('I2', 'Completas');      $sheet->setCellValue('J2', $completas);
    $sheet->setCellValue('I3', 'Con faltantes');  $sheet->setCellValue('J3', $conFaltantes);
    $sheet->setCellValue('I4', 'Sin base');       $sheet->setCellValue('J4', $sinBase);

    $labelsEstado = [new DataSeriesValues('String', "'Worksheet'!\$I\$2:\$I\$4", null, 3)];
    $valuesEstado = [new DataSeriesValues('Number', "'Worksheet'!\$J\$2:\$J\$4", null, 3)];

    $seriesEstado = new DataSeries(
        DataSeries::TYPE_DONUTCHART, null,
        range(0, 0), [], $labelsEstado, $valuesEstado
    );

    $chart1 = new Chart(
        'graficaEstado',
        new Title('Estado del inventario'),
        new Legend(Legend::POSITION_RIGHT),
        new PlotArea(null, [$seriesEstado])
    );
    $chart1->setTopLeftPosition('I6');
    $chart1->setBottomRightPosition('N20');
    $sheet->addChart($chart1);

    // ============================================================
    // DATOS GRÁFICA 2: ARTÍCULOS MÁS FALTANTES (col I-J, filas 22+)
    // ============================================================
    $filaArt = 22;
    $sheet->setCellValue('I' . $filaArt, 'Artículo');
    $sheet->setCellValue('J' . $filaArt, 'Faltantes');
    $filaArt++;

    $inicioArt = $filaArt;
    foreach ($estadisticas as $e) {
        $sheet->setCellValue('I' . $filaArt, $e['articulo']);
        $sheet->setCellValue('J' . $filaArt, $e['total_faltantes']);
        $filaArt++;
    }
    $finArt = $filaArt - 1;

    $labelsArt = [new DataSeriesValues('String', "'Worksheet'!\$I\$" . $inicioArt . ":\$I\$" . $finArt, null, count($estadisticas))];
    $valuesArt = [new DataSeriesValues('Number', "'Worksheet'!\$J\$" . $inicioArt . ":\$J\$" . $finArt, null, count($estadisticas))];

    $seriesArt = new DataSeries(
        DataSeries::TYPE_BARCHART, DataSeries::GROUPING_CLUSTERED,
        range(0, 0), [], $labelsArt, $valuesArt
    );

    $chart2 = new Chart(
        'graficaArticulos',
        new Title('Artículos más faltantes'),
        new Legend(Legend::POSITION_BOTTOM),
        new PlotArea(null, [$seriesArt])
    );
    $chart2->setTopLeftPosition('I' . ($finArt + 2));
    $chart2->setBottomRightPosition('N' . ($finArt + 18));
    $sheet->addChart($chart2);

    // ============================================================
    // DATOS GRÁFICA 3: FALTANTES POR PISO (col L-M)
    // ============================================================
    $sheet->setCellValue('L1', 'Piso');
    $sheet->setCellValue('M1', 'Faltantes');
    $filaPiso = 2;
    foreach ($faltantesPorPiso as $p) {
        $sheet->setCellValue('L' . $filaPiso, 'Piso ' . $p['piso']);
        $sheet->setCellValue('M' . $filaPiso, $p['total_faltantes']);
        $filaPiso++;
    }
    $finPiso = $filaPiso - 1;

    $labelsPiso = [new DataSeriesValues('String', "'Worksheet'!\$L\$2:\$L\$" . $finPiso, null, count($faltantesPorPiso))];
    $valuesPiso = [new DataSeriesValues('Number', "'Worksheet'!\$M\$2:\$M\$" . $finPiso, null, count($faltantesPorPiso))];

    $seriesPiso = new DataSeries(
        DataSeries::TYPE_BARCHART, DataSeries::GROUPING_CLUSTERED,
        range(0, 0), [], $labelsPiso, $valuesPiso
    );

    $chart3 = new Chart(
        'graficaPisos',
        new Title('Faltantes por piso'),
        new Legend(Legend::POSITION_BOTTOM),
        new PlotArea(null, [$seriesPiso])
    );
    $chart3->setTopLeftPosition('O6');
    $chart3->setBottomRightPosition('U20');
    $sheet->addChart($chart3);

    // ============================================================
    // DATOS GRÁFICA 4: ESTADÍSTICAS POR PISO (col L-M, filas 22+)
    // ============================================================
    $filaEP = 22;
    $sheet->setCellValue('L' . $filaEP, 'Piso');
    $sheet->setCellValue('M' . $filaEP, 'Completas');
    $sheet->setCellValue('N' . $filaEP, 'Con faltantes');
    $filaEP++;

    $inicioEP = $filaEP;
    foreach ($estadisticasPisos as $ep) {
        $sheet->setCellValue('L' . $filaEP, 'Piso ' . $ep['piso']);
        $sheet->setCellValue('M' . $filaEP, $ep['habitaciones_completas'] ?? 0);
        $sheet->setCellValue('N' . $filaEP, $ep['habitaciones_con_faltantes'] ?? 0);
        $filaEP++;
    }
    $finEP = $filaEP - 1;

   $labelsEP = [
    new DataSeriesValues(
        'String',
        "'Worksheet'!\$L\$" . $inicioEP . ":\$L\$" . $finEP,
        null,
        count($estadisticasPisos)
    )
];

$seriesLabelsEP = [

    new DataSeriesValues(
        'String',
        "'Worksheet'!\$M\$22",
        null,
        1
    ),

    new DataSeriesValues(
        'String',
        "'Worksheet'!\$N\$22",
        null,
        1
    )
];

$valuesEP = [

    new DataSeriesValues(
        'Number',
        "'Worksheet'!\$M\$" . $inicioEP . ":\$M\$" . $finEP,
        null,
        count($estadisticasPisos)
    ),

    new DataSeriesValues(
        'Number',
        "'Worksheet'!\$N\$" . $inicioEP . ":\$N\$" . $finEP,
        null,
        count($estadisticasPisos)
    )
];

$seriesEP = new DataSeries(

    DataSeries::TYPE_BARCHART,
    DataSeries::GROUPING_CLUSTERED,

    range(0, count($valuesEP) - 1),

    $seriesLabelsEP,

    $labelsEP,

    $valuesEP
);

    $chart4 = new Chart(
        'graficaEstadisticasPisos',
        new Title('Estado por piso'),
        new Legend(Legend::POSITION_BOTTOM),
        new PlotArea(null, [$seriesEP])
    );
    $chart4->setTopLeftPosition('O' . ($finEP + 2));
    $chart4->setBottomRightPosition('U' . ($finEP + 18));
    $sheet->addChart($chart4);

    $sheet->getStyle('I1:N100')->applyFromArray([

    'font' => [
        'size' => 9
    ],

    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => [
            'rgb' => 'F5F5F5'
        ]
    ],

    'borders' => [
        'allBorders' => [
            'borderStyle' =>
                \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
            'color' => [
                'rgb' => 'DDDDDD'
            ]
        ]
    ]
]);

    // ============================================================
    // HEADERS Y DESCARGA
    // ============================================================
    for ($i = 5; $i < $fila; $i++) {
        $sheet->getRowDimension($i)->setRowHeight(-1);
    }


    $writer = new Xlsx($spreadsheet);
    $writer->setIncludeCharts(true);

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="dashboard.xlsx"');
    header('Cache-Control: max-age=0');

    $writer->save('php://output');
    exit;
}

}