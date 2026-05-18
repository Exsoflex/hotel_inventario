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

    <input type="text" id="buscador" placeholder="Buscar...">
<br><br>

    <table>

    <thead>
        <tr>
            <th>ID</th>
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
                <td><?= $a['id'] ?></td>
                <td><?= $a['nombre'] ?></td>
                <td><?= $a['descripcion'] ?></td>
                <td>
                    <a href="index.php?modulo=articulos&accion=eliminar&id=<?= $a['id'] ?>"> 🗑 Eliminar</a>
                </td>
                <td>
                    <a href="index.php?modulo=articulos&accion=editar&id=<?= $a['id'] ?>#articuloFormulario"> ✏ Editar</a>
                </td>      
            </tr>

        <?php endforeach; ?>

    </tbody>
    </table>

<br>
<h2>Agregar nuevo articulo</h2>
<br>

    <br>


    <form id="articuloFormulario" action="index.php?modulo=articulos&accion=<?= isset($articuloEditar) ? 'editar' : 'agregar' ?>" method="POST">

        <label>Nombre de articulo</label>

        <input
            type="hidden"
            name="id"
            value="<?= $articuloEditar['id'] ?? '' ?>"
        >

        <input 
            type="text" 
            name="nombre" 
            required
            value="<?= $articuloEditar['nombre'] ?? '' ?>"
        >
        
        <br><br>

        <label>Descripcion</label>
        <input 
            type="text" 
            name="descripcion" 
            value="<?= $articuloEditar['descripcion'] ?? '' ?>"
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