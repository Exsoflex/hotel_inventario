<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/styles.css">
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
    <input type="text" id="buscador" placeholder="Buscar..."
    value="<?= htmlspecialchars($_GET['buscar'] ?? '') ?>">

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
                <div class="inventario-card">
                    <div class="inventario-card-header">
                        <div>
                            <h3><?= $i['nombre'] ?></h3>
                        </div>
                        <div class="estado-badge estado-<?= $i['estado'] ?>">
                            <?= ucfirst($i['estado']) ?>
                        </div>
                    </div>
                    <div class="inventario-info">

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
                             
                        <a href="index.php?modulo=inventario&accion=editar&id=<?= $i['id'] ?>">
                            Editar
                        </a>   
                        <a 
                        href="#"
                        class="btn-eliminar"
                        data-url="index.php?modulo=inventario&accion=eliminar&id=<?= $i['id'] ?>"
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
       </div>

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
            <select name="articulo_id">

                <option value="">Seleccione un artículo</option>
                <?php foreach($articulos as $a): ?>

                    <option
                    value="<?= $a['id'] ?>"

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

            <label>Estado</label>

            <select 
            name="estado"
            value="<?= $inventarioEditar['estado'] ?? '' ?>">

                <option value="">Seleccione un estado</option>
                <option value="bueno">Bueno</option>
                <option value="dañado">Dañado</option>
                <option value="en_reparacion">En reparación</option>
                <option value="perdido">Perdido</option>

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
                type="button"
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

const buscador = document.getElementById('buscador');

function filtrarInventario(){

    let texto = buscador.value.toLowerCase();

    let secciones = document.querySelectorAll('.habitacion-section');

    secciones.forEach(function(seccion){

        let tituloHabitacion = seccion
            .querySelector('.habitacion-section-header h2')
            .textContent
            .toLowerCase();

        let cards = seccion.querySelectorAll('.inventario-card');

        let algunaVisible = false;

        cards.forEach(function(card){

            let contenido = card.textContent.toLowerCase();

            let coincide =
                contenido.includes(texto) ||
                tituloHabitacion.includes(texto);

            card.style.display = coincide ? '' : 'none';

            if(coincide){
                algunaVisible = true;
            }

        });

        seccion.style.display = algunaVisible ? '' : 'none';

    });
}

buscador.addEventListener('keyup', filtrarInventario);

document.addEventListener('DOMContentLoaded', function(){

    if(buscador.value.trim() !== ''){
        filtrarInventario();
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

function cerrarModal(){

    document
    .getElementById('modalInventario')
    .classList
    .remove('active');
    document.body.style.overflow = 'auto';
}

<?php if(isset($inventarioEditar)): ?>
abrirModal();
<?php endif; ?>

/*////////---- Confirmar eliminación ----/////////*/

function confirmarEliminacion(id){

    let confirmar = confirm(
        "¿Seguro que deseas eliminar este registro?"
    );

    if(confirmar){

        window.location.href =
            "index.php?modulo=inventario&accion=eliminar&id=" + id;
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

</body>
</html>