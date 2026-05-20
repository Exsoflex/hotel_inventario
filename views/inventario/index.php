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
    <input type="text" id="buscador" placeholder="Buscar...">
    <button class="btn-agregar" onclick="abrirModal()">
        + Agregar inventario
    </button>
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
                    <div class="inventario-actions">
                        <a href="index.php?modulo=inventario&accion=editar&id=<?= $i['id'] ?>">
                            Editar
                        </a>
                        <a 
                            href="index.php?modulo=inventario&accion=eliminar&id=<?= $i['id'] ?>"
                            onclick="return confirm('¿Seguro que deseas eliminar este registro?')"
                        >
                                Eliminar
                        </a>

                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endforeach; ?>


<!-- /////////////////////////////////////////////////////// -->



</div>
<?php require_once __DIR__ . "/../layout/footer.php"; ?>


<script>

const buscador = document.getElementById('buscador');
buscador.addEventListener('keyup', function(){

    let texto = buscador.value.toLowerCase();
    // Todas las secciones de habitación
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
            // Buscar tanto en habitación como en contenido
            let coincide =
                contenido.includes(texto) ||
                tituloHabitacion.includes(texto);

            card.style.display = coincide ? '' : 'none';
            if(coincide){
                algunaVisible = true;
            }
        });

        // Mostrar u ocultar toda la sección
        seccion.style.display = algunaVisible ? '' : 'none';
    });
});

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

</body>
</html>