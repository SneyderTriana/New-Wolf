<!DOCTYPE html> <!-- Define el tipo de documento HTML5 -->
<html lang="en"> <!-- Establece el idioma del contenido como inglés -->

<head>
    <meta charset="UTF-8"> <!-- Codificación de caracteres UTF-8 -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge"> <!-- Compatibilidad con IE -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Configura el diseño responsivo -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">
    <!-- Importa Bootstrap 5 desde CDN -->
    <link rel="stylesheet" href="styles/style.css"> <!-- Enlace a hoja de estilos personalizada -->
    <title>Recuperar Contraseña</title> <!-- Título de la pestaña del navegador -->
</head>

<body class="text-center"> <!-- Cuerpo con texto centrado -->

    <main class="form-signin w-100 m-auto"> <!-- Contenedor principal centrado con ancho completo -->
        <form action="../config/change_password.php" method="POST">
            <!-- Formulario que envía datos por POST al script PHP -->
            <h1>New Wolf</h1> <!-- Título principal -->
            <h2 class="h3 mb-3 fw-normal">Establece tu nueva contraseña</h2> <!-- Subtítulo con estilo -->

            <div class="form-floating my-3"> <!-- Contenedor flotante con margen vertical -->
                <input type="password" class="form-control" id="floatingInput" name="new_password"
                    placeholder="Nueva contraseña" required> <!-- Campo para nueva contraseña -->
                <label for="floatingInput">Nueva contraseña</label> <!-- Etiqueta del campo -->
            </div>

            <input type="hidden" name="email" value="<?php echo htmlspecialchars($_GET['email'] ?? ''); ?>">
            <!-- Campo oculto que captura el email desde la URL -->
            <button class="w-100 btn btn-lg btn-primary" type="submit">Guardar nueva contraseña</button>
            <!-- Botón para enviar el formulario -->
        </form>
    </main>

</body>

</html>