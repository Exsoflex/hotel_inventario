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

    <h1>Lista de Articulos</h1>

    <table border="1">

        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Descripción</th>
            <th>Eliminar</th>
            <th>Editar</th>
        </tr>

        <?php
        //var_dump($articulo); // Agrega esta línea para verificar el contenido de $habitaciones
        foreach($articulos as $a): ?>

            <tr>
                <td><?= $a['id'] ?></td>
                <td><?= $a['nombre'] ?></td>
                <td><?= $a['descripcion'] ?></td>
                <td>
                    <a href="index.php?modulo=articulos&accion=eliminar&id=<?= $a['id'] ?>"> 🗑 Eliminar</a>
                </td>
                <td>
                    <a href="index.php?modulo=articulos&accion=editar&id=<?= $a['id'] ?>"> ✏ Editar</a>
                </td>      
            </tr>

        <?php endforeach; ?>

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

<?php require_once __DIR__ . "/../layout/footer.php"; ?>

</body>
</html>