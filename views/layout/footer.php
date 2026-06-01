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