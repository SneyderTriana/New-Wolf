<?php
// Ensure your config.php establishes a MySQLi connection, e.g.:
// $mysqli = new mysqli("localhost", "username", "password", "database_name");
// if ($mysqli->connect_errno) {
//     echo "Failed to connect to MySQL: " . $mysqli->connect_error;
//     exit();
// }
require('lib/config.php');

// Assume $mysqli is the established database connection from config.php
// If config.php doesn't establish it, you'll need to do it here.

// 1. Get and sanitize input using prepared statements
$usuario = $_POST['usuario'] ?? null;
$comentario = $_POST['comentario'] ?? null;
$publicacion = $_POST['publicacion'] ?? null;

// Basic validation: Ensure all necessary data is present
if ($usuario === null || $comentario === null || $publicacion === null) {
    // Handle missing data, e.g., redirect or show an error
    die("Error: Faltan datos necesarios para el comentario.");
}

// First: Insert the comment
$stmt_comment = $mysqli->prepare("INSERT INTO comentarios (usuario, comentario, fecha, publicacion) VALUES (?, ?, NOW(), ?)");

if (false === $stmt_comment) {
    die("Error al preparar la consulta de inserción de comentario: " . $mysqli->error);
}

// 'sss' indicates all three parameters are strings
$stmt_comment->bind_param("ssi", $usuario, $comentario, $publicacion); // Assuming publicacion is an integer (i)

if (!$stmt_comment->execute()) {
    die("Error al ejecutar la consulta de inserción de comentario: " . $stmt_comment->error);
}
$stmt_comment->close();

// Second: Get the owner of the publication
$stmt_pub_owner = $mysqli->prepare("SELECT usuario FROM publicaciones WHERE id_pub = ?");

if (false === $stmt_pub_owner) {
    die("Error al preparar la consulta para obtener el usuario de la publicación: " . $mysqli->error);
}

$stmt_pub_owner->bind_param("i", $publicacion); // 'i' indicates an integer
$stmt_pub_owner->execute();
$result_pub_owner = $stmt_pub_owner->get_result();
$ll = $result_pub_owner->fetch_assoc();
$stmt_pub_owner->close();

$usuario2 = $ll['usuario'] ?? null; // Get the owner's ID

if ($usuario2 === null) {
    // Handle case where publication owner isn't found
    // This might mean the 'publicacion' ID was invalid
    die("Error: No se encontró el usuario de la publicación.");
}

// Third: Insert the notification
// Only insert if the commenter is not the same as the publication owner
// (to avoid notifying yourself about your own comment)
if ($usuario !== $usuario2) {
    $stmt_notification = $mysqli->prepare("INSERT INTO notificaciones (user1, user2, tipo, leido, fecha, id_pub) VALUES (?, ?, 'ha comentado', '0', NOW(), ?)");

    if (false === $stmt_notification) {
        die("Error al preparar la consulta de inserción de notificación: " . $mysqli->error);
    }

    // 'ssi' indicates user1 (string), user2 (string), id_pub (integer)
    $stmt_notification->bind_param("ssi", $usuario, $usuario2, $publicacion);

    if (!$stmt_notification->execute()) {
        die("Error al ejecutar la consulta de inserción de notificación: " . $stmt_notification->error);
    }
    $stmt_notification->close();
}

// You might want to add a success message or redirect here
// echo "Comentario y notificación guardados exitosamente.";

// It's good practice to close the main connection if it was opened in config.php
// and you're done with all database operations.
// $mysqli->close();
?>