<?php
/** @var array<int, array<string, mixed>> $movimientos */
/** @var int $pagina */
/** @var int $totalPaginas */
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="icon" type="image/png" href="/hotel_inventario/assets/img/HLH_logo.png?">
    <title>Movimientos</title>
</head>

<body>

<?php require_once __DIR__ . "/../layout/header.php"; ?>

<div class="page-header">
    <h1>Movimientos</h1>
    <p>Registro de actividad del sistema</p>
</div>

<div class="container">

    <div class="inventario-topbar">
        <div class="buscador-wrapper">
            <input 
                type="text" 
                id="buscador" 
                placeholder="Buscar por usuario, módulo, acción..."
            >
            <button type="button" id="btnLimpiarBusqueda" class="btn-limpiar-buscador" title="Limpiar búsqueda">
                <i data-lucide="x"></i>
            </button>
        </div>
    </div>

    <br>

    <table>

        <thead>
            <tr>
                <th>Usuario</th>
                <th>Rol</th>
                <th>Módulo</th>
                <th>Acción</th>
                <th>Descripción</th>
                <th>Fecha</th>
            </tr>
        </thead>

        <tbody>

            <?php foreach ($movimientos as $m): ?>

                <tr>
                    <td><?= htmlspecialchars($m['usuario']) ?></td>

                    <td>
                        <span class="estado-badge estado-<?= $m['rol'] ?>">
                            <?= ucfirst($m['rol']) ?>
                        </span>
                    </td>

                    <td><?= htmlspecialchars($m['modulo']) ?></td> 

                    <td>
                        <span class="badge-accion badge-<?= $m['accion'] ?>">
                            <?= ucfirst($m['accion']) ?>
                        </span>
                    </td>

                    <td><?= htmlspecialchars($m['descripcion']) ?></td>

                    <td>
                        <?= date('d/m/Y H:i', strtotime($m['fecha'])) ?>
                    </td>
                </tr>

            <?php endforeach; ?>

            <?php if (empty($movimientos)): ?>
                <tr>
                    <td colspan="6" style="text-align:center; color:#999; padding:30px;">
                        No hay movimientos registrados aún.
                    </td>
                </tr>
            <?php endif; ?>

        </tbody>

    </table>

<!-- /////////////////////////////////////////////////////// -->
    <div class="paginacion">

<?php if($pagina > 1): ?>

    
    <a href="index.php?modulo=movimientos&pagina=<?= $pagina - 1 ?>">
        ← Anterior
    </a>

<?php endif; ?>

<?php for($i = 1; $i <= $totalPaginas; $i++): ?>

    <a
    href="index.php?modulo=movimientos&pagina=<?= $i ?>"
    class="<?= $i == $pagina ? 'pagina-activa' : '' ?>"
    >
        <?= $i ?>
    </a>

<?php endfor; ?>

<?php if($pagina < $totalPaginas): ?>

    <a href="index.php?modulo=movimientos&pagina=<?= $pagina + 1 ?>">
       Siguiente →
    </a>

<?php endif; ?>

</div>

<!-- /////////////////////////////////////////////////////// -->

</div>

<?php require_once __DIR__ . "/../layout/footer.php"; ?>

<script>

const buscador = document.getElementById('buscador');
const btnLimpiarBusqueda = document.getElementById('btnLimpiarBusqueda');

buscador.addEventListener('input', function() {

    let texto = buscador.value.toLowerCase();
    let filas = document.querySelectorAll("table tbody tr");

    filas.forEach(function(fila) {

        let celdas = fila.querySelectorAll("td:not([hidden])");
        let contenido = Array.from(celdas).map(td => td.textContent).join(' ').toLowerCase();

        fila.style.display = contenido.includes(texto) ? "" : "none";
    });
    
    if (btnLimpiarBusqueda) {
        btnLimpiarBusqueda.classList.toggle('hidden', !buscador.value);
    }
});

if (btnLimpiarBusqueda) {
    btnLimpiarBusqueda.addEventListener('click', function() {
        buscador.value = '';
        buscador.dispatchEvent(new Event('input'));
    });
}

document.addEventListener('DOMContentLoaded', function() {
    if (btnLimpiarBusqueda) {
        btnLimpiarBusqueda.classList.toggle('hidden', !buscador.value);
    }
});

</script>

</body>
</html>
