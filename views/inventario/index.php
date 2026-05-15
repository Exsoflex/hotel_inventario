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

    <h1>Lista de inventario</h1>

    <table border="1">

        <tr>
            <th>ID</th>
            <th>Habitación</th>
            <th>Articulo</th>
            <th>Cantidad</th>
            <th>Estado</th>
            <th>Comentarios</th>
            <th>Eliminar</th>
            <th>Editar</th>
        </tr>

        <?php
        //var_dump($habitaciones); // Agrega esta línea para verificar el contenido de $habitaciones
        foreach($inventarios as $i): ?>

            <tr>
                <td><?= $i['id'] ?></td>
                <td><?= $i['numero'] ?></td>
                <td><?= $i['nombre'] ?></td>
                <td><?= $i['cantidad'] ?></td>
                <td><?= $i['estado'] ?></td>
                <td><?= $i['comentarios'] ?></td>
                <td>
                    <a href="index.php?modulo=inventario&accion=eliminar&id=<?= $i['id'] ?>"> 🗑 Eliminar</a>
                </td>
                <td>
                    <a href="index.php?modulo=inventario&accion=editar&id=<?= $i['id'] ?>"> ✏ Editar</a>
                </td>    
            </tr>

        <?php endforeach; ?>

    </table>

<br>
<h2>Agregar nuevo inventario</h2>
<br>

    <br>

    <form 
    id="inventarioFormulario" 
    action="index.php?modulo=inventario&accion=<?= isset($inventarioEditar) ? 'editar' : 'agregar' ?>" method="POST">

        <input 
        type="hidden" 
        name="id" 
        value="<?= $inventarioEditar['id'] ?? '' ?>"
        >

        <label>Habitacion</label>
        <select name="habitacion_id">

        <option value="">Seleccione una habitacion</option>

            <?php foreach($habitaciones as $h): ?>

            <option 
                value="<?= $h['id'] ?>"
            
            <?= 
                isset($inventarioEditar) && 
                $inventarioEditar['habitacion_id'] == $h['id'] 
                ? 'selected' : ''
            ?>
            >

                Habitacion <?= $h['numero'] ?>
            </option>

            <?php endforeach; ?>
        </select>

        <br>

        <label>Articulo</label>
        <select name="articulo_id">

        <option value="">Seleccione un articulo</option>

            <?php foreach($articulos as $a): ?>

            <option 
                value="<?= $a['id'] ?>"
                
            <?= 
                isset($inventarioEditar) &&
                $inventarioEditar['articulo_id'] == $a['id']
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
        value="<?= $inventarioEditar['cantidad'] ?? '' ?>"
        >
        
        <br>

        <select name="estado">
            <option 
                value="">Seleccione un estado</option>
            <option 
                value="bueno"
                
            <?=
                isset($inventarioEditar)&&
                $inventarioEditar['estado'] == 'bueno'

                ? 'selected' : ''

            ?>
                
                >Bueno</option>

            <option 
                value="dañado"
                
            <?=
                isset($inventarioEditar)&&
                $inventarioEditar['estado'] == 'dañado'

                ? 'selected' : ''

            ?>
                >Dañado</option>

            <option 
                value="en_reparacion"
                
            <?=
                isset($inventarioEditar)&&
                $inventarioEditar['estado'] == 'en_reparacion'

                ? 'selected' : ''

            ?>
                >En reparación</option>

            <option 
                value="perdido"
                
            <?=
                isset($inventarioEditar)&&
                $inventarioEditar['estado'] == 'perdido'

                ? 'selected' : ''

            ?>
                >Perdido</option>
            
        </select>

        <label>Comentarios</label>
        <input 
        type="text" 
        name="comentarios" 
        value="<?= $inventarioEditar['comentarios'] ?? '' ?>"
        >

        <br><br>

        <button type="submit">Guardar</button>
        <button type="reset">Cancelar</button>

    </form>

<?php require_once __DIR__ . "/../layout/footer.php"; ?>

</body>
</html>