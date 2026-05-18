<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="assets/css/styles.css">

    <title>Revision</title>

</head>

<body>

<?php require_once __DIR__ . "/../layout/header.php"; ?>

<h1>Revision de habitaciones</h1>

<input type="text" id="buscador" placeholder="Buscar...">
<br><br>

<table border="1">

    <thead>

        <tr>

            <th>Habitacion</th>
            <th>Tipo</th>
            <th>Articulo</th>
            <th>Debe tener</th>
            <th>Tiene</th>
            <th>Faltan</th>
            <th>Estado</th>

        </tr>

    </thead>

    <tbody>

        <?php foreach($faltantes as $f): ?>

        <tr>

            <td><?= $f['numero'] ?></td>

            <td><?= $f['tipo'] ?></td>

            <td><?= $f['articulo'] ?></td>

            <td><?= $f['cantidad_base'] ?></td>

            <td><?= $f['cantidad_actual'] ?></td>

            <td>

                <?php if($f['faltantes'] > 0): ?>

                    <span style="color:red; font-weight:bold;">
                        <?= $f['faltantes'] ?>
                    </span>

                <?php else: ?>

                    <span style="color:green;">
                        Completo
                    </span>

                <?php endif; ?>

            </td>

            <td><?= $f['estado'] ?? 'faltante' ?></td>

        </tr>

        <?php endforeach; ?>

    </tbody>

</table>

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