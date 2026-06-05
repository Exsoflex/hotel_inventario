<?php

require_once __DIR__ . "/../models/revision.php";
require_once __DIR__ . '/../vendor/autoload.php'; 
use PhpOffice\PhpSpreadsheet\Spreadsheet; 
use PhpOffice\PhpSpreadsheet\Writer\Xlsx; 
use PhpOffice\PhpSpreadsheet\Style\Fill; 
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class RevisionController {

    public function index() {

        $revision = new Revision();

        $faltantes = $revision->obtenerFaltantes();

        require_once __DIR__ . "/../views/revision/index.php";

    }


public function exportar() {

    $revision = new Revision();

    $faltantes = $revision->obtenerFaltantes();

    // ==============================
    // FILTROS
    // ==============================

    $buscar =
        $_GET['buscar'] ?? '';

    $estado =
        $_GET['estado'] ?? '';

    $tipo =
        $_GET['tipo'] ?? '';

    $faltantesFiltrados = [];

    foreach($faltantes as $f){

        $estaCompleta =
            $f['faltantes'] <= 0;

        $estadoHabitacion =
            $estaCompleta
            ? 'completa'
            : 'faltante';

        $coincideBuscar =

            empty($buscar)

            ||

            stripos(
                $f['numero'],
                $buscar
            ) !== false

            ||

            stripos(
                $f['articulo'],
                $buscar
            ) !== false;

        $coincideEstado =

            empty($estado)

            ||

            $estadoHabitacion === $estado;

        $coincideTipo =

            empty($tipo)

            ||

            strtolower($f['tipo']) === strtolower($tipo);

        if(
            $coincideBuscar &&
            $coincideEstado &&
            $coincideTipo
        ){

            $faltantesFiltrados[] = $f;

        }
    }

    $faltantes = $faltantesFiltrados;

    // ==============================
    // AGRUPAR HABITACIONES
    // ==============================

    $habitacionesAgrupadas = [];

    foreach($faltantes as $f){

        $numero = $f['numero'];

        if(!isset($habitacionesAgrupadas[$numero])){

            $habitacionesAgrupadas[$numero] = [

                'numero' => $f['numero'],
                'tipo' => $f['tipo'],
                'items' => []

            ];
        }

        $habitacionesAgrupadas[$numero]['items'][] = $f;
    }

    ksort($habitacionesAgrupadas);

    // ==============================
    // EXCEL
    // ==============================

    $spreadsheet = new Spreadsheet();

    $sheet =
        $spreadsheet->getActiveSheet();

    $sheet->setCellValue(
        'A1',
        'REPORTE DE REVISIÓN'
    );

    $sheet->mergeCells('A1:F1');

    date_default_timezone_set(
        'America/Matamoros'
    );

    $sheet->setCellValue(
        'A2',
        'Fecha de exportación: '
        . date('d/m/Y - h:i:s A')
    );

    $sheet->mergeCells('A2:F2');

    $fila = 4;

    foreach($habitacionesAgrupadas as $hab){

        $faltantesHabitacion =
            array_filter(
                $hab['items'],
                fn($item) =>
                    $item['faltantes'] > 0
            );

        $estaCompleta =
            count($faltantesHabitacion) == 0;

        // ======================
        // TITULO HABITACION
        // ======================

        $sheet->setCellValue(
            'A' . $fila,
            'HABITACIÓN '
            . $hab['numero']
            . ' - '
            . strtoupper($hab['tipo'])
        );

        $sheet->mergeCells(
            'A' . $fila .
            ':F' . $fila
        );

        $sheet->getStyle(
            'A' . $fila .
            ':F' . $fila
        )->applyFromArray([

            'font' => [
                'bold' => true,
                'size' => 14
            ],

            'fill' => [
                'fillType' =>
                    Fill::FILL_SOLID,

                'startColor' => [
                    'rgb' => 'B6D7A8'
                ]
            ],

            'alignment' => [
                'horizontal' =>
                    Alignment::HORIZONTAL_CENTER
            ]
        ]);

        $fila++;

        // ======================
        // ENCABEZADOS
        // ======================

        $sheet->setCellValue(
            'A' . $fila,
            'Artículo'
        );

        $sheet->setCellValue(
            'B' . $fila,
            'Actual'
        );

        $sheet->setCellValue(
            'C' . $fila,
            'Base'
        );

        $sheet->setCellValue(
            'D' . $fila,
            'Faltantes'
        );

        $sheet->setCellValue(
            'E' . $fila,
            'Estado'
        );

        $sheet->setCellValue(
            'F' . $fila,
            'Tipo'
        );

        $sheet->getStyle(
            'A' . $fila .
            ':F' . $fila
        )->applyFromArray([

            'font' => [
                'bold' => true
            ],

            'fill' => [
                'fillType' =>
                    Fill::FILL_SOLID,

                'startColor' => [
                    'rgb' => 'D9EAD3'
                ]
            ]
        ]);

        $fila++;

        // ======================
        // ITEMS
        // ======================

        foreach($hab['items'] as $item){

            $sheet->setCellValue(
                'A' . $fila,
                $item['articulo']
            );

            $sheet->setCellValue(
                'B' . $fila,
                $item['cantidad_actual']
            );

            $sheet->setCellValue(
                'C' . $fila,
                $item['cantidad_base']
            );

            $sheet->setCellValue(
                'D' . $fila,
                $item['faltantes']
            );

            $sheet->setCellValue(
                'E' . $fila,
                $item['faltantes'] > 0
                    ? 'Faltante'
                    : 'Completo'
            );

            $sheet->setCellValue(
                'F' . $fila,
                $hab['tipo']
            );

            $fila++;
        }

        $fila += 2;
    }

    // ==============================
    // ESTILOS GENERALES
    // ==============================

    foreach(range('A', 'F') as $columna){

        $sheet->getColumnDimension(
            $columna
        )->setAutoSize(true);
    }

    $sheet->getStyle(
        'A1:F' . ($fila - 1)
    )->applyFromArray([

        'borders' => [
            'allBorders' => [

                'borderStyle' =>
                    \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN

            ]
        ]
    ]);
    
    // ==============================
    // EXPORTAR
    // ==============================

    $writer = new Xlsx($spreadsheet);

    header(
        'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    );

    header(
        'Content-Disposition: attachment;filename="revision.xlsx"'
    );

    header(
        'Cache-Control: max-age=0'
    );

    $writer->save('php://output');

    exit;
}

}