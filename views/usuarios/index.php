<?php
/** @var array<int, array<string, mixed>> $usuarios */
/** @var array<string, mixed>|null $usuarioEditar */
/** @var string|null $errorFormulario */
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

<!-- /////////////////////////////////////////////////////// -->

<div class="container">

    <div class="inventario-topbar">

        <input 
        type="text" 
        id="buscador" 
        placeholder="Buscar usuarios..."
        >
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

<tr id="usuario-<?= $u['id'] ?>">

    <td hidden><?= $u['id'] ?></td>

    <td><?= $u['nombre'] ?></td>

    <td><?= $u['correo'] ?></td>

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

            <a href="index.php?modulo=usuarios&accion=desactivar&id=<?= $u['id'] ?>">
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
                value="<?= $usuarioEditar['id'] ?? '' ?>"
            >

            <label>Nombre del usuario</label>

            <input
            type="text"
            name="nombre"
            required
            value="<?= $usuarioEditar['nombre'] ?? '' ?>"
            >

            <label>Correo</label>

            <input
            type="email"
            name="correo"
            required
            value="<?= $usuarioEditar['correo'] ?? '' ?>"
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
            <select name="rol" required>
                <option value="">Selecciona un rol</option>

                <option value="admin"
                <?= isset($usuarioEditar) && $usuarioEditar['rol'] === 'admin'
                    ? 'selected'
                    : ''
                ?>>
                    Administrador
                </option>

                <option value="supervisor"
                <?= isset($usuarioEditar) && $usuarioEditar['rol'] === 'supervisor'
                    ? 'selected'
                    : ''
                ?>>
                    Supervisor
                </option>

                <option value="operador"
                <?= isset($usuarioEditar) && $usuarioEditar['rol'] === 'operador'
                    ? 'selected'
                    : ''
                ?>>
                    Operador
                </option>

            </select>

            <label>Estado</label>
            <select name="activo">

                <option value="1"
                <?= !isset($usuarioEditar) || $usuarioEditar['activo']
                    ? 'selected'
                    : ''
                ?>>
                    Activo
                </option>

                <option value="0"
                <?= isset($usuarioEditar) && !$usuarioEditar['activo']
                    ? 'selected'
                    : ''
                ?>>
                    Inactivo
                </option>

            </select>

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


<!-- /////////////////////////////////////////////////////// -->

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
