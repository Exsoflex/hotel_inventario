<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="assets/css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@100..1000&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="icon" href="assets/img/HLH_logo.png" type="image/x-icon">

    <title>Hotel Inventario</title>
</head>

<body>

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
                <span>Dashboard</span>
            </a>

            <a href="index.php?modulo=revision">
                <i data-lucide="search"></i>
                <span>Revisión</span>
            </a>

            <a href="index.php?modulo=inventario">
                <i data-lucide="boxes"></i>
                <span>Inventario</span>
            </a>

<br><br>

            <a href="index.php?modulo=habitaciones">
                <i data-lucide="bed-double"></i>
                <span>Habitaciones</span>
            </a>

            <a href="index.php?modulo=articulos">
                <i data-lucide="package"></i>
                <span>Artículos</span>
            </a>

            <a href="index.php?modulo=inventario_base">
                <i data-lucide="clipboard-list"></i>
                <span>Inventario Base</span>
            </a>

        </nav>

    </aside>

    <!-- CONTENIDO -->
    <main class="main-content">

        <div class="container-base">