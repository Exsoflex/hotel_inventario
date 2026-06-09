<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="icon" type="image/png" href="/hotel_inventario/assets/img/HLH_logo.png?">
    <title>Habitaciones</title>
</head>

<body>

<?php require_once __DIR__ . "/../layout/header.php"; ?>

<!-- /////////////////////////////////////////////////////// -->

<div class="page-header">
    <h1>Lista de habitaciones</h1>
    <p>Administración de habitaciones del hotel</p>
</div>

<!-- /////////////////////////////////////////////////////// -->

<div class="container">

    <div class="inventario-topbar">

        <input 
        type="text" 
        id="buscador" 
        placeholder="Buscar habitaciones..."
        >

        <a
        href="index.php?modulo=habitaciones&accion=exportar"
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
            + Agregar habitación
        </button>
        <?php endif; ?>

    </div>

<!-- /////////////////////////////////////////////////////// -->


    <br>

    <table>
    <thead>

        <tr>
            <th hidden>ID</th>
            <th>Piso</th>
            <th>Numero</th>
            <th>Tipo</th>
            <th>Estado</th>
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
        //var_dump($habitaciones); // Agrega esta línea para verificar el contenido de $habitaciones
        foreach($habitaciones as $h): ?>

            <tr id="habitacion-<?= $h['id'] ?>">
                <td hidden><?= $h['id'] ?></td>
                <td><?= $h['piso'] ?></td>
                <td><?= $h['numero'] ?></td>
                <td><?= $h['tipo'] ?></td>
                <td>
                    <span class="estado-badge badge-<?= $h['estado'] ?>">
                        <?= ucfirst($h['estado']) ?>
                    </span>
                </td>
                <td><?= $h['descripcion'] ?></td>


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
                    data-url="index.php?modulo=habitaciones&accion=eliminar&id=<?= $h['id'] ?>"
                    data-habitacion="<?= $h['numero'] ?>"
                    class="table-action">
                    Eliminar
                    </a>
                </td>
                <td>
                    <a href="index.php?modulo=habitaciones&accion=editar&id=<?= $h['id'] ?>" class="table-action">                
                    Editar
                    </a>
                </td>  
                <?php endif; ?>  
            </tr>

        <?php endforeach; ?>

    </tbody>

    </table>

</div>

<!-- /////////////////////////////////////////////////////// -->
<?php require_once __DIR__ . "/../layout/footer.php"; ?>


<!--//////////-- Modal Habitacion --//////////-->

<div class="modal-overlay" id="modalHabitacion">

    <div class="modal">

        <div class="modal-header">

            <h2>
                <?= isset($habitacionEditar) 
                    ? 'Editar habitación' 
                    : 'Agregar habitación' ?>
            </h2>

            <button onclick="cerrarModal()">
                ✕
            </button>

        </div>

        <form 
        id="habitacionFormulario"
        action="index.php?modulo=habitaciones&accion=<?= isset($habitacionEditar) ? 'editar' : 'agregar' ?>" 
        method="POST">

        <?php if (isset($errorFormulario)): ?>
        <div class="alerta-error">
            ⚠ <?= htmlspecialchars($errorFormulario) ?>
        </div>
        <?php endif; ?>

            <input 
            type="hidden" 
            name="id"
            value="<?= $habitacionEditar['id'] ?? '' ?>"
            >

            <label>Piso de la habitación</label>

            <input 
            type="number"
            name="piso"
            required
            min="1"
            max="4"
            value="<?= $habitacionEditar['piso'] ?? '' ?>"
            >

            <label>Número de habitación</label>

            <input 
            type="number"
            name="numero"
            required
            min="100"
            value="<?= $habitacionEditar['numero'] ?? '' ?>"
            >

            <label>Tipo de habitación</label>

            <select name="tipo" required>

                <option value="">
                    Seleccione un tipo
                </option>

                <option 
                value="sencilla"
                <?= isset($habitacionEditar) && $habitacionEditar['tipo'] == 'sencilla' ? 'selected' : '' ?>
                >
                    Sencilla
                </option>

                <option 
                value="doble"
                <?= isset($habitacionEditar) && $habitacionEditar['tipo'] == 'doble' ? 'selected' : '' ?>
                >
                    Doble
                </option>

                <option 
                value="superior"
                <?= isset($habitacionEditar) && $habitacionEditar['tipo'] == 'superior' ? 'selected' : '' ?>
                >
                    Superior
                </option>

            </select>

            <label>Estado de la habitación</label>

            <select name="estado" required>

                <option value="">
                    Seleccione un estado
                </option>

                <option 
                value="disponible"
                <?= isset($habitacionEditar) && $habitacionEditar['estado'] == 'sencilla' ? 'selected' : '' ?>
                >
                    Disponible
                </option>

                <option 
                value="ocupada"
                <?= isset($habitacionEditar) && $habitacionEditar['estado'] == 'ocupada' ? 'selected' : '' ?>
                >
                    Ocupada
                </option>

                <option 
                value="limpieza"
                <?= isset($habitacionEditar) && $habitacionEditar['estado'] == 'limpieza' ? 'selected' : '' ?>
                >
                    Limpieza
                </option>

                <option 
                value="mantenimiento"
                <?= isset($habitacionEditar) && $habitacionEditar['estado'] == 'mantenimiento' ? 'selected' : '' ?>
                >
                    Mantenimiento
                </option>

                <option 
                value="bloqueada"
                <?= isset($habitacionEditar) && $habitacionEditar['estado'] == 'bloqueada' ? 'selected' : '' ?>
                >
                    Bloqueada
                </option>

            </select>

            <label>Descripción</label>

            <input 
            type="text"
            name="descripcion"
            value="<?= $habitacionEditar['descripcion'] ?? '' ?>"
            >

            <div class="modal-buttons">

                <button type="submit" class="btn-agregar">
                    Guardar
                </button>

                <button 
                type="reset"
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

        <h2>Eliminar habitación</h2>

        <p id="mensajeEliminar">
            ¿Seguro que deseas eliminar esta habitación?
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

<!--////////////////////////////// Modal Eliminar //////////////////////////////////-->
<script>

const botonesEliminar = document.querySelectorAll('.btn-eliminar');
const modalEliminar = document.getElementById('modalEliminar');
const mensajeEliminar = document.getElementById('mensajeEliminar');
const btnConfirmarEliminar = document.getElementById('btnConfirmarEliminar');

botonesEliminar.forEach(boton => {

    boton.addEventListener('click', function(e){

        e.preventDefault();

        const url = this.dataset.url;

        const habitacion = this.dataset.habitacion;

        mensajeEliminar.textContent =
            `¿Seguro que deseas eliminar la habitación ${habitacion}?`;

        btnConfirmarEliminar.href = url;

        modalEliminar.classList.add('active');
    });
});

function cerrarModalEliminar(){
    modalEliminar.classList.remove('active');
}
</script>

<script>

function abrirModal(){

    document
    .getElementById('modalHabitacion')
    .classList
    .add('active');

    document.body.style.overflow = 'hidden';
}

function cerrarModal(){

    <?php if(isset($habitacionEditar)): ?>

        window.location.href = 'index.php?modulo=habitaciones';

    <?php else: ?>

        document
        .getElementById('modalHabitacion')
        .classList
        .remove('active');

        document.body.style.overflow = 'auto';

    <?php endif; ?>
}

<?php if(isset($habitacionEditar) || isset($errorFormulario)): ?>
abrirModal();
<?php endif; ?>

</script>

</body>
</html>