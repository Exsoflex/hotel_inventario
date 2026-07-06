<?php
/** @var array<int, array<string, mixed>> $habitaciones */
/** @var array<int, array<string, mixed>> $estadisticas */
/** @var array<int, array<string, mixed>> $faltantesPorPiso */
/** @var array<int, array<string, mixed>> $estadisticasPisos */
/** @var string $buscar */
$buscar = $buscar ?? ($_GET['buscar'] ?? '');
?>
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
            <div class="buscador-wrapper">
            <input 
                type="text" 
                id="buscador" 
                placeholder="Buscar por habitación o articulo..."
                autocomplete="off"
                value="<?= htmlspecialchars($buscar) ?>"
                >
                <button type="button" id="btnLimpiarBusqueda" class="btn-limpiar-buscador" title="Limpiar búsqueda">
                    <i data-lucide="x"></i>
                </button>
            </div>

            <a
                href="index.php?modulo=dashboard&accion=exportar&buscar=<?= urlencode($buscar) ?>"
                data-base-url="index.php?modulo=dashboard&accion=exportar"
                id="btnExportarDashboard"
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
                        <th>Estado</th>
                        <th>Detalles</th>
                    </tr>
                </thead>

                <tbody>

                    <?php foreach ($habitacionesPiso as $h): ?>

                        <tr id="habitacion-<?= $h['habitacion_id'] ?>">

                            <td><?= $h['numero'] ?></td>

                            <td><?= $h['tipo'] ?></td>

<td>

    <?php if ($h['total_base'] == 0): ?>

        <span style="color:gray;">
            Sin inventario base definido
        </span>

    <?php else: ?>

        <?php if ($h['total_faltantes'] > 0): ?>

            <div style="color:red;">
                <strong>Faltantes:</strong><br>
                <?= $h['articulos_faltantes'] ?>
            </div>

        <?php endif; ?>

        <?php if ($h['total_sobrantes'] > 0): ?>

            <div style="color:orange; margin-top:5px;">
                <strong>Sobrantes:</strong><br>
                <?= $h['articulos_sobrantes'] ?>
            </div>

        <?php endif; ?>

        <?php if ($h['total_faltantes'] == 0 && $h['total_sobrantes'] == 0): ?>

            <span style="color:green;">
                Completo ✓
            </span>

        <?php endif; ?>

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
            fn($h) => $h['estado_inventario'] === 'completo'
        ));

        $conFaltantes = count(array_filter(
            $habitaciones,
            fn($h) => $h['estado_inventario'] === 'incompleto'
        ));

        $conSobrantes = count(array_filter(
            $habitaciones,
            fn($h) => $h['estado_inventario'] === 'sobrante'
        ));

        $sinBase = count(array_filter(
            $habitaciones,
            fn($h) => $h['total_base'] == 0
        ));

        $mixtas = count(array_filter(
            $habitaciones,
            fn($h) => $h['estado_inventario'] === 'mixto'
        ));

    ?>

        <p>🏨 Total de habitaciones: <strong><?= $total ?></strong></p>
        <br>
        <p style="color:green;">✅ Completas: <strong><?= $completas ?></strong></p>
        <br>
        <p style="color:red;">❌ Con faltantes: <strong><?= $conFaltantes ?></strong></p>
        <br>
        <p style="color:orange;">⚠️ Con sobrantes: <strong><?= $conSobrantes ?></strong></p>
        <br>
        <p style="color:blue;">⛔ Mixtas: <strong><?= $mixtas ?></strong></p>
        <br>
        <p style="color:gray;">🚫 Sin inventario base: <strong><?= $sinBase ?></strong></p>


<br><br>

<div class="grafica-card">
    <div class="graficas-fila">
        <!-- DONA -->
        <div class="mini-grafica">

            <h2>Estado del inventario</h2>

            <div class="contenedor-dona">
                <canvas id="graficaEstadoInventario"></canvas>
            </div>

        </div>

        <!-- BARRAS -->
        <div class="mini-grafica">

            <h2>Estado de habitaciones por piso</h2>

            <div class="contenedor-mini-barra">
                <canvas id="graficaHabitacionesPiso"></canvas>
            </div>

        </div>

    </div>

</div>

<br>

<div class="grafica-card">

    <h2>Artículos faltantes por piso</h2>

    <div class="contenedor-grafica">
        <canvas id="graficaFaltantesPiso"></canvas>
    </div>

</div>

<br>

<div class="grafica-card">

    <h2>Artículos más faltantes</h2>

    <div class="contenedor-grafica">
        <canvas id="graficaArticulos"></canvas>
    </div>

</div>

<br><br>


        <h2>Habitaciones críticas</h2>
        <br>

        <?php
        // Habitaciones con más faltantes primero
$criticas = array_filter(
    $habitaciones,
    fn($h) => in_array(
        $h['estado_inventario'],
        ['incompleto', 'mixto']
    )
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
<!-- ---------------------------------------- -->
<?php

$datosInventario = [
    $completas,
    $conFaltantes,
    $conSobrantes,
    $mixtas,
    $sinBase
];

?>

<!-- ---------------------------------------- -->
</div>

<?php require_once __DIR__ . "/../layout/footer.php"; ?>

<script>

const buscador = document.getElementById('buscador');
const btnExportarDashboard = document.getElementById('btnExportarDashboard');
const btnLimpiarBusqueda = document.getElementById('btnLimpiarBusqueda');

function crearUrlConBusqueda(baseUrl) {

    const url = new URL(baseUrl, window.location.href);
    const texto = buscador.value.trim();

    if (texto) {
        url.searchParams.set('buscar', texto);
    } else {
        url.searchParams.delete('buscar');
    }

    return url.pathname + '?' + url.searchParams.toString();
}

function sincronizarBusqueda() {

    if (btnExportarDashboard) {
        btnExportarDashboard.href = crearUrlConBusqueda(btnExportarDashboard.dataset.baseUrl);
    }

    window.history.replaceState(
        {},
        '',
        crearUrlConBusqueda('index.php?modulo=dashboard')
    );
}

function filtrar() {

    sincronizarBusqueda();

    let texto = buscador.value.toLowerCase();

    document.querySelectorAll(".bloque-piso").forEach(function(bloque) {

        let filas = bloque.querySelectorAll("tbody tr");

        let hayVisibles = false;

        let tituloPiso = bloque.querySelector("h2")
            .textContent
            .toLowerCase();

        filas.forEach(function(fila) {

            let contenido = (
                fila.textContent + " " + tituloPiso
            ).toLowerCase();

            let coincide = contenido.includes(texto);

            fila.style.display = coincide ? "" : "none";

            if (coincide) {
                hayVisibles = true;
            }

        });

        bloque.style.display = hayVisibles ? "" : "none";

    });
    
    if (btnLimpiarBusqueda) {
        btnLimpiarBusqueda.classList.toggle('hidden', !buscador.value);
    }
}

buscador.addEventListener('input', filtrar);
document.addEventListener('DOMContentLoaded', function() {
    filtrar();
    if (btnLimpiarBusqueda) {
        btnLimpiarBusqueda.classList.toggle('hidden', !buscador.value);
    }
});

if (btnLimpiarBusqueda) {
    btnLimpiarBusqueda.addEventListener('click', function() {
        buscador.value = '';
        filtrar();
    });
}

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

            backgroundColor: '#fd9b2cb3',

            label: 'Cantidad faltante',
            data: datos,
            borderWidth: 1,
            borderColor: '#d78324'

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

<script>

const datosInventario = 
<?= json_encode($datosInventario) ?>;

const ctxInventario = document.getElementById(
    'graficaEstadoInventario'
);


new Chart(ctxInventario, {

    type: 'doughnut',

    data: {

        labels: [
            'Completas',
            'Con faltantes',
            'Con sobrantes',
            'Mixtas',
            'Sin inventario base'
        ],

        datasets: [{

            barThickness: 30,
            data: datosInventario,

        backgroundColor: [
            '#3399eddd', // completas
            '#ee248ccf', // faltantes
            '#e9b300',   // sobrantes
            '#7b1fa2',   // mixtas
            '#9E9E9E'    // sin base
        ],

            borderWidth: 2

        }]
    },

    options: {

        responsive: true,

        plugins: {

            legend: {
                position: 'bottom'
            }
        }
    }
});

</script>

<script>

const datosHabitaciones =
<?= json_encode($estadisticasPisos) ?>;

const labelsHabitaciones =
    datosHabitaciones.map(
        p => 'Piso ' + p.piso
    );

const habitacionesCompletas =
    datosHabitaciones.map(
        p => Number(
            p.habitaciones_completas
        )
    );

const habitacionesFaltantes =
    datosHabitaciones.map(
        p => Number(
            p.habitaciones_con_faltantes
        )
    );

const habitacionesSobrantes =
    datosHabitaciones.map(
        p => Number(
            p.habitaciones_con_sobrantes
        )
    );

const habitacionesMixtas =
    datosHabitaciones.map(
        p => Number(
            p.habitaciones_mixtas
        )
    );

const ctxHabitaciones =
    document.getElementById(
        'graficaHabitacionesPiso'
    );

new Chart(ctxHabitaciones, {

    type: 'bar',

    data: {

        labels: labelsHabitaciones,

        datasets: [

            {

                label: 'Completas',

                data: habitacionesCompletas,

                backgroundColor: '#1182d3a3',

                borderColor:'#2580ca', // completas

                borderWidth: 1,

                barThickness: 22
            },

            {

                label: 'Con faltantes',

                data: habitacionesFaltantes,

                backgroundColor: '#e11e7999',

                borderColor: '#a21f76',

                borderWidth: 1,

                barThickness: 22
            },

            {
                label: 'Con sobrantes',

                data: habitacionesSobrantes,

                backgroundColor: '#f0be1bc8',

                borderColor: '#d2a214',

                borderWidth: 1,

                barThickness: 22
            },

            {

                label: 'Mixtas',

                data: habitacionesMixtas,

                backgroundColor: '#a836f4b3',

                borderColor: '#ac28c6',

                borderWidth: 1,

                barThickness: 22

            }

        ]
    },

    options: {

        responsive: true,

        maintainAspectRatio: false,

        plugins: {

            legend: {
                position: 'bottom'
            }
        },

        scales: {

            y: {
                beginAtZero: true,
                ticks: {
                    precision: 0
                }
            }
        }
    }
});

</script>

<script>

const datosPisos =
<?= json_encode($faltantesPorPiso) ?>;

const labelsFaltantes =
    datosPisos.map(p => 'Piso ' + p.piso);

const valoresFaltantes =
    datosPisos.map(
        p => Number(p.total_faltantes)
    );

const ctxFaltantes =
    document.getElementById(
        'graficaFaltantesPiso'
    );

new Chart(ctxFaltantes, {

    type: 'bar',

    data: {

        labels: labelsFaltantes,

        datasets: [{

            label: 'Artículos faltantes',

            data: valoresFaltantes,

            borderWidth: 1
        }]
    },

    options: {

        responsive: true,
        maintainAspectRatio: false
    }
});


</script>



</body>
</html>
