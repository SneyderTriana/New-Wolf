<?php
if (!isset($conexion)) {
    $host = 'localhost';
    $user = 'root';
    $password = '';
    $dbname = 'login_db';

    $conexion = mysqli_connect($host, $user, $password, $dbname);

    if (!$conexion) {
        die("Error MySQLi (procedural): " . mysqli_connect_error());
    }

    mysqli_set_charset($conexion, "utf8");
}
