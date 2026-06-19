<?php
/** @var array<int, array<string, mixed>> $faltantes */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="icon" type="image/png" href="/hotel_inventario/assets/img/HLH_logo.png?">

    <title>Revision</title>
</head>

<body>

<?php require_once __DIR__ . "/../layout/header.php"; ?>

<!-- /////////////////////////////////////////////////////// -->

<div class="page-header">
<h1>Revision de habitaciones</h1>
<p>Verifica que articulos deben tener las habitaciones</p>
</div>

<!-- /////////////////////////////////////////////////////// -->

<div class="container">

<div class="inventario-topbar">

<input type="text" id="buscador" placeholder="Buscar..."
value="<?= htmlspecialchars($_GET['buscar'] ?? '') ?>">

    <button 
    class="btn-filtros"
    onclick="exportarExcelRevision()">
        <i data-lucide="download"></i>
    </button>


    <div class="filtro-wrapper">
<!-- ------------------------------------------------------- -->
        <button
            type="button"
            id="btnFiltros"
            class="btn-filtros"
        >
        <i data-lucide="filter"></i>
        </button>
<!-- ------------------------------------------------------- -->
        <div
            id="menuFiltros"
            class="menu-filtros"
        >

            <label>Estado</label>

            <select id="filtroEstado">

            <option value="">
                Todas
            </option>

            <option value="completa">
                Completas
            </option>

            <option value="faltante">
                Con faltantes
            </option>

            <option value="sobrante">
                Con sobrantes
            </option>

            </select>
 <!-- ====================== -->   
            <label style="margin-top:10px">
                Tipo de habitación
            </label>

            <select id="filtroTipo">

                <option value="">
                    Todas
                </option>

                <option value="sencilla">
                    Sencilla
                </option>

                <option value="doble">
                    Doble
                </option>

                <option value="superior">
                    Superior
                </option>

            </select>
<!-- ====================== -->    
            <button
                style="margin-top: 10px"
                type="button"
                id="btnLimpiarFiltros"
                class="btn-subfiltro"
            >
                Limpiar filtros
            </button>
    </div>
</div>
</div>
<!-- ====================== --> 

<?php

$habitacionesAgrupadas = [];

foreach ($faltantes as $f) {

    $numeroHabitacion = $f['numero'];

    if (!isset($habitacionesAgrupadas[$numeroHabitacion])) {

        $habitacionesAgrupadas[$numeroHabitacion] = [

            'numero' => $f['numero'],
            'tipo' => $f['tipo'],
            'items' => []

        ];
    }

    $habitacionesAgrupadas[$numeroHabitacion]['items'][] = $f;
}
?>

<br><br>

<div class="revision-grid">

<?php foreach($habitacionesAgrupadas as $hab): ?>


<!-- Estado de la habitacion -->
    <?php

$tieneFaltantes = false;
$tieneSobrantes = false;

foreach ($hab['items'] as $item) {

    if ($item['faltantes'] > 0) {
        $tieneFaltantes = true;
    }

    if ($item['sobrantes'] > 0) {
        $tieneSobrantes = true;
    }
}

if ($tieneFaltantes) {

    $estadoHabitacion = 'faltante';
    $textoEstado = 'Con faltantes';
    $claseEstado = 'estado-faltante';

} elseif ($tieneSobrantes) {

    $estadoHabitacion = 'sobrante';
    $textoEstado = 'Con sobrantes';
    $claseEstado = 'estado-sobrante';

} else {

    $estadoHabitacion = 'completa';
    $textoEstado = 'Completa ✓';
    $claseEstado = 'estado-ok';

}
    ?>

    <div class="habitacion-card"
    data-estado="<?= $estadoHabitacion ?>"
    data-tipo="<?= strtolower($hab['tipo']) ?>"
    >

        <!--///////////////////////////// HEADER CARD ////////////////////////////-->
        <div class="habitacion-card-header">
            <div>
                <h2>Habitación <?= $hab['numero'] ?></h2>
                <p><?= $hab['tipo'] ?></p>
                <a href="index.php?modulo=inventario&buscar=<?= $hab['numero'] ?>" class="btn-ver-inventario">Ver inventario</a>
            </div>

                <div class="<?= $claseEstado ?>">
                    <?= $textoEstado ?>
                </div>

        </div>

        <!--///////////////////////////// ITEMS ///////////////////////////////-->

        <div class="habitacion-items">
            <?php foreach($hab['items'] as $item): ?>
                <div class="item-row">
                    <div class="item-info">
                        <strong><?= $item['articulo'] ?></strong>
                        <span>

                            <?= $item['cantidad_actual'] ?>
                            /
                            <?= $item['cantidad_base'] ?>

                        </span>
                    </div>
                    <div>

        <?php if($item['faltantes'] > 0): ?>

            <span class="badge-faltante">
                Faltan <?= $item['faltantes'] ?>
            </span>

        <?php elseif($item['sobrantes'] > 0): ?>

            <span class="badge-sobrante">
                Sobran <?= $item['sobrantes'] ?>
            </span>

        <?php else: ?>

            <span class="badge-ok">
                Completo
            </span>

        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endforeach; ?>

</div>

<div id="noResultsRevision" class="no-results-message hidden">
    <p>No se encontraron registros para los filtros seleccionados.</p>
</div>

</div>
<?php require_once __DIR__ . "/../layout/footer.php"; ?>

<!-- /////////////////////////////////////////////////////// -->

<script>
const buscador = document.getElementById('buscador');

const filtroEstado =
    document.getElementById('filtroEstado');

const filtroTipo =
    document.getElementById('filtroTipo');

function filtrar() {

    let texto =
        buscador.value.toLowerCase();

    let estadoSeleccionado =
        filtroEstado.value;

    let cards =
        document.querySelectorAll('.habitacion-card');

    cards.forEach(function(card){

        let contenido =
            card.textContent.toLowerCase();

        let estado =
            card.dataset.estado;

        let tipoSeleccionado =
            filtroTipo.value;

        let tipo =
            card.dataset.tipo;

        let coincideTexto =
            contenido.includes(texto);

        let coincideEstado =
            estadoSeleccionado === '' ||
            estado === estadoSeleccionado;

        let coincideTipo =
            tipoSeleccionado === '' ||
            tipo === tipoSeleccionado;

        let mostrar =
            coincideTexto &&
            coincideEstado &&
            coincideTipo;

        card.style.display =
            mostrar ? '' : 'none';

    });

    const noResultsMessage = document.getElementById('noResultsRevision');
    if (noResultsMessage) {
        const hayResultados = Array.from(cards).some(card => card.style.display !== 'none');
        noResultsMessage.classList.toggle('hidden', hayResultados);
    }
}

buscador.addEventListener('keyup', filtrar);

filtroEstado.addEventListener(
    'change',
    filtrar
);

filtroTipo.addEventListener(
    'change',
    filtrar
);

document.addEventListener('DOMContentLoaded', function() {

    filtrar();

});

// -------------------------------------------------------
</script>

<script>

const btnFiltros =
    document.getElementById('btnFiltros');

const menuFiltros =
    document.getElementById('menuFiltros');

btnFiltros.addEventListener('click', function(e){

    e.stopPropagation();

    menuFiltros.classList.toggle('active');

});

document.addEventListener('click', function(e){

    if(
        !menuFiltros.contains(e.target) &&
        !btnFiltros.contains(e.target)
    ){

        menuFiltros.classList.remove('active');

    }

});

const btnLimpiarFiltros =
    document.getElementById('btnLimpiarFiltros');

btnLimpiarFiltros.addEventListener('click', function(){

    buscador.value = '';

    filtroEstado.value = '';

    filtroTipo.value = '';

    filtrar();

});
</script>

<script>
function exportarExcelRevision(){

    let texto =
        document.getElementById('buscador')
        .value;

    let estado =
        document.getElementById('filtroEstado')
        .value;

    let tipo =
        document.getElementById('filtroTipo')
        .value;

    let url =
        'index.php?modulo=revision&accion=exportar';

    url += '&buscar=' +
        encodeURIComponent(texto);

    url += '&estado=' +
        encodeURIComponent(estado);

    url += '&tipo=' +
        encodeURIComponent(tipo);

    window.location.href = url;
}
</script>

</body>
</html>
