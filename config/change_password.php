<?php
// C:\xampp\htdocs\login_1\config\change_password.php

// Mostrar todos los errores de PHP para depuración (SOLO EN DESARROLLO)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir la conexión a la base de datos
// Asegúrate de que 'conexion.php' contenga la variable $conexion correctamente inicializada.
require_once('conexion.php');

// 1. Verificar que los datos necesarios han sido enviados por POST
// Esperamos 'email' y 'new_password'
if (isset($_POST['email']) && isset($_POST['new_password'])) {
    // 2. Obtener y sanear los datos
    $email = trim($_POST['email']); // Trim para eliminar espacios en blanco al inicio/final
    $new_password = $_POST['new_password'];

    // Depuración: Verifica qué valores se están recibiendo
    error_log("DEBUG_CHANGE_PASSWORD: Recibido Email = " . $email);
    error_log("DEBUG_CHANGE_PASSWORD: Recibida new_password (sin hashear) = " . $new_password);

    // 3. Hashear la nueva contraseña (¡CRUCIAL para seguridad!)
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // Depuración: Verifica la contraseña hasheada
    error_log("DEBUG_CHANGE_PASSWORD: Contraseña hasheada = " . $hashed_password);

    // 4. Preparar la consulta SQL para actualizar la contraseña por email
    // Es CRÍTICO que la columna 'correo' en tu tabla 'usuarios' sea UNIQUE.
    $stmt = $conexion->prepare("UPDATE usuarios SET password = ? WHERE correo = ?");

    // Verificar si la preparación de la consulta fue exitosa
    if ($stmt === false) {
        error_log("DEBUG_CHANGE_PASSWORD: Error al preparar la consulta: " . $conexion->error);
        header("Location: ../view/recuperarClave.php?message=prepare_failed");
        exit();
    }

    // 5. Vincular los parámetros a la sentencia preparada
    // "ss" porque ambos parámetros son strings (contraseña hasheada y email)
    $stmt->bind_param("ss", $hashed_password, $email);

    // 6. Ejecutar la consulta
    if ($stmt->execute()) {
        error_log("DEBUG_CHANGE_PASSWORD: Consulta ejecutada con éxito.");
        error_log("DEBUG_CHANGE_PASSWORD: Filas afectadas por UPDATE: " . $stmt->affected_rows);

        // Si 0 filas afectadas, el email no fue encontrado o no se actualizó nada
        if ($stmt->affected_rows === 0) {
            error_log("DEBUG_CHANGE_PASSWORD: WARNING - No se encontró usuario con Email '" . $email . "' o la contraseña era la misma.");
            // Podrías redirigir con un mensaje diferente si quieres diferenciar entre "no encontrado" y "misma contraseña"
            header("Location: ../view/recuperarClave.php?message=user_not_found");
            $stmt->close();
            exit();
        }

        // Contraseña actualizada con éxito
        header("Location: /login_1/view/login.php?message=success_password");
        $stmt->close();
        exit();
    } else {
        // Error al ejecutar la consulta (ej. problema con la DB, permisos)
        error_log("DEBUG_CHANGE_PASSWORD: Error al ejecutar UPDATE: " . $stmt->error);
        header("Location: ../view/recuperarClave.php?message=db_update_error");
        $stmt->close();
        exit();
    }
} else {
    // Datos no enviados correctamente (falta email o new_password en $_POST)
    error_log("DEBUG_CHANGE_PASSWORD: Datos POST faltantes (email o new_password). Esto indica un problema con el formulario HTML o la URL de acceso.");
    header("Location: ../view/recuperarClave.php?message=missing_data");
    exit();
}
?>