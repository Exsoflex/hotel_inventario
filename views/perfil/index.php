-- Active: 1771267609556@@127.0.0.1@3306@hotel_inventario
<?php
/** @var array{nombre: string, correo: string, rol: string} $perfil */
/** @var array<int, array{modulo: string, accion: string, descripcion: string, fecha: string}> $movimientos */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@100..1000&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="icon" type="image/png" href="/hotel_inventario/assets/img/HLH_logo.png?">
    <title>Mi perfil</title>
</head>
<body>

<?php require_once __DIR__ . "/../layout/header.php"; ?>

<div class="page-header">
    <h1>Mi perfil</h1>
    <p>Administra tu información personal</p>
</div>

<!-- /////////////////////////////////////////////////////// -->

<?php if (isset($_GET['exito'])): ?>
<div class="alerta-exito">
    ✓ Perfil actualizado correctamente.
</div>
<?php endif; ?>

<?php if (isset($errorFormulario)): ?>
<div class="alerta-error">
    ⚠ <?= htmlspecialchars($errorFormulario) ?>
</div>
<?php endif; ?>

<!-- /////////////////////////////////////////////////////// -->

<div class="perfil-layout">

    <!-- ===== COLUMNA IZQUIERDA: formulario ===== -->
    <div class="container perfil-form">

        <div class="perfil-cabecera">
            <div class="user-avatar" style="width:64px; height:64px; font-size:26px; flex-shrink:0;">
                <?= strtoupper(substr($perfil['nombre'], 0, 1)) ?>
            </div>
            <div>
                <p style="font-size:20px; font-weight:700; margin:0;">
                    <?= htmlspecialchars($perfil['nombre']) ?>
                </p>
                <p style="color:#777; margin:4px 0 0;">
                    <?= ucfirst($perfil['rol']) ?>
                </p>
            </div>
        </div>

        <form action="index.php?modulo=perfil&accion=editar" method="POST">

            <label>Nombre</label>
            <input
                type="text"
                name="nombre"
                required
                value="<?= htmlspecialchars($perfil['nombre']) ?>"
            >

            <label>Correo</label>
            <input
                type="email"
                name="correo"
                required
                value="<?= htmlspecialchars($perfil['correo']) ?>"
            >

            <label style="color:#aaa;">Rol</label>
            <input
                type="text"
                value="<?= ucfirst($perfil['rol']) ?>"
                disabled
                style="color:#aaa; cursor:not-allowed;"
            >

            <label>Contraseña</label>
            <div class="input-password">
                <input
                    type="password"
                    name="password"
                    id="password"
                    placeholder="Nueva contraseña"
                >
                <button type="button" class="menu-btn" id="btnMostrarPassword">
                    <i data-lucide="eye"></i>
                </button>
            </div>

            <div style="display:flex; gap:12px; margin-top:10px;">
                <button type="submit" class="btn-agregar">
                    Guardar cambios
                </button>
                <a
                    href="index.php?modulo=dashboard"
                    class="btn-cancelar"
                    style="padding:14px 22px; border-radius:18px; font-weight:700;"
                >
                    Cancelar
                </a>
            </div>

        </form>
    </div>

    <!-- ===== COLUMNA DERECHA: movimientos ===== -->
    <div class="container perfil-movimientos">

        <div class="perfil-color-card">
            <h2 style="margin-bottom:8px;">Color de fondo</h2>
            <p style="margin:0 0 12px; color:#777;">Elige una paleta que se aplique al fondo de la aplicación, incluso si cambias de tema.</p>

            <div class="perfil-color-selector" role="group" aria-label="Seleccionar color de fondo">
                <button type="button" class="perfil-color-option active" data-preset="sunset" aria-label="Naranja y morado" style="--preset-gradient: linear-gradient(120deg, #ffb06b 0%, #e86a8a 42%, #a648c8 78%, #7b4cf8 100%);"></button>
                <button type="button" class="perfil-color-option" data-preset="aurora" aria-label="Naranja, rosa y azul" style="--preset-gradient: linear-gradient(120deg, #ff8f6b 0%, #ff5e7a 36%, #7b5cff 72%, #3b8cff 100%);"></button>
                <button type="button" class="perfil-color-option" data-preset="ocean" aria-label="Azul y morado" style="--preset-gradient: linear-gradient(120deg, #2ec5f4 0%, #4d69ff 42%, #7a3ef2 78%, #b94cf5 100%);"></button>
                <button type="button" class="perfil-color-option" data-preset="berry" aria-label="Rosa y morado" style="--preset-gradient: linear-gradient(120deg, #ff7ba2 0%, #c44bff 46%, #6f4bff 100%);"></button>
                <button type="button" class="perfil-color-option" data-preset="mint" aria-label="Verde y azul" style="--preset-gradient: linear-gradient(120deg, #58d6c4 0%, #4aa6ff 42%, #7e56ff 100%);"></button>
                <button type="button" class="perfil-color-option" data-preset="violet" aria-label="Violeta" style="--preset-gradient: linear-gradient(120deg, #9f7bff 0%, #6d4efc 48%, #4b5dff 100%);"></button>
                <button type="button" class="perfil-color-option" data-preset="ember" aria-label="Coral y dorado" style="--preset-gradient: linear-gradient(120deg, #ff8a5c 0%, #ff6b6b 45%, #ffc857 100%);"></button>
                <button type="button" class="perfil-color-option" data-preset="seafoam" aria-label="Turquesa y lavanda" style="--preset-gradient: linear-gradient(120deg, #42d7c8 0%, #5b7cff 45%, #9c7cff 100%);"></button>
                <button type="button" class="perfil-color-option" data-preset="lilac" aria-label="Lila intenso" style="--preset-gradient: linear-gradient(120deg, #c084fc 0%, #8b5cf6 45%, #4f46e5 100%);"></button>
                <button type="button" class="perfil-color-option" data-preset="scarlet" aria-label="Rojo y carmín" style="--preset-gradient: linear-gradient(120deg, #ff6b6b 0%, #ef4444 45%, #7c2d12 100%);"></button>
            </div>
        </div>

        <div class="perfil-actividad-card">
            <h2 style="margin-bottom:16px;">Mi actividad reciente</h2>

            <input
                type="text"
                id="buscadorMovimientos"
                placeholder="Buscar en mis movimientos..."
                style="margin-bottom:16px;"
            >

            <div class="perfil-movimientos-tabla">
                <table>
                <thead>
                    <tr>
                        <th>Módulo</th>
                        <th>Acción</th>
                        <th>Descripción</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody id="tablaMovimientos">
                    <?php if (empty($movimientos)): ?>
                        <tr>
                            <td colspan="4" style="text-align:center; color:#999; padding:30px;">
                                No hay movimientos registrados aún.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($movimientos as $m): ?>
                        <tr>
                            <td><?= htmlspecialchars($m['modulo']) ?></td>
                            <td>
                                <span class="badge-accion badge-<?= $m['accion'] ?>">
                                    <?= ucfirst($m['accion']) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($m['descripcion']) ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($m['fecha'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        </div>

    </div>

</div>
</div>

<!-- /////////////////////////////////////////////////////// -->

<?php require_once __DIR__ . "/../layout/footer.php"; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    lucide.createIcons();
});

// Mostrar/ocultar contraseña
const inputPassword      = document.getElementById('password');
const btnMostrarPassword = document.getElementById('btnMostrarPassword');
let mostrando = false;

btnMostrarPassword.addEventListener('click', function () {
    mostrando = !mostrando;
    inputPassword.type = mostrando ? 'text' : 'password';
    btnMostrarPassword.innerHTML = mostrando
        ? '<i data-lucide="eye-off"></i>'
        : '<i data-lucide="eye"></i>';
    lucide.createIcons();
});

// Buscador de movimientos
document.getElementById('buscadorMovimientos').addEventListener('keyup', function () {
    const texto = this.value.toLowerCase();
    document.querySelectorAll('#tablaMovimientos tr').forEach(function (fila) {
        fila.style.display = fila.textContent.toLowerCase().includes(texto) ? '' : 'none';
    });
});

// Selector de color de fondo
const presetButtons = document.querySelectorAll('.perfil-color-option');
const savedPreset = localStorage.getItem('bgPreset');

function applyBackgroundPreset(preset) {
    if (preset) {
        document.documentElement.setAttribute('data-bg-preset', preset);
        localStorage.setItem('bgPreset', preset);
    } else {
        document.documentElement.removeAttribute('data-bg-preset');
        localStorage.removeItem('bgPreset');
    }

    presetButtons.forEach((button) => {
        button.classList.toggle('active', button.dataset.preset === preset);
    });
}

presetButtons.forEach((button) => {
    button.addEventListener('click', () => {
        applyBackgroundPreset(button.dataset.preset);
    });
});

if (savedPreset) {
    applyBackgroundPreset(savedPreset);
} else {
    applyBackgroundPreset('sunset');
}
</script>

</body>
</html>
