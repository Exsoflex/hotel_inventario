<?php
/** @var array<int, int> $pisos */
/** @var int $piso */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="icon" type="image/png" href="/hotel_inventario/assets/img/HLH_logo.png?">
    <script> window.pisoActual = <?= (int)$piso ?>; </script>

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
<br>
<div class="paginacion-pisos">

<?php foreach($pisos as $p): ?>

    <a
        href="index.php?modulo=revision&piso=<?= $p ?>&buscar=<?= urlencode($_GET['buscar'] ?? '') ?>"
        class="<?= $p == $piso ? 'activo' : '' ?>"
    >
        Piso <?= $p ?>
    </a>

<?php endforeach; ?>

</div>

<br><br>

<!-- ====================== --> 
<div class="revision-grid" id="revisionGrid">
<!-- Aquí JS va a pintar las habitaciones -->
</div>

<div id="noResultsRevision" class="no-results-message hidden">
    <p>No se encontraron registros para los filtros seleccionados.</p>
</div>

</div>
<?php require_once __DIR__ . "/../layout/footer.php"; ?>

<!-- /////////////////////////////////////////////////////// -->
<script src="/hotel_inventario/assets/js/revision/index.js"></script>
</body>
</html>
