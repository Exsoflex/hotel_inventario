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
        value="<?= htmlspecialchars($_GET['buscar'] ?? '') ?>"
    >
       <!-- Los hidden inputs solo sirven para pasar los filtros al form de agregar/editar -->
    <input type="hidden" id="hidden_buscar"        value="<?= htmlspecialchars($_GET['buscar'] ?? '') ?>">
    <input type="hidden" id="hidden_estado_filtro" value="<?= htmlspecialchars($_GET['estado'] ?? '') ?>">

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
                <option value=""          <?= ($_GET['estado'] ?? '') === ''            ? 'selected' : '' ?>>Todos</option>
                <option value="bueno"     <?= ($_GET['estado'] ?? '') === 'bueno'       ? 'selected' : '' ?>>Bueno</option>
                <option value="dañado"    <?= ($_GET['estado'] ?? '') === 'dañado'      ? 'selected' : '' ?>>Dañado</option>
                <option value="en_reparacion" <?= ($_GET['estado'] ?? '') === 'en_reparacion' ? 'selected' : '' ?>>En reparación</option>
                <option value="perdido"   <?= ($_GET['estado'] ?? '') === 'perdido'     ? 'selected' : '' ?>>Perdido</option>
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
        </div>

        <div class="inventario-grid">
            <?php foreach($items as $i): ?>
                <div class="inventario-card"
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
                <a href="index.php?modulo=inventario&accion=editar&id=<?= $i['id'] ?>&buscar=<?= urlencode($_GET['buscar'] ?? '') ?>&estado=<?= urlencode($_GET['estado'] ?? '') ?>">
                    Editar
                </a>

                <!-- Eliminar -->
                <a 
                    href="#"
                    class="btn-eliminar"
                    data-url="index.php?modulo=inventario&accion=eliminar&id=<?= $i['id'] ?>&buscar=<?= urlencode($_GET['buscar'] ?? '') ?>&estado=<?= urlencode($_GET['estado'] ?? '') ?>"
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
        <input type="hidden" name="buscar"        id="hidden_buscar"       value="<?= htmlspecialchars($_GET['buscar'] ?? '') ?>">
        <input type="hidden" name="estado_filtro" id="hidden_estado_filtro" value="<?= htmlspecialchars($_GET['estado'] ?? '') ?>">

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
            class="btn-cancelar"
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

// Buscador — filtra en pantalla Y actualiza el hidden
const buscador        = document.getElementById('buscador');
const hiddenBuscar    = document.getElementById('hidden_buscar');
const hiddenEstado    = document.getElementById('hidden_estado_filtro');
const filtroEstado    = document.getElementById('filtroEstado');
const filtrosArticulo = document.querySelectorAll('.filtro-articulo');

buscador.addEventListener('keyup', function () {
    hiddenBuscar.value = this.value;
    filtrarInventario();
});

filtroEstado.addEventListener('change', function () {
    hiddenEstado.value = this.value;
    filtrarInventario();
});

filtrosArticulo.forEach(check => {
    check.addEventListener('change', filtrarInventario);
});

document.addEventListener('DOMContentLoaded', filtrarInventario);

function filtrarInventario(){

    let buscador =
        document.getElementById('buscador');

    let filtroEstado =
        document.getElementById('filtroEstado');

    let filtroArticulo =
        document.getElementById('filtroArticulo');

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

    });

}
/*
filtroEstado.addEventListener(
    'change',
    filtrarInventario
);
*/
filtrosArticulo.forEach(check => {

    check.addEventListener(
        'change',
        filtrarInventario
    );

});

buscador.addEventListener('keyup', filtrarInventario);

document.addEventListener('DOMContentLoaded', function(){
        filtrarInventario();

});

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
            'index.php?modulo=inventario'
            + '&buscar='  + encodeURIComponent(hiddenBuscar.value)
            + '&estado='  + encodeURIComponent(hiddenEstado.value);
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

        const url = this.dataset.url;

        const inventario = this.dataset.inventario;

        mensajeEliminar.textContent =
            `¿Seguro que deseas eliminar el artículo "${inventario}" del inventario?`;

        btnConfirmarEliminar.href = url;

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

    filtrarInventario();

});

btnLimpiar.addEventListener('click', function(){

    filtrosArticulo.forEach(check => {

        check.checked = false;

    });

    filtrarInventario();

});
</script>

<script>
function exportarExcel(){

    let texto =
        document.getElementById('buscador')
        .value;

    let estado =
        document.getElementById('filtroEstado')
        .value;

    let articulosSeleccionados =
        Array.from(document.querySelectorAll('.filtro-articulo'))
        .filter(c => c.checked)
        .map(c => c.value);

    let url =
        'index.php?modulo=inventario&accion=exportar';

    url += '&buscar=' + encodeURIComponent(texto);

    url += '&estado=' + encodeURIComponent(estado);

    url += '&articulos=' +
        encodeURIComponent(
            articulosSeleccionados.join(',')
        );

        window.location.href = url;
}
</script>


</body>
</html>