<?php

// Se incluye el archivo de configuración que define rutas absolutas (como HEADER, FOOTER, etc.)
require_once __DIR__ . '/../config/addressDocument.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Configuración de codificación y visualización responsiva -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Título de la página -->
    <title>Iniciar Sesión - New Wolf</title>

    <!-- Enlace al archivo de estilos CSS -->
    <link rel="stylesheet" href="/login_1/assets/styles/style.css">
</head>

<body>

    <!-- Contenedor general del formulario de inicio de sesión -->
    <div class="login-container-wrapper">
        <div class="contenedor-form">

            <!-- Título del formulario -->
            <h2>Iniciar Sesión</h2>

            <!-- Formulario para enviar usuario y contraseña -->
            <form action="/login_1/index.php" method="POST">

                <!-- Campo oculto que indica que esta acción es de login -->
                <input type="hidden" name="action" value="login">

                <!-- Campo para el nombre de usuario -->
                <div class="contenedor-input">
                    <span class="icono">👤</span>
                    <input type="text" name="username" id="username" required>
                    <label for="username">Usuario</label>
                </div>

                <!-- Campo para la contraseña -->
                <div class="contenedor-input">
                    <span class="icono">🔒</span>
                    <input type="password" name="password" id="password" required>
                    <label for="password">Contraseña</label>
                </div>

                <!-- Opción para recordar sesión y enlace para recuperar contraseña -->
                <div class="recordar">
                    <label><input type="checkbox"> Recuérdame</label>
                    <a href="/login_1/view/recuperarClave.php">¿Olvidaste tu contraseña?</a>
                </div>

                <!-- Botón para enviar el formulario -->
                <button type="submit" class="btn-login">Iniciar Sesión</button>

                <!-- Separador visual para inicio de sesión con redes sociales -->
                <div class="social-login-separator">O continuar con</div>

                <!-- Botones ficticios para redes sociales (no funcionales en este código) -->
                <div class="social-login-buttons">
                    <button type="button" class="social-btn google-btn">G</button>
                    <button type="button" class="social-btn facebook-btn">f</button>
                    <button type="button" class="social-btn github-btn"></button>
                </div>

                <!-- Enlace para redirigir al formulario de registro -->
                <div class="registrar">
                    <p>¿No tienes cuenta? <a href="view/register.php">Registrarse</a></p>
                </div>
            </form>
        </div>
    </div>
</body>

</html>