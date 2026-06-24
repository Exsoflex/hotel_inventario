<?php

require_once __DIR__ . "/../models/revision.php";
require_once __DIR__ . '/../vendor/autoload.php'; 
use PhpOffice\PhpSpreadsheet\Spreadsheet; 
use PhpOffice\PhpSpreadsheet\Writer\Xlsx; 
use PhpOffice\PhpSpreadsheet\Style\Fill; 
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class RevisionController {

private function agruparPorHabitacion($faltantes) {

    $agrupadas = [];

    foreach ($faltantes as $f) {

        $num = $f['numero'];

        if (!isset($agrupadas[$num])) {

            $agrupadas[$num] = [
                'numero' => $f['numero'],
                'piso' => $f['piso'],
                'tipo' => $f['tipo'],
                'items' => []
            ];
        }

        $agrupadas[$num]['items'][] = $f;
    }

    return array_values($agrupadas);
}

public function index() {

    $revision = new Revision();

    $piso = isset($_GET['piso'])
        ? (int)$_GET['piso']
        : 1;

    $buscar = trim($_GET['buscar'] ?? '');

    // vista normal
    $pisos = $revision->obtenerPisos();

    require_once __DIR__ . "/../views/revision/index.php";
}

public function ajax() {

    $revision = new Revision();

    $piso = isset($_GET['piso'])
        ? (int)$_GET['piso']
        : 1;

    $buscar = trim($_GET['buscar'] ?? '');

    $estado = $_GET['estado'] ?? '';
    $tipo = $_GET['tipo'] ?? '';

    if ($buscar !== '') {
        $piso = null;
    }

    $faltantes = $revision->obtenerFaltantes($piso, $buscar, $estado, $tipo);

    header('Content-Type: application/json');

    echo json_encode($this->agruparPorHabitacion($faltantes));
    exit;
}


    public function exportar() {

        $revision = new Revision();

        // ==============================
        // FILTROS
        // ==============================

        $buscar =
            $_GET['buscar'] ?? '';

        $estado =
            $_GET['estado'] ?? '';

        $tipo =
            $_GET['tipo'] ?? '';

        $faltantes = $revision->obtenerFaltantes(
            null,
            trim($buscar),
            $estado,
            $tipo
        );

        
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


        // ==============================
        // FILTRAR
        // ==============================

    $habitacionesFiltradas = [];

    foreach($habitacionesAgrupadas as $numero => $hab){

        $tieneFaltantes = false;
        $tieneSobrantes = false;

        foreach($hab['items'] as $item){

            if($item['faltantes'] > 0){
                $tieneFaltantes = true;
            }
            if($item['sobrantes'] > 0){
                $tieneSobrantes = true;
            }
        }

        if($tieneFaltantes){
            $estadoHabitacion = 'faltante';
        }elseif($tieneSobrantes){
            $estadoHabitacion = 'sobrante';
        }else{
            $estadoHabitacion = 'completa';

        }

        // aquí siguen los filtros...

        $coincideBuscar = empty($buscar);

    if(!$coincideBuscar){

        if(stripos($hab['numero'], $buscar) !== false){

            $coincideBuscar = true;

        }else{

            foreach($hab['items'] as $item){

                if(stripos($item['articulo'], $buscar) !== false){

                    $coincideBuscar = true;
                    break;

                }

            }

        }

    }

        $coincideEstado =

        empty($estado)

        ||

        $estadoHabitacion === $estado;

    $coincideTipo =

        empty($tipo)

        ||

        strtolower($hab['tipo']) === strtolower($tipo);

    if(
        $coincideBuscar &&
        $coincideEstado &&
        $coincideTipo
    ){

        $habitacionesFiltradas[$numero] = $hab;

    }

    }

    $habitacionesAgrupadas = $habitacionesFiltradas;

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

    $tieneFaltantes = false;
    $tieneSobrantes = false;

    foreach($hab['items'] as $item){

        if($item['faltantes'] > 0){

            $tieneFaltantes = true;

        }

        if($item['sobrantes'] > 0){

            $tieneSobrantes = true;

        }

    }

    if($tieneFaltantes){

        $estadoTexto = 'Con faltantes';

    }elseif($tieneSobrantes){

        $estadoTexto = 'Con sobrantes';

    }else{

        $estadoTexto = 'Completa';

    }

            // ======================
            // TITULO HABITACION
            // ======================
            $sheet->setCellValue(

                'A'.$fila,

                'HABITACIÓN '
                .$hab['numero']
                .' - '
                .strtoupper($hab['tipo'])
                .' - '
                .$estadoTexto

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
                'Diferencia'
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

    if($item['faltantes'] > 0){

        $estadoArticulo = 'Faltante';

    }elseif($item['sobrantes'] > 0){

        $estadoArticulo = 'Sobrante';

    }else{

        $estadoArticulo = 'Completo';

    }

    if($item['faltantes'] > 0){

        $diferencia = '-' . $item['faltantes'];

    }elseif($item['sobrantes'] > 0){

        $diferencia = '+' . $item['sobrantes'];

    }else{

        $diferencia = 0;

    }


                $sheet->setCellValue(
                    'A' . $fila,
                    $item['articulo']
                );

                $sheet->setCellValue(
                    'E' . $fila,
                    $estadoArticulo
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
                    $diferencia
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
