<?php
$buscar = $buscar ?? ($_GET['buscar'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="icon" type="image/png" href="/hotel_inventario/assets/img/HLH_logo.png?">
    <title>Inventario base</title>
</head>

<body>

<?php require_once __DIR__ . "/../layout/header.php"; ?>

<!-- /////////////////////////////////////////////////////// -->

<div class="page-header">
    <h1>Lista de inventario base</h1>
    <p>Administración de inventario base del hotel</p>
</div>

<!-- /////////////////////////////////////////////////////// -->

<div class="container">

    <div class="inventario-topbar">

        <input 
        type="text" 
        id="buscador" 
        placeholder="Buscar inventario base..."
        value="<?= htmlspecialchars($buscar) ?>"
        >

        <a
        href="index.php?modulo=inventario_base&accion=exportar&buscar=<?= urlencode($buscar) ?>"
        data-base-url="index.php?modulo=inventario_base&accion=exportar"
        id="btnExportar"
        class="menu-btn"
        >
            <i data-lucide="download"></i>
        </a>

        <?php if(
        in_array(
            $_SESSION['usuario']['rol'],
            ['admin', 'supervisor']
        )
        ): ?>
        <button 
        class="btn-agregar"
        onclick="abrirModal()"
        >
            + Agregar inventario base
        </button>
        <?php endif; ?>

    </div>

    <br>

<!-- /////////////////////////////////////////////////////// -->

    <table>

    <thead>

         <tr>
            <th hidden>ID</th>
            <th>Tipo de habitación</th>
            <th>Articulo</th>
            <th>Cantidad</th>
            <?php if(
            in_array(
                $_SESSION['usuario']['rol'],
                ['admin', 'supervisor']
            )
            ): ?>
            <th>Eliminar</th>
            <th>Editar</th>
            <?php endif; ?>
        </tr>
    
    </thead>
    
    <tbody>

        <?php
        //var_dump($habitaciones); // Agrega esta línea para verificar el contenido de $habitaciones
        foreach($inventarios_base as $i): ?>

            <tr id="inventario_base-<?= $i['id'] ?>">
                <td hidden><?= $i['id'] ?></td>
                <td><?= $i['tipo_habitacion'] ?></td>
                <td><?= $i['nombre'] ?></td>
                <td><?= $i['cantidad'] ?></td>
                <?php if(
                in_array(
                    $_SESSION['usuario']['rol'],
                    ['admin', 'supervisor']
                )
                ): ?>
                <td>
                    <a 
                    href="#"
                    class="btn-eliminar"
                    data-base-url="index.php?modulo=inventario_base&accion=eliminar&id=<?= $i['id'] ?>"
                    data-inventario_base="<?= $i['nombre'] ?>"
                    >
                    Eliminar
                    </a>
                </td>
                <td>
                    <a
                    class="btn-editar"
                    data-base-url="index.php?modulo=inventario_base&accion=editar&id=<?= $i['id'] ?>"
                    href="index.php?modulo=inventario_base&accion=editar&id=<?= $i['id'] ?>&buscar=<?= urlencode($buscar) ?>#inventario_baseFormulario"
                    > 
                    Editar</a>
                </td>
                <?php endif; ?>    
            </tr>

        <?php endforeach; ?>

    </tbody>



    </table>

</div>
<?php require_once __DIR__ . "/../layout/footer.php"; ?>

<!-- Modal Inventario Base -->

<div 
class="modal-overlay <?= isset($inventario_baseEditar) ? 'active' : '' ?>" 
id="modalInventarioBase"
>

    <div class="modal">

        <div class="modal-header">

            <h2>
                <?= isset($inventario_baseEditar) 
                    ? 'Editar inventario base' 
                    : 'Agregar inventario base' ?>
            </h2>

            <button onclick="cerrarModal()">
                ✕
            </button>

        </div>

        <form 
        id="inventario_baseFormulario" 
        action="index.php?modulo=inventario_base&accion=<?= isset($inventario_baseEditar) ? 'editar' : 'agregar' ?>" 
        method="POST"
        >

            <input
            type="hidden"
            name="buscar"
            id="form_buscar"
            value="<?= htmlspecialchars($buscar) ?>"
            >

        <?php if (isset($errorFormulario)): ?>
            <div class="alerta-error">
                ⚠ <?= htmlspecialchars($errorFormulario) ?>
            </div>
        <?php endif; ?>

            <input 
            type="hidden" 
            name="id" 
            value="<?= $inventario_baseEditar['id'] ?? '' ?>"
            >

            <label>Tipo de habitación</label>

            <select name="tipo" required>

                <option value="">Seleccione un tipo</option>

                <option 
                    value="sencilla"
                    <?= isset($inventario_baseEditar) &&
                    $inventario_baseEditar['tipo_habitacion'] == 'sencilla'
                    ? 'selected' : '' ?>
                >
                    Sencilla
                </option>

                <option 
                    value="doble"
                    <?= isset($inventario_baseEditar) &&
                    $inventario_baseEditar['tipo_habitacion'] == 'doble'
                    ? 'selected' : '' ?>
                >
                    Doble
                </option>

                <option 
                    value="superior"
                    <?= isset($inventario_baseEditar) &&
                    $inventario_baseEditar['tipo_habitacion'] == 'superior'
                    ? 'selected' : '' ?>
                >
                    Superior
                </option>

            </select>

            <label>Artículo</label>

            <select name="articulo_id" required>

                <option value="">Seleccione un artículo</option>

                <?php foreach($articulos as $a): ?>

                    <option 
                        value="<?= $a['id'] ?>"
                        <?= isset($inventario_baseEditar) &&
                        $inventario_baseEditar['articulo_id'] == $a['id']
                        ? 'selected' : '' ?>
                    >
                        <?= $a['nombre'] ?>
                    </option>

                <?php endforeach; ?>

            </select>

            <label>Cantidad</label>

            <input 
            type="number" 
            name="cantidad" 
            required
            min="0"
            value="<?= $inventario_baseEditar['cantidad'] ?? '' ?>"
            >

            <div class="modal-buttons">

                <button type="submit">
                    <?= isset($inventario_baseEditar) 
                        ? 'Guardar cambios' 
                        : 'Agregar inventario base' ?>
                </button>

                <button 
                type="reset" 
                onclick="cerrarModal()"
                >
                    Cancelar
                </button>

            </div>

        </form>

    </div>

</div>


<!-- /////////////////////////////////////////////////////// -->

    <div class="modal-overlay" id="modalEliminar">

    <div class="modal-confirmacion">

        <div class="modal-icono">
            ⚠
        </div>

        <h2>Eliminar articulo de inventario base</h2>

        <p id="mensajeEliminar">
            ¿Seguro que deseas eliminar este articulo?
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

const buscador = document.getElementById('buscador');
const formBuscar = document.getElementById('form_buscar');
const btnExportar = document.getElementById('btnExportar');

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

    formBuscar.value = buscador.value.trim();

    document.querySelectorAll('.btn-editar').forEach(link => {
        link.href = crearUrlConBusqueda(link.dataset.baseUrl) + '#inventario_baseFormulario';
    });

    if (btnExportar) {
        btnExportar.href = crearUrlConBusqueda(btnExportar.dataset.baseUrl);
    }

    window.history.replaceState(
        {},
        '',
        crearUrlConBusqueda('index.php?modulo=inventario_base')
    );
}

function filtrarTabla() {

    let texto = buscador.value.toLowerCase();
    let filas = document.querySelectorAll("table tbody tr");

    filas.forEach(function(fila){

        let contenido = fila.textContent.toLowerCase();

        if(contenido.includes(texto)){
            fila.style.display = "";
        } else {
            fila.style.display = "none";
        }
    });
}

function actualizarBusqueda() {

    sincronizarBusqueda();
    filtrarTabla();
}

buscador.addEventListener('keyup', actualizarBusqueda);
document.addEventListener('DOMContentLoaded', actualizarBusqueda);

</script>

<!-- Modal Inventario Base -->

<script>

function abrirModal(){

    document
    .getElementById('modalInventarioBase')
    .classList
    .add('active');

    document.body.style.overflow = 'hidden';
}

function cerrarModal(){

    <?php if(
        isset($inventario_baseEditar)
        || isset($errorFormulario)
    ): ?>

        window.location.href =
            crearUrlConBusqueda('index.php?modulo=inventario_base');

    <?php else: ?>

        document
        .getElementById('modalInventarioBase')
        .classList
        .remove('active');

        document.body.style.overflow = 'auto';

    <?php endif; ?>
}

<?php if(
    isset($inventario_baseEditar)
    || isset($errorFormulario)
): ?>

abrirModal();

<?php endif; ?>

</script>

<!-- Modal Eliminar -->
<script>

const botonesEliminar = document.querySelectorAll('.btn-eliminar');
const modalEliminar = document.getElementById('modalEliminar');
const mensajeEliminar = document.getElementById('mensajeEliminar');
const btnConfirmarEliminar = document.getElementById('btnConfirmarEliminar');

botonesEliminar.forEach(boton => {

    boton.addEventListener('click', function(e){

        e.preventDefault();

        const inventario_base = this.dataset.inventario_base;

        mensajeEliminar.textContent =
            `¿Seguro que deseas eliminar el artículo "${inventario_base}" del inventario base?`;

        btnConfirmarEliminar.href = crearUrlConBusqueda(this.dataset.baseUrl);

        modalEliminar.classList.add('active');
    });
});

function cerrarModalEliminar(){

    modalEliminar.classList.remove('active');
}

</script>

</body>
</html>
