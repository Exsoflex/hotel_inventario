<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/styles.css">
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
<input type="text" id="buscador" placeholder="Buscar...">
<br><br>

    <table>

    <thead>

         <tr>
            <th hidden>ID</th>
            <th>Tipo de habitación</th>
            <th>Articulo</th>
            <th>Cantidad</th>
            <th>Eliminar</th>
            <th>Editar</th>
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
                <td>
                    <a 
                    href="#"
                    class="btn-eliminar"
                    data-url="index.php?modulo=inventario_base&accion=eliminar&id=<?= $i['id'] ?>"
                    data-inventario_base="<?= $i['nombre'] ?>"
                    >
                    🗑 Eliminar
                    </a>
                </td>
                <td>
                    <a href="index.php?modulo=inventario_base&accion=editar&id=<?= $i['id'] ?>#inventario_baseFormulario"> ✏ Editar</a>
                </td>    
            </tr>

        <?php endforeach; ?>

    </tbody>

    </table>

<br>
<h2>Agregar nuevo inventario base</h2>
<br>

    <br>

    <form 
    id="inventario_baseFormulario" 
    action="index.php?modulo=inventario_base&accion=<?= isset($inventario_baseEditar) ? 'editar' : 'agregar' ?>" method="POST">

        <input 
        type="hidden" 
        name="id" 
        value="<?= $inventario_baseEditar['id'] ?? '' ?>"
        >

        <label>Tipo de habitacion</label>

        <select name="tipo">
            <option 
                value="">Seleccione un tipo</option>
            <option 
                value="sencilla"
                
            <?=
                isset($inventario_baseEditar)&&
                $inventario_baseEditar['tipo_habitacion'] == 'sencilla'

                ? 'selected' : ''

            ?>
                
                >Sencilla</option>

            <option 
                value="doble"
                
            <?=
                isset($inventario_baseEditar)&&
                $inventario_baseEditar['tipo_habitacion'] == 'doble'

                ? 'selected' : ''

            ?>
                >Doble</option>

            <option 
                value="superior"
                
            <?=
                isset($inventario_baseEditar)&&
                $inventario_baseEditar['tipo_habitacion'] == 'superior'

                ? 'selected' : ''

            ?>
                >Superior</option>
            
        </select>

        <br>

        <label>Articulo</label>
        <select name="articulo_id">

        <option value="">Seleccione un articulo</option>

            <?php foreach($articulos as $a): ?>

            <option 
                value="<?= $a['id'] ?>"
                
            <?= 
                isset($inventario_baseEditar) &&
                $inventario_baseEditar['articulo_id'] == $a['id']
                ? 'selected' : ''
            ?>    
            >
                <?= $a['nombre'] ?>
            </option>
            
            <?php endforeach; ?>
        </select>

        <br>

        <label>Cantidad</label>
        <input 
        type="number" 
        name="cantidad" 
        required
        min="0"
        value="<?= $inventario_baseEditar['cantidad'] ?? '' ?>"
        >

        <br><br>

        <button type="submit">Guardar</button>
        <button type="reset">Cancelar</button>

    </form>

</div>
<?php require_once __DIR__ . "/../layout/footer.php"; ?>


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

        const inventario_base = this.dataset.inventario_base;

        mensajeEliminar.textContent =
            `¿Seguro que deseas eliminar el artículo "${inventario_base}" del inventario base?`;

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