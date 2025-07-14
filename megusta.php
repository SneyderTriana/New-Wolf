<?php
session_start();
include '../config/conexion.php'; // Asegúrate que esta ruta es correcta

header('Content-Type: application/json');

// Validar si hay sesión y datos requeridos
if (!isset($_SESSION['id']) || !isset($_POST['id'])) {
    echo json_encode(['error' => 'Datos incompletos']);
    exit;
}

$post = intval($_POST['id']); // Sanitiza directamente con intval
$usuario = intval($_SESSION['id']);

// Verificar si ya dio "me gusta"
$sql_check = "SELECT 1 FROM likes WHERE post = ? AND usuario = ?";
$stmt = $mysqli->prepare($sql_check);
$stmt->bind_param("ii", $post, $usuario);
$stmt->execute();
$stmt->store_result();
$count = $stmt->num_rows;
$stmt->close();

if ($count === 0) {
    // Insertar like
    $sql_insert = "INSERT INTO likes (usuario, post, fecha) VALUES (?, ?, NOW())";
    $stmt_insert = $mysqli->prepare($sql_insert);
    $stmt_insert->bind_param("ii", $usuario, $post);
    $stmt_insert->execute();
    $stmt_insert->close();

    // Incrementar contador
    $sql_update = "UPDATE publicaciones SET likes = likes + 1 WHERE id_pub = ?";
    $stmt_update = $mysqli->prepare($sql_update);
    $stmt_update->bind_param("i", $post);
    $stmt_update->execute();
    $stmt_update->close();

    $estado = "No me gusta";
} else {
    // Eliminar like
    $sql_delete = "DELETE FROM likes WHERE post = ? AND usuario = ?";
    $stmt_delete = $mysqli->prepare($sql_delete);
    $stmt_delete->bind_param("ii", $post, $usuario);
    $stmt_delete->execute();
    $stmt_delete->close();

    // Decrementar contador
    $sql_update = "UPDATE publicaciones SET likes = likes - 1 WHERE id_pub = ?";
    $stmt_update = $mysqli->prepare($sql_update);
    $stmt_update->bind_param("i", $post);
    $stmt_update->execute();
    $stmt_update->close();

    $estado = "Me gusta";
}

// Obtener cantidad actualizada de likes
$sql_likes = "SELECT likes FROM publicaciones WHERE id_pub = ?";
$stmt_likes = $mysqli->prepare($sql_likes);
$stmt_likes->bind_param("i", $post);
$stmt_likes->execute();
$result_likes = $stmt_likes->get_result();
$row = $result_likes->fetch_assoc();
$likes = $row['likes'];
$stmt_likes->close();

// Formar respuesta
$icon = "<i class='fa fa-thumbs-o-up'></i> ";
$likes_text = " ($likes)";
$megusta = $icon . $estado;

echo json_encode([
    'likes' => $likes_text,
    'text' => $megusta
]);
?>
