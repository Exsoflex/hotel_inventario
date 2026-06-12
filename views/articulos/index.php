<?php
/** @var array<int, array<string, mixed>> $articulos */
/** @var array<string, mixed>|null $articuloEditar */
/** @var string|null $errorFormulario */
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
    <link rel="icon" type="image/png" href="/hotel_inventario/assets/img/HLH_logo.png?">

    <title>Articulos</title>
</head>

<body>

<?php require_once __DIR__ . "/../layout/header.php"; ?>

<!-- /////////////////////////////////////////////////////// -->

<div class="page-header"> 
    <h1>Lista de artículos</h1> 
    <p>Administración de artículos del hotel</p> 
</div>

<!-- /////////////////////////////////////////////////////// -->

<div class="container">

    <div class="inventario-topbar">

        <input 
        type="text" 
        id="buscador" 
        placeholder="Buscar articulos..."
        value="<?= htmlspecialchars($buscar) ?>"
        >

        <a
        href="index.php?modulo=articulos&accion=exportar&buscar=<?= urlencode($buscar) ?>"
        data-base-url="index.php?modulo=articulos&accion=exportar"
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
            + Agregar articulo
        </button>
        <?php endif; ?>
    </div>

    <br>

<!-- /////////////////////////////////////////////////////// -->

    <table>

    <thead>
        <tr>
            <th hidden>ID</th>
            <th>Nombre</th>
            <th>Descripción</th>
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
        //var_dump($articulo); // Agrega esta línea para verificar el contenido de $habitaciones
        foreach($articulos as $a): ?>

            <tr id="articulo-<?= $a['id'] ?>">
                <td hidden><?= $a['id'] ?></td>
                <td><?= $a['nombre'] ?></td>
                <td><?= $a['descripcion'] ?></td>
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
                    data-base-url="index.php?modulo=articulos&accion=eliminar&id=<?= $a['id'] ?>"
                    data-articulo="<?= $a['nombre'] ?>"
                    >
                    Eliminar
                    </a>
                </td>
                <td>
                    <a
                    class="btn-editar"
                    data-base-url="index.php?modulo=articulos&accion=editar&id=<?= $a['id'] ?>"
                    href="index.php?modulo=articulos&accion=editar&id=<?= $a['id'] ?>&buscar=<?= urlencode($buscar) ?>"
                    >                
                    Editar
                    </a>
                </td>  
                <?php endif; ?>    
            </tr>

        <?php endforeach; ?>

    </tbody>
    </table>

</div>
<?php require_once __DIR__ . "/../layout/footer.php"; ?>

<!--//////////-- Modal Articulo --//////////-->

<div class="modal-overlay" id="modalArticulo">

    <div class="modal">

        <div class="modal-header">

            <div>
                <h2>
                    <?= isset($articuloEditar) 
                        ? 'Editar artículo' 
                        : 'Agregar artículo' ?>
                </h2>

                <p>
                    Completa la información del artículo
                </p>
            </div>

            <button onclick="cerrarModal()">
                ✕
            </button>

        </div>

        <form 
        id="articuloFormulario" 
        action="index.php?modulo=articulos&accion=<?= isset($articuloEditar) ? 'editar' : 'agregar' ?>" 
        method="POST">

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
                value="<?= $articuloEditar['id'] ?? '' ?>"
            >

            <label>Nombre del artículo</label>

            <input 
                type="text" 
                name="nombre" 
                required
                value="<?= $articuloEditar['nombre'] ?? '' ?>"
            >

            <label>Descripción</label>

            <input 
                type="text" 
                name="descripcion" 
                value="<?= $articuloEditar['descripcion'] ?? '' ?>"
            >

            <div class="modal-buttons">

                <button type="submit" class="btn-agregar">
                    <?= isset($articuloEditar) 
                        ? 'Guardar cambios' 
                        : 'Agregar artículo' ?>
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

        <h2>Eliminar articulo</h2>

        <p id="mensajeEliminar">
            ¿Seguro que deseas eliminar este articulo?
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
        link.href = crearUrlConBusqueda(link.dataset.baseUrl);
    });

    if (btnExportar) {
        btnExportar.href = crearUrlConBusqueda(btnExportar.dataset.baseUrl);
    }

    window.history.replaceState(
        {},
        '',
        crearUrlConBusqueda('index.php?modulo=articulos')
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

<!--//////////-- Modal Articulo --//////////-->

<script>

function abrirModal(){

    document
    .getElementById('modalArticulo')
    .classList
    .add('active');

    document.body.style.overflow = 'hidden';
}

function cerrarModal(){

    <?php if(isset($articuloEditar)): ?>

        window.location.href = crearUrlConBusqueda('index.php?modulo=articulos');

    <?php else: ?>

        document
        .getElementById('modalArticulo')
        .classList
        .remove('active');

        document.body.style.overflow = 'auto';

    <?php endif; ?>
}

<?php if(isset($articuloEditar) || isset($errorFormulario)): ?>

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

        const articulo = this.dataset.articulo;

        mensajeEliminar.textContent =
            `¿Seguro que deseas eliminar el artículo "${articulo}"?`;

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
