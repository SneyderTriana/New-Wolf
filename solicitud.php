<?php
session_start();
include 'lib/config.php'; // Asegúrate de que este archivo contiene la conexión $mysqli
include 'lib/socialnetwork-lib.php'; // Asegúrate de que este archivo no use funciones mysql_*

// Muestra errores para depuración (¡desactívalo en producción!)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Verifica si la conexión a la base de datos existe y es válida
if (!isset($mysqli) || $mysqli->connect_error) {
    // Es vital que lib/config.php establezca la conexión $mysqli
    die("Error de conexión a la base de datos: " . (isset($mysqli) ? $mysqli->connect_error : "La conexión a MySQLi no está definida en lib/config.php"));
}

// Redirige si el usuario no está logueado
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit(); // Es importante usar exit() después de header()
}

// Procesa las acciones de solicitud de amistad
if (isset($_GET['id']) && isset($_GET['action'])) {
    // Sanea y valida las entradas GET
    $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    $action = htmlspecialchars($_GET['action']); // Sanea la acción

    if ($id === false || $id <= 0) {
        // Manejo de error si el ID no es un entero válido o es menor o igual a cero
        error_log("Intento de acceso con ID de amigo inválido: " . $_GET['id']);
        // Podrías redirigir o mostrar un mensaje de error al usuario
        // header("Location: error_page.php?msg=invalid_id");
        // exit();
    } else {
        $query_success = false;

        if ($action === 'aceptar') {
            // Prepara la sentencia para aceptar la solicitud de amistad
            $stmt = $mysqli->prepare("UPDATE amigos SET estado = '1' WHERE id_ami = ?");
            if ($stmt) {
                $stmt->bind_param("i", $id); // 'i' indica que $id es un entero
                if ($stmt->execute()) {
                    $query_success = true;
                } else {
                    error_log("Error al aceptar amigo con ID: $id - " . $stmt->error);
                    // Manejo de error: podrías mostrar un mensaje al usuario
                }
                $stmt->close();
            } else {
                error_log("Error al preparar la sentencia para aceptar amigo: " . $mysqli->error);
            }
        } elseif ($action === 'rechazar') {
            // Prepara la sentencia para rechazar la solicitud de amistad
            $stmt = $mysqli->prepare("DELETE FROM amigos WHERE id_ami = ?");
            if ($stmt) {
                $stmt->bind_param("i", $id); // 'i' indica que $id es un entero
                if ($stmt->execute()) {
                    $query_success = true;
                } else {
                    error_log("Error al rechazar amigo con ID: $id - " . $stmt->error);
                    // Manejo de error: podrías mostrar un mensaje al usuario
                }
                $stmt->close();
            } else {
                error_log("Error al preparar la sentencia para rechazar amigo: " . $mysqli->error);
            }
        } else {
            // Acción no reconocida
            error_log("Acción desconocida en solicitud de amistad: " . $action);
            // header("Location: error_page.php?msg=invalid_action");
            // exit();
        }

        // Si la consulta fue exitosa, redirige al usuario de vuelta a la página anterior
        if ($query_success) {
            // Es preferible usar un valor predeterminado si HTTP_REFERER no está disponible
            $referer = $_SERVER['HTTP_REFERER'] ?? 'index.php';
            header('Location: ' . $referer);
            exit();
        }
    }
}
?>