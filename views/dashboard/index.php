<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/styles.css">
    <title>Dashboard</title>
</head>

<body>

<?php require_once __DIR__ . "/../layout/header.php"; ?>

    <h1>Dashboard</h1>

    <input type="text" id="buscador" placeholder="Buscar...">

    <br><br>

    <?php
    // Agrupar habitaciones por piso
    $porPiso = [];
    foreach ($habitaciones as $h) {
        $porPiso[$h['piso']][] = $h;
    }
    ksort($porPiso); // Ordenar pisos de menor a mayor
    ?>

    <?php foreach ($porPiso as $piso => $habitacionesPiso): ?>

        <h2>Piso <?= $piso ?></h2>

        <table border="1">

            <thead>
                <tr>
                    <th>Habitación</th>
                    <th>Tipo</th>
                    <th>Artículos faltantes</th>
                    <th>Detalles</th>
                </tr>
            </thead>

            <tbody>

                <?php foreach ($habitacionesPiso as $h): ?>

                    <tr id="habitacion-<?= $h['habitacion_id'] ?>">

                        <td><?= $h['numero'] ?></td>

                        <td><?= $h['tipo'] ?></td>

                        <td>
                            <?php if ($h['total_faltantes'] > 0): ?>

                                <span style="color:red; font-weight:bold;">
                                    <?= $h['articulos_faltantes'] ?>
                                </span>

                            <?php elseif (is_null($h['articulos_faltantes'])): ?>

                                <span style="color:gray;">
                                    Sin inventario base definido
                                </span>

                            <?php else: ?>

                                <span style="color:green;">
                                    Completo ✓
                                </span>

                            <?php endif; ?>
                        </td>

                        <td>
                        <a href="index.php?modulo=revision&buscar=<?= $h['numero'] ?>">
                         🔍 Ver detalles
                        </a>
                        </td>

                    </tr>
                <?php endforeach; ?>
                
            </tbody>
        </table>

        <br>

    <?php endforeach; ?>

<?php require_once __DIR__ . "/../layout/footer.php"; ?>

<script>

const buscador = document.getElementById('buscador');
buscador.addEventListener('keyup', function() {

    let texto = buscador.value.toLowerCase();

    // Busca en todas las tablas del dashboard
    let filas = document.querySelectorAll("table tbody tr");

    filas.forEach(function(fila) {

        let celdas = fila.querySelectorAll("td:not([hidden])");
        let contenido = Array.from(celdas).map(td => td.textContent).join(' ').toLowerCase();

        if (contenido.includes(texto)) {
            fila.style.display = "";
        } else {
            fila.style.display = "none";
        }
    });

    // Ocultar titulo de piso si todas sus filas están ocultas
    document.querySelectorAll("h2").forEach(function(titulo) {

        let tabla = titulo.nextElementSibling;
        let filasVisibles = tabla.querySelectorAll("tbody tr:not([style*='display: none'])");

        titulo.style.display = filasVisibles.length > 0 ? "" : "none";

    });

});

</script>

</body>
</html>