<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/styles.css">

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
        >

        <button 
        class="btn-agregar"
        onclick="abrirModal()"
        >
            + Agregar articulo
        </button>

    </div>

    <br>

<!-- /////////////////////////////////////////////////////// -->

    <table>

    <thead>
        <tr>
            <th hidden>ID</th>
            <th>Nombre</th>
            <th>Descripción</th>
            <th>Eliminar</th>
            <th>Editar</th>
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
                <td>
                    <a 
                    href="#"
                    class="btn-eliminar"
                    data-url="index.php?modulo=articulos&accion=eliminar&id=<?= $a['id'] ?>"
                    data-articulo="<?= $a['nombre'] ?>"
                    >
                    Eliminar
                    </a>
                </td>
                <td>
                    <a href="index.php?modulo=articulos&accion=editar&id=<?= $a['id'] ?>">                
                    Editar
                    </a>
                    </td>      
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
                type="button"
                class="btn-cancelar"
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
buscador.addEventListener('keyup', function() {

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
});

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

    document
    .getElementById('modalArticulo')
    .classList
    .remove('active');

    document.body.style.overflow = 'auto';
}

<?php if(isset($articuloEditar)): ?>

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

        const url = this.dataset.url;

        const articulo = this.dataset.articulo;

        mensajeEliminar.textContent =
            `¿Seguro que deseas eliminar el artículo "${articulo}"?`;

        btnConfirmarEliminar.href = url;

        modalEliminar.classList.add('active');
    });
});

function cerrarModalEliminar(){

    modalEliminar.classList.remove('active');
}

</script>

</body>
</html>