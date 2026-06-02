<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/styles.css">

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

    <div class="filtro-wrapper">
<!-- ------------------------------------------------------- -->
        <button
            type="button"
            id="btnFiltros"
            class="btn-filtros"
        >
            Filtros
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

            </select>
            <button
                style="margin-top: 10px"
                type="button"
                id="btnLimpiarFiltros"
                class="btn-mini"
            >
                Limpiar filtros
            </button>
    </div>
</div>
</div>

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

<p>
    Habitaciones con faltantes:
    <?= count(array_filter(
        $habitacionesAgrupadas,
        fn($hab) => count(array_filter(
            $hab['items'],
            fn($item) => $item['faltantes'] > 0
        )) > 0
    )) ?>
</p>

<br>

<div class="revision-grid">

<?php foreach($habitacionesAgrupadas as $hab): ?>

    <?php

    $faltantesHabitacion = array_filter(
        $hab['items'],
        fn($item) => $item['faltantes'] > 0
    );

    $estaCompleta = count($faltantesHabitacion) == 0;
    ?>

    <div class="habitacion-card"
    data-estado="<?= $estaCompleta ? 'completa' : 'faltante' ?>"
    >

        <!--///////////////////////////// HEADER CARD ////////////////////////////-->
        <div class="habitacion-card-header">
            <div>
                <h2>Habitación <?= $hab['numero'] ?></h2>
                <p><?= $hab['tipo'] ?></p>
                <a href="index.php?modulo=inventario&buscar=<?= $hab['numero'] ?>" class="btn-ver-inventario">Ver inventario</a>
            </div>

            <div class="<?= $estaCompleta ? 'estado-ok' : 'estado-faltante' ?>">
                <?= $estaCompleta ? 'Completa ✓' : 'Con faltantes' ?>
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


</div>
<?php require_once __DIR__ . "/../layout/footer.php"; ?>

<!-- /////////////////////////////////////////////////////// -->

<script>
const buscador = document.getElementById('buscador');

const filtroEstado =
    document.getElementById('filtroEstado');

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

        let coincideTexto =
            contenido.includes(texto);

        let coincideEstado =
            estadoSeleccionado === '' ||
            estado === estadoSeleccionado;

        let mostrar =
            coincideTexto &&
            coincideEstado;

        card.style.display =
            mostrar ? '' : 'none';

    });
}

buscador.addEventListener('keyup', filtrar);

filtroEstado.addEventListener(
    'change',
    filtrar
);

document.addEventListener('DOMContentLoaded', function() {

    if (buscador.value.trim() !== '') {
        filtrar();
    }

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

    filtrar();

});



</script>

</body>
</html>