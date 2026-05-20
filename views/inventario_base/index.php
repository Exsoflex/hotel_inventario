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
            <th>ID</th>
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
                <td><?= $i['id'] ?></td>
                <td><?= $i['tipo_habitacion'] ?></td>
                <td><?= $i['nombre'] ?></td>
                <td><?= $i['cantidad'] ?></td>
                <td>
                    <a href="index.php?modulo=inventario_base&accion=eliminar&id=<?= $i['id'] ?>"
                    onclick="return confirm('¿Seguro que deseas eliminar este articulo del inventario base?')">
                         🗑 Eliminar</a>
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

</body>
</html>