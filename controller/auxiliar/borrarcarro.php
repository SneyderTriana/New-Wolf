<?php
session_start();
header('Content-Type: application/json');

unset($_SESSION['carrito']); // Elimina la variable de sesión 'carrito'

echo json_encode(['status' => 'OK', 'message' => 'Carrito vaciado correctamente.']);
exit;
?>