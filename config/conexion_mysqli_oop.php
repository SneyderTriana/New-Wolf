<?php
if (!isset($mysqli)) {
    $host = 'localhost';
    $user = 'root';
    $password = '';
    $dbname = 'login_db';

    $mysqli = new mysqli($host, $user, $password, $dbname);

    if ($mysqli->connect_error) {
        die("Error MySQLi (orientado a objetos): " . $mysqli->connect_error);
    }

    $mysqli->set_charset("utf8");
}
