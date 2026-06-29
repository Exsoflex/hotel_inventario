</div>

<!-- //////////////////// SIDEBAR //////////////////////////////// -->    

<script>

lucide.createIcons();

const sidebar = document.getElementById("sidebar");
const layout = document.querySelector(".layout");
const toggleBtn = document.getElementById("toggleSidebar");
const sidebarOverlay = document.getElementById("sidebarOverlay");
const MOBILE_BREAKPOINT = 1024;

function isMobileSidebar(){
    return window.innerWidth <= MOBILE_BREAKPOINT;
}

function openMobileSidebar(){
    sidebar.classList.add("mobile-open");
    sidebarOverlay?.classList.add("active");
    document.body.classList.add("sidebar-mobile-open");
}

function closeMobileSidebar(){
    sidebar.classList.remove("mobile-open");
    sidebarOverlay?.classList.remove("active");
    document.body.classList.remove("sidebar-mobile-open");
}

function applyDesktopSidebarState(){
    if(localStorage.getItem("sidebarCollapsed") === "true"){
        sidebar.classList.add("collapsed");
        layout.classList.add("sidebar-collapsed");
    }
}

/* ===== CARGAR ESTADO ===== */

if(!isMobileSidebar()){
    applyDesktopSidebarState();
}

/* ===== QUITAR PRELOAD ===== */

window.addEventListener("load", () => {
    document.documentElement.classList.remove("sidebar-preload");
});

/* ===== TOGGLE ===== */

toggleBtn.addEventListener("click", () => {

    userDropdown?.classList.remove("active");

    if(isMobileSidebar()){
        if(sidebar.classList.contains("mobile-open")){
            closeMobileSidebar();
        }else{
            openMobileSidebar();
        }
        return;
    }

    sidebar.classList.toggle("collapsed");
    layout.classList.toggle("sidebar-collapsed");

    localStorage.setItem(
        "sidebarCollapsed",
        sidebar.classList.contains("collapsed")
    );
});

sidebarOverlay?.addEventListener("click", closeMobileSidebar);

sidebar.querySelectorAll(".sidebar-nav a").forEach((link) => {
    link.addEventListener("click", () => {
        if(isMobileSidebar()){
            closeMobileSidebar();
        }
    });
});

window.addEventListener("resize", () => {
    if(isMobileSidebar()){
        sidebar.classList.remove("collapsed");
        layout.classList.remove("sidebar-collapsed");
    }else{
        closeMobileSidebar();
        sidebar.classList.remove("collapsed");
        layout.classList.remove("sidebar-collapsed");
        applyDesktopSidebarState();
    }
});
</script>


<script>
// La animación CSS se reinicia naturalmente en navegación tradicional
// El keyframe está diseñado para que 0% y 100% sean idénticos (reinicio suave)
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

<!--/////////////////// THEME TOGGLE //////////////////////////-->

<script>

const themeToggle = document.getElementById("themeToggle");

function setTheme(theme){
    if(theme === "dark"){
        document.documentElement.setAttribute("data-theme", "dark");
    }else{
        document.documentElement.removeAttribute("data-theme");
    }
    localStorage.setItem("theme", theme);
}

if(themeToggle){
    themeToggle.addEventListener("click", (e) => {
        e.stopPropagation();
        const isDark = document.documentElement.getAttribute("data-theme") === "dark";
        setTheme(isDark ? "light" : "dark");
        lucide.createIcons();
    });
}

</script>

<script>
window.addEventListener('pageshow', function(event) {

    if (
        event.persisted ||
        window.performance.navigation.type === 2
    ) {

        window.location.reload();
    }
});
</script>

</body>
</html>