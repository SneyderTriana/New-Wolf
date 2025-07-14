<?php

require_once "controller/UsuarioController.php";

$controller = new UsuarioController();

$username = "nuevo_usuario";
$password = "clave_secreta";

if ($controller->registrar($username, $password)) {
    echo "Usuario registrado correctamente.";
} else {
    echo "Eror al registrar el usuario";
}
?>