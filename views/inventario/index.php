<?php
/** @var array<int, array<string, mixed>> $inventarios */
/** @var array<int, array<string, mixed>> $habitaciones */
/** @var array<int, array<string, mixed>> $articulos */
/** @var array<int, int> $pisos */
/** @var int $piso */
/** @var array<string, mixed> $filtros */
/** @var array<string, mixed>|null $inventarioEditar */
$filtros = $filtros ?? [
    'buscar' => $_GET['buscar'] ?? '',
    'estado' => $_GET['estado'] ?? '',
    'articulos' => $_GET['articulos'] ?? '',
    'piso' => $_GET['piso'] ?? 1,
];

$articulosFiltrados = array_filter(
    array_map('strtolower', array_map('trim', explode(',', $filtros['articulos'])))
);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="icon" type="image/png" href="/hotel_inventario/assets/img/HLH_logo.png?">
    <title>Inventario</title>
</head>

<body>

<?php require_once __DIR__ . "/../layout/header.php"; ?>

<!-- /////////////////////////////////////////////////////// -->

<div class="page-header">
    <h1>Lista de inventario</h1>
    <p>Administración de inventario del hotel</p>
</div>

<!-- /////////////////////////////////////////////////////// -->

<?php if(isset($_GET['mensaje'])): ?>

    <div class="alerta-exito">

        <?php if($_GET['mensaje'] == 'agregado'): ?>
            Inventario agregado correctamente ✓
        <?php endif; ?>

        <?php if($_GET['mensaje'] == 'editado'): ?>
            Inventario editado correctamente ✓
        <?php endif; ?>

        <?php if($_GET['mensaje'] == 'eliminado'): ?>
            Inventario eliminado correctamente ✓
        <?php endif; ?>

    </div>

<?php endif; ?>

<!-- /////////////////////////////////////////////////////// -->

<div
    class="container"
    id="inventarioModulo"
    data-piso-actual="<?= (int)$piso ?>"
    data-puede-gestionar="<?= in_array($_SESSION['usuario']['rol'], ['admin', 'supervisor']) ? '1' : '0' ?>"
    data-abrir-modal-inicial="<?= (isset($inventarioEditar) || isset($errorFormulario)) ? '1' : '0' ?>"
    data-editando="<?= isset($inventarioEditar) ? '1' : '0' ?>"
>

<div class="inventario-topbar">

    <input 
        type="text" 
        id="buscador" 
        placeholder="Buscar..."
        value="<?= htmlspecialchars($filtros['buscar']) ?>"
    >

    <button 
    class="btn-filtros"
    onclick="exportarExcel()">
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
                <option value=""          <?= $filtros['estado'] === ''            ? 'selected' : '' ?>>Todos</option>
                <option value="bueno"     <?= $filtros['estado'] === 'bueno'       ? 'selected' : '' ?>>Bueno</option>
                <option value="dañado"    <?= $filtros['estado'] === 'dañado'      ? 'selected' : '' ?>>Dañado</option>
                <option value="en_reparacion" <?= $filtros['estado'] === 'en_reparacion' ? 'selected' : '' ?>>En reparación</option>
                <option value="perdido"   <?= $filtros['estado'] === 'perdido'     ? 'selected' : '' ?>>Perdido</option>
            </select>

<!-- ------------------------------------------------------- -->
        <label style="margin-top: 7px">Artículos</label>

        <button
            type="button"
            id="btnArticulos"
            class="btn-subfiltro"
        >
            Seleccionar artículos
        </button>
                <!-- ==== -->

        <div class="acciones-articulos">

            <button
                type="button"
                id="seleccionarTodos"
                class="btn-subfiltro"
            >
                Todos
            </button>

            <button
                type="button"
                id="limpiarArticulos"
                class="btn-subfiltro"
            >
                Limpiar
            </button>

        </div>
                <!-- ==== -->
        <div
            id="listaArticulos"
            class="lista-articulos"
        >

            <div class="filtro-articulos">

            <?php foreach($articulos as $articulo): ?>

            <label class="check-articulo">

                <input
                    type="checkbox"
                    class="filtro-articulo"
                    value="<?= strtolower($articulo['nombre']) ?>"
                    <?= in_array(strtolower($articulo['nombre']), $articulosFiltrados) ? 'checked' : '' ?>
                >

                <?= $articulo['nombre'] ?>

            </label>

            <?php endforeach; ?>

            </div>
        </div>
<!-- ------------------------------------------------------- -->
        </div>
    </div>

    <?php if(
    in_array(
        $_SESSION['usuario']['rol'],
        ['admin', 'supervisor']
    )
    ): ?>
    <button class="btn-agregar" onclick="abrirModal()">
        + Agregar inventario
    </button>
    <?php endif; ?>
</div>

<br>

<!-- /////////////////////////////////////////////////////// -->
<div class="paginacion-pisos <?= empty($filtros['buscar']) ? '' : 'hidden' ?>">

<?php foreach($pisos as $p): ?>

    <a
        href="index.php?modulo=inventario&piso=<?= $p ?>"
        class="<?= $p == $piso ? 'activo' : '' ?>"
    >
        Piso <?= $p ?>
    </a>

<?php endforeach; ?>

</div>

<br><br>
<div id="inventarioContenedor">
<!-- JS pinta las habitaciones y cards de inventario -->
</div>

<div id="noResultsInventario" class="no-results-message hidden">
    <p>No se encontraron registros para los filtros seleccionados.</p>
</div>

<!-- /////////////////////////////////////////////////////// -->

</div>
<?php require_once __DIR__ . "/../layout/footer.php"; ?>

<!-- ////////////// MODAL INVENTARIO //////////////////////////////// -->       
       
<div class="modal-overlay" id="modalInventario">

    <div class="modal">

        <div class="modal-header">

            <h2>

                <?= isset($inventarioEditar)
                    ? 'Editar inventario'
                    : 'Agregar inventario'
                ?>

            </h2>

            <button onclick="cerrarModal()">✕</button>
        
</div>

    <form
    id="inventarioFormulario"
    action="index.php?modulo=inventario&accion=<?= isset($inventarioEditar) ? 'editar' : 'agregar' ?>"
    method="POST">

        <!-- Preservar búsqueda -->
        <input type="hidden" name="buscar" id="form_buscar" value="<?= htmlspecialchars($filtros['buscar']) ?>">
        <input type="hidden" name="estado_filtro" id="form_estado_filtro" value="<?= htmlspecialchars($filtros['estado']) ?>">
        <input type="hidden" name="articulos_filtro" id="form_articulos_filtro" value="<?= htmlspecialchars($filtros['articulos']) ?>">
        <input type="hidden" name="piso" id="form_piso" value="<?= (int)$piso ?>">

        <!-- resto del form igual -->

        <?php if(isset($errorFormulario)): ?>

        <div class="alerta-error">
            ⚠ <?= htmlspecialchars($errorFormulario) ?>
        </div>

        <?php endif; ?>

            <input
            type="hidden"
            name="id"
            value="<?= $inventarioEditar['id'] ?? '' ?>"
            >

            <label>Habitación</label>
            <select name="habitacion_id" required>
                <option value="">Seleccione una habitación</option>
                <?php foreach($habitaciones as $h): ?>

                    <option
                    value="<?= $h['id'] ?>"

                    <?= isset($inventarioEditar)
                        && $inventarioEditar['habitacion_id'] == $h['id']
                        ? 'selected'
                        : ''
                    ?>
                    >
                        Habitación <?= $h['numero'] ?>
                    </option>

                <?php endforeach; ?>
            </select>
            <label>Artículo</label>
            <select name="articulo_id" id="articulo_id" required>

                <option value="">Seleccione un artículo</option>
                <?php foreach($articulos as $a): ?>

                <option
                value="<?= $a['id'] ?>"
                data-codigo="<?= $a['usa_codigo_barras'] ?>"
                <?= isset($inventarioEditar)
                    && $inventarioEditar['articulo_id'] == $a['id']
                    ? 'selected'
                    : ''
                ?>
                >
                    <?= $a['nombre'] ?>
                </option>

                <?php endforeach; ?>

            </select>

            <label>Cantidad</label>

            <input
            type="number"
            name="cantidad"
            min="0"
            required
            value="<?= $inventarioEditar['cantidad'] ?? '' ?>"
            >

            <div id="contenedorCodigo">

                <label>Código</label>

                <input
                type="text"
                name="codigo_barras"
                value="<?= $inventarioEditar['codigo_barras'] ?? '' ?>"
                >

            </div>

            <label>Estado</label>

        <select name="estado" required>
        <option value="">Seleccione un estado</option>

            <option value="bueno"
            <?= isset($inventarioEditar)
                && $inventarioEditar['estado'] === 'bueno'
                ? 'selected'
                : ''
            ?>>
                Bueno
            </option>

            <option value="dañado"
            <?= isset($inventarioEditar)
                && $inventarioEditar['estado'] === 'dañado'
                ? 'selected'
                : ''
            ?>>
                Dañado
            </option>

            <option value="en_reparacion"
            <?= isset($inventarioEditar)
                && $inventarioEditar['estado'] === 'en_reparacion'
                ? 'selected'
                : ''
            ?>>
                En reparación
            </option>

            <option value="perdido"
            <?= isset($inventarioEditar)
                && $inventarioEditar['estado'] === 'perdido'
                ? 'selected'
                : ''
            ?>>
                Perdido
            </option>
        </select>

            <label>Comentarios</label>

            <input
            type="text"
            name="comentarios"
            value="<?= $inventarioEditar['comentarios'] ?? '' ?>"
            >

            <div class="modal-buttons">

                <button type="submit">

                    Guardar

                </button>

                <button
                type="reset"
                onclick="cerrarModal()">

                    Cancelar

                </button>
            </div>
        </form>
    </div>
</div>

    </main>

</div>

<!-- /////////////////////////////////////////////////////// -->

    <div class="modal-overlay" id="modalEliminar">

    <div class="modal-confirmacion">

        <div class="modal-icono">
            ⚠
        </div>

        <h2>Eliminar articulo de inventario</h2>

        <p id="mensajeEliminar">
            ¿Seguro que deseas eliminar este articulo del inventario?
        </p>

        <div class="modal-botones">

            <button 
            onclick="cerrarModalEliminar()"
            >
                Cancelar
            </button>

            <a 
            href="#" 
            id="btnConfirmarEliminar"
            class="btn-confirmar"
            >
                Sí, eliminar
            </a>

        </div>

    </div>

</div>

<!-- /////////////////////////////////////////////////////// -->

<script src="/hotel_inventario/assets/js/inventario/index.js"></script>

</body>
</html>
