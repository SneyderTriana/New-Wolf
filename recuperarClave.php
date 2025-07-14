<?php
// Se incluye el archivo que define rutas absolutas como HEADER o FOOTER
require_once __DIR__ . '/../config/addressDocument.php';
?>

<!-- Enlace al archivo de estilos (está vacío, se debe especificar la ruta si se quiere aplicar estilos) -->
<link rel="stylesheet" href="">

<body>

    <!-- Contenedor principal del formulario -->
    <div class="contenedor-form">
        <!-- Título del formulario -->
        <h2>Recuperar Comtraseña</h2>

        <!-- Formulario que envía el correo del usuario para recuperación -->
        <form action="../config/recovery.php" method="POST">

            <!-- Campo oculto para indicar el tipo de acción (aunque aquí se pone 'login', parece que debería ser 'recovery') -->
            <input type="hidden" name="action" value="login">

            <!-- Campo de entrada para el correo electrónico -->
            <div class="contenedor-input">
                <span class="icono">👤</span>
                <input type="email" name="email" required>
                <label for="mail">Correo</label>
            </div>

            <br>

            <!-- Botón para enviar el formulario -->
            <button type="submit" class="btn">Recuperar Contraseña</button>

            <!-- Enlace para redirigir al formulario de registro -->
            <div class="registrar">
                <p>¿No tienes cuenta? <a href="register.php">Registrarse</a></p>
            </div>
        </form>
    </div>

</body>

</html>