<?php
/** @var array<int, array<string, mixed>> $inventarios */
/** @var array<int, array<string, mixed>> $habitaciones */
/** @var array<int, array<string, mixed>> $articulos */
/** @var array<string, string> $filtros */
/** @var array<string, mixed>|null $inventarioEditar */
$filtros = $filtros ?? [
    'buscar' => $_GET['buscar'] ?? '',
    'estado' => $_GET['estado'] ?? '',
    'articulos' => $_GET['articulos'] ?? '',
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

<div class="container">

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

            <?php

            $articulosUnicos = [];

            foreach($inventarios as $item){
                $articulosUnicos[$item['nombre']] = true;
            }

            ksort($articulosUnicos);

            ?>

            <?php foreach(array_keys($articulosUnicos) as $articulo): ?>

            <label class="check-articulo">

                <input
                    type="checkbox"
                    class="filtro-articulo"
                    value="<?= strtolower($articulo) ?>"
                    <?= in_array(strtolower($articulo), $articulosFiltrados) ? 'checked' : '' ?>
                >

                <?= $articulo ?>

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

<?php

$inventarioPorHabitacion = [];

foreach($inventarios as $i){

    $inventarioPorHabitacion[$i['numero']][] = $i;
}

ksort($inventarioPorHabitacion);

?>

<!-- /////////////////////////////////////////////////////// -->

<?php foreach($inventarioPorHabitacion as $numero => $items): ?>

    <div class="habitacion-section">
        <div class="habitacion-section-header">
            <h2>Habitación <?= $numero ?></h2>
            <a href="index.php?modulo=revision&buscar=<?= urlencode($numero) ?>" class="btn-ver-revision">Ver revisión</a>
        </div>

        <div class="inventario-grid">
            <?php foreach($items as $i): ?>
                <div class="inventario-card"
                id="inventario-<?= $i['id'] ?>"
                data-estado="<?= $i['estado'] ?>"
                data-articulo="<?= strtolower($i['nombre'])?>"
                >
                    <div class="inventario-card-header">
                        <div>
                            <h3><?= $i['nombre'] ?></h3>
                        </div>
                        <div class="estado-badge estado-<?= $i['estado'] ?>">
                            <?= ucfirst($i['estado']) ?>
                        </div>
                    </div>
                    <div class="inventario-info">

                    <?php if($i['usa_codigo_barras']): ?>
                        <p>
                            <strong>Código:</strong>
                            <?= $i['codigo_barras'] ?: 'Sin asignar' ?>
                        </p>
                    <?php endif; ?>

                        <p>
                            <strong>Cantidad:</strong>
                            <?= $i['cantidad'] ?>
                        </p>

                        <p>
                            <strong>Comentarios:</strong>
                            <?= $i['comentarios'] ?: 'Sin comentarios' ?>
                        </p>

                    </div>

                    <?php if(
                        in_array(
                            $_SESSION['usuario']['rol'],
                            ['admin', 'supervisor']
                        )
                    ): ?>
                    <div class="inventario-actions">    
                             
                <!-- Editar -->
                <a
                    class="btn-editar"
                    data-base-url="index.php?modulo=inventario&accion=editar&id=<?= $i['id'] ?>"
                    href="index.php?modulo=inventario&accion=editar&id=<?= $i['id'] ?>&buscar=<?= urlencode($filtros['buscar']) ?>&estado=<?= urlencode($filtros['estado']) ?>&articulos=<?= urlencode($filtros['articulos']) ?>"
                >
                    Editar
                </a>

                <!-- Eliminar -->
                <a 
                    href="#"
                    class="btn-eliminar"
                    data-base-url="index.php?modulo=inventario&accion=eliminar&id=<?= $i['id'] ?>"
                    data-inventario="<?= $i['nombre'] ?>"
                >
                    Eliminar
                </a>
                    </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endforeach; ?>

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
            <select name="habitacion_id">
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
            <select name="articulo_id" id="articulo_id">

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

        <select name="estado">
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

<script>

const buscador        = document.getElementById('buscador');
const filtroEstado    = document.getElementById('filtroEstado');
const filtrosArticulo = document.querySelectorAll('.filtro-articulo');
const formBuscar      = document.getElementById('form_buscar');
const formEstado      = document.getElementById('form_estado_filtro');
const formArticulos   = document.getElementById('form_articulos_filtro');

function obtenerFiltrosActuales() {

    const articulosSeleccionados =
        Array.from(filtrosArticulo)
        .filter(c => c.checked)
        .map(c => c.value);

    return {
        buscar: buscador.value.trim(),
        estado: filtroEstado.value,
        articulos: articulosSeleccionados.join(',')
    };
}

function crearUrlConFiltros(baseUrl) {

    const filtros = obtenerFiltrosActuales();
    const url = new URL(baseUrl, window.location.href);

    ['buscar', 'estado', 'articulos'].forEach(nombre => {
        if (filtros[nombre]) {
            url.searchParams.set(nombre, filtros[nombre]);
        } else {
            url.searchParams.delete(nombre);
        }
    });

    return url.pathname + '?' + url.searchParams.toString();
}

function sincronizarFiltros() {

    const filtros = obtenerFiltrosActuales();

    formBuscar.value = filtros.buscar;
    formEstado.value = filtros.estado;
    formArticulos.value = filtros.articulos;

    document.querySelectorAll('.btn-editar').forEach(link => {
        link.href = crearUrlConFiltros(link.dataset.baseUrl);
    });

    const urlActual = crearUrlConFiltros('index.php?modulo=inventario');
    window.history.replaceState({}, '', urlActual);
}

function actualizarFiltro() {

    sincronizarFiltros();
    filtrarInventario();
}

buscador.addEventListener('keyup', actualizarFiltro);
filtroEstado.addEventListener('change', actualizarFiltro);

filtrosArticulo.forEach(check => {
    check.addEventListener('change', actualizarFiltro);
});

document.addEventListener('DOMContentLoaded', actualizarFiltro);

function filtrarInventario(){

    let texto =
        buscador.value.toLowerCase();

    let estadoSeleccionado =
        filtroEstado.value;

    let secciones =
        document.querySelectorAll('.habitacion-section');

    let articulosSeleccionados =
        Array.from(filtrosArticulo)
        .filter(c => c.checked)
        .map(c => c.value);

    let algunaVisibleSection = false;

    secciones.forEach(function(seccion){

        let cards =
            seccion.querySelectorAll('.inventario-card');

        let algunaVisible = false;

    let tituloHabitacion =
        seccion.querySelector('h2')
        .textContent
        .toLowerCase();

    cards.forEach(function(card){

        let contenido =
            (
                card.textContent +
                ' ' +
                tituloHabitacion
            ).toLowerCase();

            let estado =
                card.dataset.estado;

            let articulo =
                card.dataset.articulo;

            let coincideTexto =
                contenido.includes(texto);

            let coincideEstado =
                estadoSeleccionado === '' ||
                estado === estadoSeleccionado;

            let coincideArticulo =
                articulosSeleccionados.length === 0 ||
                articulosSeleccionados.includes(
                    articulo
                );

            let mostrar =

                coincideTexto &&
                coincideEstado &&
                coincideArticulo;

            card.style.display =
                mostrar ? '' : 'none';

            if(mostrar){
                algunaVisible = true;
            }

        });

        seccion.style.display =
            algunaVisible ? '' : 'none';

        if(algunaVisible){
            algunaVisibleSection = true;
        }

    });

    const noResultsMessage =
        document.getElementById('noResultsInventario');

    if(noResultsMessage){
        noResultsMessage.classList.toggle(
            'hidden',
            algunaVisibleSection
        );
    }

}

</script>

<!-- /////////////////////////////////////////////////////// -->
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

</script>
<!--//////////-- Modal Inventario --//////////-->

<script>

function abrirModal(){

    document
    .getElementById('modalInventario')
    .classList
    .add('active');
    document.body.style.overflow = 'hidden';
}

function cerrarModal() {
    <?php if (isset($inventarioEditar)): ?>
        window.location.href =
            crearUrlConFiltros('index.php?modulo=inventario');
    <?php else: ?>
        document.getElementById('modalInventario').classList.remove('active');
        document.body.style.overflow = 'auto';
    <?php endif; ?>
}

<?php if(
    isset($inventarioEditar)
    || isset($errorFormulario)
): ?>

abrirModal();

<?php endif; ?>

/*////////---- Confirmar eliminación ----/////////*/

function confirmarEliminacion(id){

    let confirmar = confirm(
        "¿Seguro que deseas eliminar este registro?"
    );

    if(confirmar){

const textoBusqueda =
    document.getElementById('buscador').value;

const estadoBusqueda =
    document.getElementById('filtroEstado').value;

window.location.href =
    'index.php?modulo=inventario'
    + '&buscar=' + encodeURIComponent(textoBusqueda)
    + '&estado=' + encodeURIComponent(estadoBusqueda);
    }
}

</script>

<!--//////////-- Modal Eliminar --//////////-->
<script>

const botonesEliminar = document.querySelectorAll('.btn-eliminar');
const modalEliminar = document.getElementById('modalEliminar');
const mensajeEliminar = document.getElementById('mensajeEliminar');
const btnConfirmarEliminar = document.getElementById('btnConfirmarEliminar');

botonesEliminar.forEach(boton => {

    boton.addEventListener('click', function(e){

        e.preventDefault();

        const inventario = this.dataset.inventario;

        mensajeEliminar.textContent =
            `¿Seguro que deseas eliminar el artículo "${inventario}" del inventario?`;

        btnConfirmarEliminar.href = crearUrlConFiltros(this.dataset.baseUrl);

        modalEliminar.classList.add('active');
    });
});

function cerrarModalEliminar(){

    modalEliminar.classList.remove('active');
}

</script>

<!--//////////-- Mostrar campo de codigo si el articulo lo amerita --//////////-->

<script>

const selectArticulo =
    document.querySelector('select[name="articulo_id"]');

const contenedorCodigo =
    document.getElementById('contenedorCodigo');

function actualizarCampoCodigo(){

    const opcionSeleccionada =
        selectArticulo.options[selectArticulo.selectedIndex];

    const usaCodigo =
        opcionSeleccionada.dataset.codigo;

    if(usaCodigo == "1"){

        contenedorCodigo.style.display = 'block';

    }else{

        contenedorCodigo.style.display = 'none';

    }
}

selectArticulo.addEventListener(
    'change',
    actualizarCampoCodigo
);

document.addEventListener(
    'DOMContentLoaded',
    actualizarCampoCodigo
);

</script>

<!--//////////-- Mostrar seccion de articulos --//////////-->
<script>
const btnArticulos =
    document.getElementById('btnArticulos');

const listaArticulos =
    document.getElementById('listaArticulos');

btnArticulos.addEventListener('click', function(){

    listaArticulos.classList.toggle('active');

});

// Botones para seleccionar/limpiar todos los articulos //

const btnTodos =
    document.getElementById('seleccionarTodos');

const btnLimpiar =
    document.getElementById('limpiarArticulos');

btnTodos.addEventListener('click', function(){

    filtrosArticulo.forEach(check => {

        check.checked = true;

    });

    actualizarFiltro();

});

btnLimpiar.addEventListener('click', function(){

    filtrosArticulo.forEach(check => {

        check.checked = false;

    });

    if (typeof filtroEstado !== 'undefined') {
        filtroEstado.value = '';
    }

    actualizarFiltro();

});
</script>

<script>
function exportarExcel(){

    window.location.href =
        crearUrlConFiltros('index.php?modulo=inventario&accion=exportar');
}
</script>


</body>
</html>
