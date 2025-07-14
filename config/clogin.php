<?php
// C:\xampp\htdocs\login_1\config\login.php

// Mostrar todos los errores de PHP para depuración (SOLO EN DESARROLLO)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Asegúrate de que 'conexion.php' carga tu conexión a la base de datos ($conexion)
// La ruta es relativa a la ubicación de este archivo (config/login.php)
require_once('conexion.php');

// 1. Verificar si email y password están presentes en $_POST
if (isset($_POST['email']) && isset($_POST['password'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // --- IMPORTANTE: Hashing de Contraseñas y Sentencias Preparadas ---

    // La consulta SQL original intentaba unirse con la tabla 'roles'.
    // Según tu phpMyAdmin, la tabla 'roles' no existe y la columna 'rol_id' no está en 'usuarios'.
    // Por lo tanto, se elimina la unión y la selección del rol.
    // Consulta para obtener el usuario por email
    $query = "SELECT id, correo, password, status FROM usuarios WHERE correo = ?";

    // Preparar la consulta para seguridad y evitar inyección SQL
    $stmt = $conexion->prepare($query);

    // Verificar si la preparación de la consulta fue exitosa
    if ($stmt === false) {
        error_log("Error al preparar la consulta de login: " . $conexion->error);
        header("Location: /login_1/index.php?login_error=prepare_failed");
        exit();
    }

    // Vincular el parámetro email a la sentencia preparada
    // "s" indica que $email es un string
    $stmt->bind_param("s", $email);

    // Ejecutar la consulta
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    // Cerrar la sentencia preparada
    $stmt->close();

    // 2. Verificar el resultado y comparar la contraseña
    // ¡Es CRUCIAL que las contraseñas en tu base de datos estén HASHEADAS!
    // Si no lo están, password_verify() siempre fallará.
    // Si no hasheas las contraseñas, tu sistema es MUY INSEGURO.
    if ($row && $row['status'] == 1 && password_verify($password, $row['password'])) {
        session_start();
        $_SESSION['user'] = $email;
        // La línea para $_SESSION['rol'] se eliminó porque la tabla 'roles' y la columna 'rol_id' no existen.
        header("Location: /login_1/bienvenida.php"); // Ruta absoluta a la página de bienvenida
        exit(); // Siempre sal después de un header() para asegurar la redirección
    } else {
        // Credenciales incorrectas o usuario inactivo
        // Puedes redirigir de nuevo a index.php con un mensaje de error
        header("Location: /login_1/index.php?login_error=invalid_credentials");
        exit();
    }

} else {
    // Si email o password no están definidos en $_POST (ej. acceso directo al script)
    header("Location: /login_1/index.php?login_error=missing_fields");
    exit();
}
?>