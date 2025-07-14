<?php
session_start();
header('Content-Type: application/json'); // ¡Importante para que JavaScript lo interprete como JSON!

$cantidad_final = 0; // Inicializa la variable

if (isset($_SESSION['carrito']) && !empty($_SESSION['carrito'])) {
    foreach ($_SESSION['carrito'] as $item) {
        $cantidad_final += intval($item['cantidad'] ?? 0);
    }
}

// Devuelve un JSON con el conteo
echo json_encode(['status' => 'OK', 'count' => $cantidad_final]);
exit;
?>