<?php
/** @var array<int, array<string, mixed>> $usuarios */
/** @var array<string, mixed>|null $usuarioEditar */
/** @var string|null $errorFormulario */
$formUsuario = $usuarioEditar ?? $_POST ?? [];
$esCuentaPropia = isset($usuarioEditar)
    && (int)$usuarioEditar['id'] === (int)$_SESSION['usuario']['id'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="icon" type="image/png" href="/hotel_inventario/assets/img/HLH_logo.png?">

    <title>Usuarios</title>
</head>

<body>

<?php require_once __DIR__ . "/../layout/header.php"; ?>

<!-- /////////////////////////////////////////////////////// -->

<div class="page-header"> 
    <h1>Lista de usuarios</h1> 
    <p>Administración de usuarios</p> 
</div>

<?php if (($_GET['error'] ?? '') === 'ultimo_admin'): ?>
    <div class="alerta-error">⚠ Debe permanecer al menos un administrador activo.</div>
<?php endif; ?>

<!-- /////////////////////////////////////////////////////// -->

<div class="container">

    <div class="inventario-topbar">

        <div class="buscador-wrapper">
        <input 
            type="text" 
            id="buscador" 
            placeholder="Buscar usuarios..."
            >
            <button type="button" id="btnLimpiarBusqueda" class="btn-limpiar-buscador" title="Limpiar búsqueda">
                <i data-lucide="x"></i>
            </button>
        </div>
        <?php if(
        in_array(
            $_SESSION['usuario']['rol'],
            ['admin']
        )
        ): ?>
        <button 
        class="btn-agregar"
        onclick="abrirModal()"
        >
            + Agregar usuario
        </button>
        <?php endif; ?>
    </div>

    <br>

<!-- /////////////////////////////////////////////////////// -->

    <table>

<thead>
<tr>
    <th hidden>ID</th>
    <th>Nombre</th>
    <th>Correo</th>
    <th>Rol</th>
    <th>Estado</th>
    <th>Último acceso</th>
    <th>Acciones</th>
</tr>
</thead>

<tbody>

<?php foreach($usuarios as $u): ?>

<tr id="usuario-<?= (int)$u['id'] ?>">

    <td hidden><?= $u['id'] ?></td>

    <td><?= htmlspecialchars($u['nombre']) ?></td>

    <td><?= htmlspecialchars($u['correo']) ?></td>

    <td><?= ucfirst($u['rol']) ?></td>

    <td>

    <?php if($u['activo']): ?>
        <span class="estado-badge estado-ok">
            Activo
        </span>
    <?php else: ?>
        <span class="estado-badge estado-faltante">
            Inactivo
        </span>
    <?php endif; ?>

    </td>

    <td>

        <?= $u['ultimo_login']
            ? date('d/m/Y H:i', strtotime($u['ultimo_login']))
            : 'Nunca'
        ?>

    </td>

    <td>
    <a href="index.php?modulo=usuarios&accion=editar&id=<?= $u['id'] ?>">
        Editar
    </a>

            <?php if($u['id'] != $_SESSION['usuario']['id']): ?>

                |

        <?php if($u['activo']): ?>

            <a href="#" class="btn-desactivar"
                data-url="index.php?modulo=usuarios&accion=desactivar&id=<?= (int)$u['id'] ?>"
                data-usuario="<?= htmlspecialchars($u['nombre']) ?>">
                Desactivar
            </a>

        <?php else: ?>

            <a href="index.php?modulo=usuarios&accion=activar&id=<?= $u['id'] ?>">
                Activar
            </a>

        <?php endif; ?>

    <?php endif; ?>
    </td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>
<?php require_once __DIR__ . "/../layout/footer.php"; ?>

<!--//////////-- Modal Articulo --//////////-->

<div class="modal-overlay" id="modalUsuario">

    <div class="modal">

        <div class="modal-header">

            <div>
                <h2>
                    <?= isset($usuarioEditar) 
                        ? 'Editar usuario' 
                        : 'Agregar usuario' ?>
                </h2>

                <p>
                    Completa la información del usuario
                </p>
                <p style="font-weight: 550; opacity: 40%;">
                    ⚠ (Los usuarios creados no se podrán eliminar por cuestiones de seguridad e integridad de datos)
                </p>
            </div>

            <button onclick="cerrarModal()">
                ✕
            </button>

        </div>

        <form 
        id="usuarioFormulario" 
        action="index.php?modulo=usuarios&accion=<?= isset($usuarioEditar) ? 'editar' : 'agregar' ?>" 
        method="POST">

        <?php if (isset($errorFormulario)): ?>
        <div class="alerta-error">
            ⚠ <?= htmlspecialchars($errorFormulario) ?>
        </div>
        <?php endif; ?>

            <input
                type="hidden"
                name="id"
                value="<?= htmlspecialchars($formUsuario['id'] ?? '') ?>"
            >

            <label>Nombre del usuario</label>

            <input
            type="text"
            name="nombre"
            required
            value="<?= htmlspecialchars($formUsuario['nombre'] ?? '') ?>"
            >

            <label>Correo</label>

            <input
            type="email"
            name="correo"
            required
            value="<?= htmlspecialchars($formUsuario['correo'] ?? '') ?>"
            >

            <?php if(!isset($usuarioEditar)): ?>

            <label>Contraseña</label>

            <input
            type="password"
            name="password"
            required
            >

            <?php endif; ?>

            <label>Rol</label>
            <?php if ($esCuentaPropia): ?>
                <input type="hidden" name="rol" value="<?= htmlspecialchars($formUsuario['rol']) ?>">
            <?php endif; ?>
            <select name="rol" required <?= $esCuentaPropia ? 'disabled' : '' ?>>
                <option value="">Selecciona un rol</option>

                <option value="admin"
                <?= ($formUsuario['rol'] ?? '') === 'admin'
                    ? 'selected'
                    : ''
                ?>>
                    Administrador
                </option>

                <option value="supervisor"
                <?= ($formUsuario['rol'] ?? '') === 'supervisor'
                    ? 'selected'
                    : ''
                ?>>
                    Supervisor
                </option>

                <option value="operador"
                <?= ($formUsuario['rol'] ?? '') === 'operador'
                    ? 'selected'
                    : ''
                ?>>
                    Operador
                </option>

            </select>

            <label>Estado</label>
            <?php if ($esCuentaPropia): ?>
                <input type="hidden" name="activo" value="<?= (int)$formUsuario['activo'] ?>">
            <?php endif; ?>
            <select name="activo" <?= $esCuentaPropia ? 'disabled' : '' ?>>

                <option value="1"
                <?= !isset($usuarioEditar) || !empty($formUsuario['activo'])
                    ? 'selected'
                    : ''
                ?>>
                    Activo
                </option>

                <option value="0"
                <?= isset($usuarioEditar) && empty($formUsuario['activo'])
                    ? 'selected'
                    : ''
                ?>>
                    Inactivo
                </option>

            </select>

            <?php if ($esCuentaPropia): ?>
                <p style="color:#777; font-size:13px;">Tu rol y estado solo pueden ser modificados por otro administrador.</p>
            <?php endif; ?>

            <div class="modal-buttons">

                <button type="submit" class="btn-agregar">
                    <?= isset($usuarioEditar) 
                        ? 'Guardar cambios' 
                        : 'Agregar usuario' ?>
                </button>

                <button 
                type="reset"
                onclick="cerrarModal()"
                >
                    Cancelar
                </button>

            </div>

        </form>

    </div>

</div>

<div class="modal-overlay" id="modalDesactivar">
    <div class="modal-confirmacion">
        <div class="modal-icono">⚠</div>
        <h2>Desactivar usuario</h2>
        <p id="mensajeDesactivar">¿Seguro que deseas desactivar este usuario?</p>
        <div class="modal-botones">
            <button type="button" onclick="cerrarModalDesactivar()">Cancelar</button>
            <a href="#" id="btnConfirmarDesactivar" class="btn-confirmar">Sí, desactivar</a>
        </div>
    </div>
</div>


<!-- /////////////////////////////////////////////////////// -->

<script>

const buscador = document.getElementById('buscador');
const btnLimpiarBusqueda = document.getElementById('btnLimpiarBusqueda');

buscador.addEventListener('input', function() {

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

<script>
const modalDesactivar = document.getElementById('modalDesactivar');
const mensajeDesactivar = document.getElementById('mensajeDesactivar');
const btnConfirmarDesactivar = document.getElementById('btnConfirmarDesactivar');

document.querySelectorAll('.btn-desactivar').forEach(boton => {
    boton.addEventListener('click', function(event) {
        event.preventDefault();
        mensajeDesactivar.textContent = `¿Seguro que deseas desactivar a "${this.dataset.usuario}"?`;
        btnConfirmarDesactivar.href = this.dataset.url;
        modalDesactivar.classList.add('active');
    });
});

function cerrarModalDesactivar() {
    modalDesactivar.classList.remove('active');
}
</script>

<!--//////////-- Modal Articulo --//////////-->

<script>

function abrirModal(){

    document
    .getElementById('modalUsuario')
    .classList
    .add('active');

    document.body.style.overflow = 'hidden';
}

function cerrarModal(){

    <?php if(isset($usuarioEditar)): ?>

        window.location.href = 'index.php?modulo=usuarios';

    <?php else: ?>

        document
        .getElementById('modalUsuario')
        .classList
        .remove('active');

        document.body.style.overflow = 'auto';

    <?php endif; ?>
}

<?php if(isset($usuarioEditar) || isset($errorFormulario)): ?>

abrirModal();

<?php endif; ?>

</script>

</body>
</html>
