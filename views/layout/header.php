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
    (function(){
        if(localStorage.getItem("sidebarCollapsed") === "true"){
            document.documentElement.classList.add("sidebar-preload");
        }
        var savedTheme = localStorage.getItem("theme");
        if(savedTheme === "dark" || (!savedTheme && window.matchMedia("(prefers-color-scheme: dark)").matches)){
            document.documentElement.setAttribute("data-theme", "dark");
        }
    })();
    </script>
    <link rel="icon" type="image/png" href="/hotel_inventario/assets/img/HLH_logo.png?v=1">


    <title>Hotel Inventario</title>
</head>

<body>

<!-- ///////////////////////////////////////-->

<div class="layout">

<!-- ///////////////////////////////////////-->
<div class="topbar">

    <div class="topbar-left">

        <button id="toggleSidebar" class="menu-btn">
            <i data-lucide="menu"></i>
        </button>
        <a href="index.php?modulo=dashboard">
            <img src="assets/img/HLH_logo.png"
                 alt="logo"
                 class="topbar-logo">
        </a>

    </div>

    <?php if(isset($_SESSION['usuario'])): ?>

    <div class="topbar-right">

        <button class="user-menu-btn" id="userMenuBtn">

            <div class="user-avatar">
                <?= strtoupper(substr($_SESSION['usuario']['nombre'], 0, 1)) ?>
            </div>

            <div class="user-info">

                <strong>
                    <?= $_SESSION['usuario']['nombre'] ?>
                </strong>

                <span>
                    <?= $_SESSION['usuario']['rol'] ?>
                </span>

            </div>

            <i data-lucide="chevron-down"></i>

        </button>

        <div class="user-dropdown" id="userDropdown">

            <a href="index.php?modulo=perfil">
                <i data-lucide="user"></i>
                Perfil
            </a>

            <button type="button" id="themeToggle" class="dropdown-theme-btn" aria-label="Cambiar tema">
                <i data-lucide="moon" class="icon-theme-dark"></i>
                <i data-lucide="sun" class="icon-theme-light"></i>
                <span class="theme-label-dark">Modo oscuro</span>
                <span class="theme-label-light">Modo claro</span>
            </button>

            <a href="index.php?modulo=auth&accion=logout">
                <i data-lucide="log-out"></i>
                Cerrar sesión
            </a>

        </div>

    </div>

    <?php endif; ?>

</div>
<!-- ///////////////////////////////////////-->

    <!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">
<!--------------------------------->
<div class="sidebar-content">

        <nav class="sidebar-nav">

            <a href="index.php?modulo=dashboard">
                <i data-lucide="layout-dashboard"></i>
                <span>Inicio</span>
            </a>

            <a href="index.php?modulo=revision">
                <i data-lucide="search"></i>
                <span>Revisión</span>
            </a>

            <a href="index.php?modulo=inventario">
                <i data-lucide="boxes"></i>
                <span>Inventario</span>
            </a>

            <a href="index.php?modulo=historial_codigos">
                <i data-lucide="barcode"></i>
                <span>Códigos de Barras</span>
            </a>

<br><br>
            <?php if(
            in_array(
                $_SESSION['usuario']['rol'],
                ['admin']
            )
            ): ?>
            <a href="index.php?modulo=movimientos">
                <i data-lucide="logs"></i>
                <span>Movimientos</span>
            </a>
            <?php endif; ?>

            <a href="index.php?modulo=habitaciones">
                <i data-lucide="bed-double"></i>
                <span>Habitaciones</span>
            </a>

            <?php if(
            in_array(
                $_SESSION['usuario']['rol'],
                ['admin', 'supervisor']
            )
            ): ?>
            <a href="index.php?modulo=articulos">
                <i data-lucide="package"></i>
                <span>Artículos</span>
            </a>

            <a href="index.php?modulo=inventario_base">
                <i data-lucide="clipboard-list"></i>
                <span>Inventario Base</span>
            </a>
            <?php endif; ?>

            <?php if(
            in_array(
                $_SESSION['usuario']['rol'],
                ['admin']
            )
            ): ?>
            <a href="index.php?modulo=usuarios">
                <i data-lucide="users"></i>
                <span>Usuarios</span>
            </a>
            <?php endif; ?>
            
        </nav>
<!--------------------------------->
</aside>

<div class="sidebar-overlay" id="sidebarOverlay" aria-hidden="true"></div>

    <!-- CONTENIDO -->
    <main class="main-content">

        <div class="container-base">