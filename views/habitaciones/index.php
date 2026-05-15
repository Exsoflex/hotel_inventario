<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/styles.css">
    <title>Habitaciones</title>
</head>

<body>

<?php require_once __DIR__ . "/../layout/header.php"; ?>

    <h1>Lista de habitaciones</h1>

    <table border="1">

        <tr>
            <th>ID</th>
            <th>Piso</th>
            <th>Numero</th>
            <th>Tipo</th>
            <th>Descripción</th>
            <th>Eliminar</th>
            <th>Editar</th>
        </tr>

        <?php
        //var_dump($habitaciones); // Agrega esta línea para verificar el contenido de $habitaciones
        foreach($habitaciones as $h): ?>

            <tr>
                <td><?= $h['id'] ?></td>
                <td><?= $h['piso'] ?></td>
                <td><?= $h['numero'] ?></td>
                <td><?= $h['tipo'] ?></td>
                <td><?= $h['descripcion'] ?></td>
                <td>
                    <a href="index.php?modulo=habitaciones&accion=eliminar&id=<?= $h['id'] ?>"> 🗑 Eliminar</a>
                </td>
                <td>
                    <a href="index.php?modulo=habitaciones&accion=editar&id=<?= $h['id'] ?>"> ✏ Editar</a>
                </td>    
            </tr>

        <?php endforeach; ?>

    </table>

<br>
<h2>Agregar nueva habitación</h2>
<br>

    <br>


    <form 
    id="habitacionFormulario" 
    action="index.php?modulo=hanitaciones&accion=<?= isset($habitacionEditar) ? 'editar' : 'agregar' ?>" method="POST">

        <input 
        type="hidden" 
        name="id" 
        value="<?= $habitacionEditar['id'] ?? '' ?>"
        >

        <label>Piso de la habitacion</label>
        <input 
            type="number" 
            name="piso" 
            required 
            min="1"
            max="4"
            value="<?= $habitacionEditar['piso'] ?? '' ?>"
        >
        
        <label>Numero de habitacion</label>
        <input 
            type="number" 
            name="numero" 
            required 
            min="100"
            value="<?= $habitacionEditar['numero'] ?? '' ?>"
        >

        <label>Tipo de habitacion</label>
        <select name="tipo">
            <option 
                value="">Seleccione un tipo</option>
            <option 
                value="sencilla"
                
            <?=
                isset($habitacionEditar)&&
                $habitacionEditar['tipo'] == 'sencilla'

                ? 'selected' : ''

            ?>
                
                >Sencilla</option>

            <option 
                value="doble"
                
            <?=
                isset($habitacionEditar)&&
                $habitacionEditar['tipo'] == 'doble'

                ? 'selected' : ''

            ?>
                >Doble</option>

            <option 
                value="superior"
                
            <?=
                isset($habitacionEditar)&&
                $habitacionEditar['tipo'] == 'superior'

                ? 'selected' : ''

            ?>
                >Superior</option>
            
        </select>

        <label>Descripcion</label>
        <input 
            type="text" 
            name="descripcion" 
            value="<?= $habitacionEditar['descripcion'] ?? '' ?>"
        >

        <button type="submit">Guardar</button>
        <button type="reset">Cancelar</button>

    </form>

<?php require_once __DIR__ . "/../layout/footer.php"; ?>

</body>
</html>