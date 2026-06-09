<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <title>Dashboard</title>
    <link rel="icon" type="image/png" href="/hotel_inventario/assets/img/HLH_logo.png">
</head>

<body class="dashboard-body">

<?php require_once __DIR__ . "/../layout/header.php"; ?>

<div class="dashboard-header">
    <h1>Inicio</h1>
    <p>Estado general del inventario del hotel</p>
</div>

<div class="dashboard-wrapper">

    <!-- ===== PANEL IZQUIERDO: tabla de habitaciones ===== -->
    <div class="dashboard-panel-izquierdo">

        <?php
        $porPiso = [];
        foreach ($habitaciones as $h) {
            $porPiso[$h['piso']][] = $h;
        }
        ksort($porPiso);
        ?>

        <div class="dashboard-buscador">
            <input 
            type="text" 
            id="buscador" 
            placeholder="Buscar habitación..."
            autocomplete="off"
            >

        <a
            href="index.php?modulo=dashboard&accion=exportar&buscar=<?= urlencode($_GET['buscar'] ?? '') ?>"
            class="menu-btn"
            >
            <i data-lucide="download"></i>
        </a>
        </div>

<br>

    <?php foreach ($porPiso as $piso => $habitacionesPiso): ?>

    <div class="bloque-piso">

        <h2>Piso <?= $piso ?></h2>

        <table>

                <thead>
                    <tr>
                        <th>Habitación</th>
                        <th>Tipo</th>
                        <th>Artículos faltantes</th>
                        <th>Detalles</th>
                    </tr>
                </thead>

                <tbody>

                    <?php foreach ($habitacionesPiso as $h): ?>

                        <tr id="habitacion-<?= $h['habitacion_id'] ?>">

                            <td><?= $h['numero'] ?></td>

                            <td><?= $h['tipo'] ?></td>

                           <td>

                            <?php if ($h['total_faltantes'] > 0): ?>

                                <span style="color:red;">
                                    <?= $h['articulos_faltantes'] ?>
                                </span>

                            <?php elseif ($h['total_base'] == 0): ?>

                                <span style="color:gray;">
                                    Sin inventario base definido
                                </span>

                            <?php else: ?>

                                <span style="color:green;">
                                    Completo ✓
                                </span>

                            <?php endif; ?>

                        </td>

                            <td>
                                <a href="index.php?modulo=revision&buscar=<?= $h['numero'] ?>">
                                    🔍 Ver detalles
                                </a>
                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

            <br>
        </div>
        <?php endforeach; ?>

    </div>

    <!-- ===== PANEL DERECHO: estadísticas ===== -->
    <div class="dashboard-panel-derecho">

        <h2>Resumen</h2>
<br>

    <?php

            $total = count($habitaciones);

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

    ?>

        <p>🏨 Total de habitaciones: <strong><?= $total ?></strong></p>
        <br>
        <p style="color:green;">✓ Completas: <strong><?= $completas ?></strong></p>
        <br>
        <p style="color:red;">✗ Con faltantes: <strong><?= $conFaltantes ?></strong></p>
        <br>
        <p style="color:gray;">⚠ Sin inventario base: <strong><?= $sinBase ?></strong></p>

<br><br>

    <h2>Artículos más faltantes</h2>
<div class="contenedor-grafica">
    <canvas id="graficaArticulos"></canvas>
</div>

    </canvas>

    
<br><br>


        <h2>Habitaciones críticas</h2>
        <br>

        <?php
        // Habitaciones con más faltantes primero
        $criticas = array_filter(
            $habitaciones,
            fn($h) =>
                $h['total_faltantes'] > 0 &&
                $h['total_faltantes'] > 0
        );

        usort(
            $criticas,
            fn($a, $b) =>
                $b['total_faltantes'] - $a['total_faltantes']
        );

        $criticas = array_slice($criticas, 0, 5);

        ?>

        <?php if (empty($criticas)): ?>

            <p style="color:green;">Ninguna habitación con faltantes ✓</p>

        <?php else: ?>

            <?php foreach ($criticas as $c): ?>

                <p>
                    <strong>Hab <?= $c['numero'] ?></strong>
                    — <?= $c['total_faltantes'] ?> faltante(s)
                    <a href="index.php?modulo=revision&buscar=<?= $c['numero'] ?>">
                        🔍
                    </a>
                </p>
                <br>

            <?php endforeach; ?>

        <?php endif; ?>

    </div>

</div>

<?php require_once __DIR__ . "/../layout/footer.php"; ?>

<script>

const buscador = document.getElementById('buscador');

function filtrar() {

    let texto = buscador.value.toLowerCase();

    // recorrer cada piso
    document.querySelectorAll(".bloque-piso").forEach(function(bloque) {

        let filas = bloque.querySelectorAll("tbody tr");

        let hayVisibles = false;

        filas.forEach(function(fila) {

            let contenido = fila.textContent.toLowerCase();

            let coincide = contenido.includes(texto);

            fila.style.display = coincide ? "" : "none";

            if (coincide) {
                hayVisibles = true;
            }

        });

        // ocultar TODO el piso si no hay resultados
        bloque.style.display = hayVisibles ? "" : "none";

    });

}

buscador.addEventListener('input', filtrar);
</script>

<script>

const estadisticas = <?= json_encode($estadisticas) ?>;

console.log(estadisticas);

const labels = estadisticas.map(e => e.articulo);

const datos = estadisticas.map(
    e => Number(e.total_faltantes)
);

const ctx = document.getElementById('graficaArticulos');

new Chart(ctx, {

    type: 'bar',
    data: {

        labels: labels,
        datasets: [{

            label: 'Cantidad faltante',
            data: datos,
            borderWidth: 1

        }]
    },

options: {

    indexAxis: 'y',

    responsive: true,
    maintainAspectRatio: false,

    scales: {

        x: {
            beginAtZero: true
        }
    }
}
}
);
</script>



</body>
</html>