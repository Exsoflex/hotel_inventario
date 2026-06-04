<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@100..1000&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
    if(localStorage.getItem("sidebarCollapsed") === "true"){
        document.documentElement.classList.add("sidebar-preload");
    }
    </script>
    <title>Mi perfil</title>
</head>

<body>

<?php require_once __DIR__ . "/../layout/header.php"; ?>

<div class="page-header">
    <h1>Mi perfil</h1>
    <p>Administra tu información personal</p>
</div>

<div class="container" style="max-width: 520px;">

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

    <!-- Cabecera de perfil -->
    <div style="display:flex; align-items:center; gap:20px; margin-bottom:30px; padding-bottom:25px; border-bottom:1px solid #eee;">

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

    <!-- Formulario -->
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

            <button
                type="button"
                class="menu-btn"
                id="btnMostrarPassword"
            >
                <i data-lucide="eye"></i>
            </button>

        </div>

        <div style="display:flex; gap:12px; margin-top:10px;">
            <button type="submit" class="btn-agregar">
                Guardar cambios
            </button>
            <a href="index.php?modulo=dashboard" class="btn-cancelar" style="padding:14px 22px; border-radius:18px; font-weight:700;">
                Cancelar
            </a>
        </div>

    </form>

</div>

<?php require_once __DIR__ . "/../layout/footer.php"; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    lucide.createIcons();
});
</script>

<script>

lucide.createIcons();

const inputPassword =
    document.getElementById('password');

const btnMostrarPassword =
    document.getElementById('btnMostrarPassword');

let mostrando = false;

btnMostrarPassword.addEventListener('click', function(){

    mostrando = !mostrando;

    if(mostrando){

        inputPassword.type = 'text';

        btnMostrarPassword.innerHTML =
            '<i data-lucide="eye-off"></i>';

    }else{

        inputPassword.type = 'password';

        btnMostrarPassword.innerHTML =
            '<i data-lucide="eye"></i>';
    }

    lucide.createIcons();
});

</script>

</body>
</html>