<!-- ////////////// MODAL INVENTARIO //////////////////////////////// -->       
       </div>

        <div class="modal-overlay" id="modalInventario">

    <div class="modal">

        <div class="modal-header">

            <h2>

                <?= isset($inventarioEditar)
                    ? 'Editar inventario'
                    : 'Agregar inventario'
                ?>

            </h2>

            <button onclick="cerrarModal()">✕</button>
        
</div>

        <form
        id="inventarioFormulario"
        action="index.php?modulo=inventario&accion=<?= isset($inventarioEditar) ? 'editar' : 'agregar' ?>"
        method="POST">

            <input
            type="hidden"
            name="id"
            value="<?= $inventarioEditar['id'] ?? '' ?>"
            >

            <label>Habitación</label>
            <select name="habitacion_id">
                <option value="">Seleccione una habitación</option>
                <?php foreach($habitaciones as $h): ?>

                    <option
                    value="<?= $h['id'] ?>"

                    <?= isset($inventarioEditar)
                        && $inventarioEditar['habitacion_id'] == $h['id']
                        ? 'selected'
                        : ''
                    ?>
                    >
                        Habitación <?= $h['numero'] ?>
                    </option>

                <?php endforeach; ?>
            </select>
            <label>Artículo</label>
            <select name="articulo_id">

                <option value="">Seleccione un artículo</option>
                <?php foreach($articulos as $a): ?>

                    <option
                    value="<?= $a['id'] ?>"

                    <?= isset($inventarioEditar)
                        && $inventarioEditar['articulo_id'] == $a['id']
                        ? 'selected'
                        : ''
                    ?>

                    >
                        <?= $a['nombre'] ?>
                    </option>

                <?php endforeach; ?>

            </select>

            <label>Cantidad</label>

            <input
            type="number"
            name="cantidad"
            min="0"
            required
            value="<?= $inventarioEditar['cantidad'] ?? '' ?>"
            >

            <label>Estado</label>

            <select name="estado">

                <option value="bueno">Bueno</option>
                <option value="dañado">Dañado</option>
                <option value="en_reparacion">En reparación</option>
                <option value="perdido">Perdido</option>

            </select>

            <label>Comentarios</label>

            <input
            type="text"
            name="comentarios"
            value="<?= $inventarioEditar['comentarios'] ?? '' ?>"
            >

            <div class="modal-buttons">

                <button type="submit">

                    Guardar

                </button>

                <button
                type="button"
                onclick="cerrarModal()">

                    Cancelar

                </button>
            </div>
        </form>
    </div>
</div>

    </main>

</div>

<!-- //////////////////// SIDEBAR //////////////////////////////// -->    

<script>

lucide.createIcons();

const sidebar = document.getElementById("sidebar");
const layout = document.querySelector(".layout");
const toggleBtn = document.getElementById("toggleSidebar");

/* ===== CARGAR ESTADO ===== */

if(localStorage.getItem("sidebarCollapsed") === "true"){

    sidebar.classList.add("collapsed");

    layout.classList.add("sidebar-collapsed");
}

/* ===== QUITAR PRELOAD ===== */

window.addEventListener("load", () => {

    document.documentElement.classList.remove("sidebar-preload");

});

/* ===== TOGGLE ===== */

toggleBtn.addEventListener("click", () => {

    sidebar.classList.toggle("collapsed");

    layout.classList.toggle("sidebar-collapsed");

    /* cerrar dropdown */

    userDropdown.classList.remove("active");

    localStorage.setItem(
        "sidebarCollapsed",
        sidebar.classList.contains("collapsed")
    );

});
</script>


<!--/////////////////// USER SIDE BAR  //////////////////////////-->

<script>

const userMenuBtn = document.getElementById("userMenuBtn");
const userDropdown = document.getElementById("userDropdown");

if(userMenuBtn){

    userMenuBtn.addEventListener("click", () => {

    /* NO abrir si sidebar está colapsada */

        if(sidebar.classList.contains("collapsed")){
            return;
        }
        userDropdown.classList.toggle("active");
    });

    // cerrar si se hace click afuera
    document.addEventListener("click", function(e){

        if(
            !userMenuBtn.contains(e.target) &&
            !userDropdown.contains(e.target)
        ){

            userDropdown.classList.remove("active");

        }

    });

}

</script>

</body>
</html>