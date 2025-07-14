<?php
session_start();
header('Content-Type: application/json'); // ¡Muy importante para que JavaScript lo lea como JSON!

// Inicializa el carrito si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

$response = ['status' => 'KO', 'message' => 'Petición inválida.']; // Respuesta por defecto

// Lógica para añadir un producto al carrito
if (isset($_POST['titulo']) && isset($_POST['precio']) && isset($_POST['cantidad']) && isset($_POST['ref'])) {
    $titulo = htmlspecialchars($_POST['titulo']);
    $precio = floatval($_POST['precio']);
    $cantidad = intval($_POST['cantidad']);
    $ref = htmlspecialchars($_POST['ref']);

    $encontrado = false;
    foreach ($_SESSION['carrito'] as $key => $item) {
        if (isset($item['ref']) && $item['ref'] == $ref) {
            $_SESSION['carrito'][$key]['cantidad'] += $cantidad;
            $encontrado = true;
            break;
        }
    }

    if (!$encontrado) {
        $_SESSION['carrito'][] = [
            "titulo" => $titulo,
            "precio" => $precio,
            "cantidad" => $cantidad,
            "ref" => $ref
        ];
    }
    $response = ['status' => 'OK', 'message' => 'Producto añadido al carrito.'];

}
// Lógica para actualizar la cantidad de un producto (desde modal_carrito.php)
// Se verifica 'id' (índice) y 'nueva_cantidad' para evitar conflictos con 'add'
elseif (isset($_POST['id_item_carrito']) && isset($_POST['nueva_cantidad'])) {
    $id = intval($_POST['id_item_carrito']); // Este es el índice del array en la sesión
    $nueva_cantidad = intval($_POST['nueva_cantidad']);

    if (isset($_SESSION['carrito'][$id])) {
        if ($nueva_cantidad < 1) { // Si la cantidad es 0 o menos, eliminar el item
            unset($_SESSION['carrito'][$id]);
            $_SESSION['carrito'] = array_values($_SESSION['carrito']); // Reindexar
            $response = ['status' => 'OK', 'message' => 'Producto eliminado del carrito.'];
        } else {
            $_SESSION['carrito'][$id]['cantidad'] = $nueva_cantidad;
            $response = ['status' => 'OK', 'message' => 'Cantidad actualizada.'];
        }
    } else {
        $response = ['status' => 'KO', 'message' => 'Artículo no encontrado en el carrito para actualizar.'];
    }
}
// Lógica para eliminar un producto específico por su ID (índice en el array)
elseif (isset($_POST['id_eliminar_item'])) {
    $id_eliminar = intval($_POST['id_eliminar_item']);
    if (isset($_SESSION['carrito'][$id_eliminar])) {
        unset($_SESSION['carrito'][$id_eliminar]);
        $_SESSION['carrito'] = array_values($_SESSION['carrito']); // Reindexar el array
        $response = ['status' => 'OK', 'message' => 'Producto eliminado del carrito.'];
    } else {
        $response = ['status' => 'KO', 'message' => 'Producto no encontrado para eliminar.'];
    }
}

echo json_encode($response);
exit;
?>