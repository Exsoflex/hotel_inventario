<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/hotel_inventario/assets/css/styles.css">
    <link rel="icon" type="image/png" href="/hotel_inventario/assets/img/HLH_logo.png?">
    <script>
    (function(){
        var savedTheme = localStorage.getItem("theme");
        if(savedTheme === "dark" || (!savedTheme && window.matchMedia("(prefers-color-scheme: dark)").matches)){
            document.documentElement.setAttribute("data-theme", "dark");
        }
    })();
    </script>

    <title>Iniciar sesión</title>

</head>

<body class="login-body">

<div class="login-wrapper">

    <!-- PANEL IZQUIERDO -->
    <div class="login-info">

        <h1>Inventario del Hotel</h1>

        <p>
            Sistema de gestión e inventario hotelero.
        </p>

        <div class="login-info-box">

            <p>🏨 Control de habitaciones</p>
            <p>📦 Gestión de inventario</p>
            <p>🔐 Acceso seguro con usuarios</p>

        </div>

    </div>

    <!-- PANEL DERECHO -->
    <div class="login-container">

        <div class="login-card">

            <h2>Bienvenido</h2>

            <p class="login-subtitle">
                Inicia sesión para continuar
            </p>

            <?php if(isset($_GET['error'])): ?>

                <div class="alerta-error">

                    Usuario o contraseña incorrectos

                </div>

            <?php endif; ?>

            <form 
                action="/hotel_inventario/index.php?modulo=auth&accion=login" 
                method="POST">

                <label>Usuario o correo</label>

                <input
                    type="text"
                    name="login"
                    placeholder="Ingresa tu usuario o correo"
                    required
                >

                <label>Contraseña</label>

                <input
                    type="password"
                    name="password"
                    placeholder="Ingresa tu contraseña"
                    required
                >

                <button type="submit" class="btn-login">
                    Iniciar sesión
                </button>

            </form>

        </div>
    </div>
</div>

</body>
</html>