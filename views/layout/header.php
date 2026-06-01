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
    <link rel="icon" href="assets/img/HLH_logo.png" type="image/x-icon">

    <title>Hotel Inventario</title>
</head>

<body>

<!-- ///////////////////////////////////////-->

<div class="layout">

    <!-- SIDEBAR -->
    <aside class="sidebar" id="sidebar">

        <div class="sidebar-top">

            <button id="toggleSidebar" class="menu-btn">
                <i data-lucide="menu"></i>
            </button>

            <span class="logo-text">
            <a href="index.php?modulo=dashboard">
            <img src="assets/img/HLH_logo.png" alt="logo" class="logo-img">
            </a>    
            </span>


        </div>

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

            <a href="index.php?modulo=movimientos">
                <i data-lucide="logs"></i>
                <span>Movimientos</span>
            </a>

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

    <?php if(
        in_array(
            $_SESSION['usuario']['rol'],
            ['operador']
        )
        ): ?>
<!-- //////////////////////////////////////////////////////////////////////////////// -->
    <?php endif; ?>

    <?php if(isset($_SESSION['usuario'])): ?>

    <div class="sidebar-user">

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

            <i data-lucide="chevron-up"></i>

        </button>

        <div class="user-dropdown" id="userDropdown">

            <a href="#">
                <i data-lucide="user"></i>
                Perfil
            </a>

            <a href="#">
                <i data-lucide="settings"></i>
                Configuración
            </a>

            <a href="index.php?modulo=auth&accion=logout">
                <i data-lucide="log-out"></i>

                Cerrar sesión

            </a>
        </div>
    </div>
    <?php endif; ?>

    </aside>

    <!-- CONTENIDO -->
    <main class="main-content">

        <div class="container-base">