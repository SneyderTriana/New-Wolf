<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Codificación de caracteres -->
    <meta charset="UTF-8">
    <!-- Configuración para que sea responsive en móviles -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Título que se mostrará en la pestaña del navegador -->
    <title>Registrar Usuario</title>
    <!-- Enlace al archivo CSS para aplicar estilos al formulario -->
    <link rel="stylesheet" href="/login_1/assets/styles/login.css">
</head>

<body>

    <!-- Encabezado principal -->
    <h1>Registrar Nuevo Usuario</h1>

    <!-- Formulario que envía los datos al archivo index.php para procesar el registro -->
    <form action="../index.php" method="POST">

        <!-- Campo oculto para indicar la acción que se desea realizar -->
        <input type="hidden" name="action" value="register">

        <!-- Campo para el nombre del usuario -->
        <label for="text">Nombre:</label>
        <input type="text" name="nombre" id="nombre" required>
        <br>

        <!-- (Repetido innecesariamente) Campo oculto para la acción -->
        <input type="hidden" name="action" value="register">

        <!-- Campo para el nombre de usuario (username) -->
        <label for="username">Usuario:</label>
        <input type="text" name="username" id="username" required>
        <br>

        <!-- (Repetido innecesariamente) Campo oculto para la acción -->
        <input type="hidden" name="action" value="register">

        <!-- Campo para el correo electrónico -->
        <label for="email">Correo:</label>
        <input type="email" name="correo" id="correo" required>
        <br>

        <!-- Campo para la contraseña -->
        <label for="password">contraseña: </label>
        <input type="password" name="password" id="password" required>
        <br>

        <!-- (Repetido innecesariamente) Campo oculto para la acción -->
        <input type="hidden" name="action" value="register">

        <!-- Campo para la fecha de nacimiento -->
        <label for="date">Fecha Nacimiento:</label>
        <input type="date" name="fechaNacimiento" id="fechaNacimiento" required>
        <br>

        <!-- (Repetido innecesariamente) Campo oculto para la acción -->
        <input type="hidden" name="action" value="register">

        <!-- Campo para el género del usuario -->
         <p>Genero</p>
         <select name="genero" id="genero">
            <option value="masculino">Masculino</option>
            <option value="femenino">Femenino</option>
            <option value="otro">Otro</option>
         </select>

        <br>

        <!-- Botón para enviar el formulario -->
        <button type="submit">Register</button>
    </form>

    <hr>

    <!-- Enlace para volver a la página de inicio de sesión -->
    <a href="../index.php">Volver al login</a>

</body>

</html>