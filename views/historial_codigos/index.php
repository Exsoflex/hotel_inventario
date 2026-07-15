<?php
/** @var array<int, array<string, mixed>> $historial */
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="icon" type="image/png" href="/hotel_inventario/assets/img/HLH_logo.png?">
    <title>Historial de códigos</title>
</head>

<body>

<?php require_once __DIR__ . "/../layout/header.php"; ?>

<div class="page-header">
    <h1>Historial de códigos</h1>
    <p>Consulta de artículos por código de barras</p>
</div>

<!-- Mensajes -->
<?php if (isset($_GET['mensaje']) && $_GET['mensaje'] === 'eliminado'): ?>
    <div class="alerta-exito">
        Registro eliminado correctamente ✓
    </div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <div class="alerta-error">
        <?php if ($_GET['error'] === 'no_encontrado'): ?>
            ⚠ No se encontró ningún artículo con el código
            <strong><?= htmlspecialchars($_GET['codigo'] ?? '') ?></strong>.
        <?php elseif ($_GET['error'] === 'vacio'): ?>
            ⚠ Escribe un código antes de buscar.
        <?php endif; ?>
    </div>
<?php endif; ?>

<!-- Barra de búsqueda por código -->
<div class="container">

    <form
        action="index.php?modulo=historial_codigos&accion=buscar"
        method="POST"
        class="inventario-topbar"
    >
        <div class="buscador-wrapper historial-codigo-buscador">
            <input
                type="text"
                name="codigo"
                id="codigo"
                placeholder="Escribe un código..."
                autofocus
                required
            >
            <button type="button" id="btnLimpiarBusqueda" class="btn-limpiar-buscador" title="Limpiar búsqueda">
                <i data-lucide="x"></i>
            </button>
        </div>
        <button type="submit" class="btn-agregar">
            Buscar
        </button>
    </form>

    <br>

    <!-- Tabla de historial -->
    <?php if (empty($historial)): ?>

        <p>No hay registros en el historial todavía.</p>

    <?php else: ?>

        <table>
            <thead>
                <tr>
                    <th>Fecha y hora</th>
                    <th>Usuario</th>
                    <th>Código</th>
                    <th>Artículo</th>
                    <th>Habitación</th>
                    <th>Estado</th>
                    <th>Ver en inventario</th>
                    <?php if (in_array(
                        $_SESSION['usuario']['rol'],
                        ['admin', 'supervisor']
                    )): ?>
                    <th>Eliminar</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($historial as $h): ?>
                <tr>
                    <td><?= $h['fecha_hora'] ?></td>
                    <td><?= htmlspecialchars($h['usuario']) ?></td>
                    <td><?= htmlspecialchars($h['codigo_barras']) ?></td>
                    <td><?= htmlspecialchars($h['articulo']) ?></td>
                    <td>Habitación <?= $h['habitacion'] ?></td>
                    <td>
                        <span class="estado-badge estado-<?= $h['estado'] ?>">
                            <?= ucfirst($h['estado']) ?>
                        </span>
                    </td>
                    <td>
                        <a href="index.php?modulo=inventario&buscar=<?= urlencode($h['codigo_barras']) ?>">
                            Ver artículo
                        </a>
                    </td>
                    <?php if (in_array(
                        $_SESSION['usuario']['rol'],
                        ['admin', 'supervisor']
                    )): ?>
                    <td>
                        <a
                            href="#"
                            class="btn-eliminar"
                            data-url="index.php?modulo=historial_codigos&accion=eliminar&id=<?= $h['id'] ?>"
                            data-codigo="<?= htmlspecialchars($h['codigo_barras']) ?>"
                        >
                            Eliminar
                        </a>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    <?php endif; ?>

</div>
<?php require_once __DIR__ . "/../layout/footer.php"; ?>
<!-- Modal confirmación eliminar -->
<div class="modal-overlay" id="modalEliminar">
    <div class="modal-confirmacion">
        <div class="modal-icono">⚠</div>
        <h2>Eliminar registro</h2>
        <p id="mensajeEliminar">¿Seguro que deseas eliminar este registro del historial?</p>
        <div class="modal-botones">
            <button onclick="cerrarModalEliminar()">
                Cancelar
            </button>
            <a href="#" id="btnConfirmarEliminar" class="btn-confirmar">
                Sí, eliminar
            </a>
        </div>
    </div>
</div>
<script>
const botonesEliminar = document.querySelectorAll('.btn-eliminar');
const modalEliminar   = document.getElementById('modalEliminar');
const mensajeEliminar = document.getElementById('mensajeEliminar');
const btnConfirmar    = document.getElementById('btnConfirmarEliminar');

botonesEliminar.forEach(boton => {
    boton.addEventListener('click', function(e) {
        e.preventDefault();
        const url    = this.dataset.url;
        const codigo = this.dataset.codigo;
        mensajeEliminar.textContent =
            `¿Seguro que deseas eliminar el registro del código "${codigo}"?`;
        btnConfirmar.href = url;
        modalEliminar.classList.add('active');
    });
});

function cerrarModalEliminar() {
    modalEliminar.classList.remove('active');
}

const inputCodigo = document.getElementById('codigo');
const btnLimpiarBusqueda = document.getElementById('btnLimpiarBusqueda');

if (inputCodigo && btnLimpiarBusqueda) {
    btnLimpiarBusqueda.classList.toggle('hidden', !inputCodigo.value);
    
    inputCodigo.addEventListener('input', function() {
        btnLimpiarBusqueda.classList.toggle('hidden', !this.value);
    });
    
    btnLimpiarBusqueda.addEventListener('click', function() {
        inputCodigo.value = '';
        btnLimpiarBusqueda.classList.add('hidden');
        inputCodigo.focus();
    });
}
</script>

</body>
</html>
