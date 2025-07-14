<?php
if (!class_exists('ConexionPDO')) {
    class ConexionPDO {
        public $conn;

        public function __construct() {
            $host = 'localhost';
            $dbname = 'login_db';
            $user = 'root';
            $password = '';

            try {
                $this->conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->conn->exec("SET NAMES utf8");
            } catch (PDOException $e) {
                die("Error en la conexión PDO: " . $e->getMessage());
            }
        }
    }
}
