<?php
require_once __DIR__ . '/../config/conexion_pdo.php';

class Usuario
{
    private $db;

    public function __construct()
    {
        $this->db = (new ConexionPDO())->conn;
    }

    // ====================
    // INICIAR SESIÓN
    // ====================
    public function login($username, $password)
    {
        $query = "SELECT * FROM usuarios WHERE username = :username";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($user && password_verify($password, $user['password'])) ? $user : false;
    }

    // ====================
    // REGISTRAR USUARIO
    // ====================
    public function registrar($username, $password, $nombre, $correo, $genero, $fechaNacimiento)
    {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $query = "INSERT INTO usuarios (username, password, nombre, correo, genero, fechaNacimiento) 
                  VALUES (:username, :password, :nombre, :correo, :genero, :fechaNacimiento)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":password", $hash);
        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":correo", $correo);
        $stmt->bindParam(":genero", $genero);
        $stmt->bindParam(":fechaNacimiento", $fechaNacimiento);
        return $stmt->execute();
    }

    // ====================
    // OBTENER USUARIO POR ID
    // ====================
    public function obtenerUsuarioPorId($id)
    {
        $query = "SELECT id_use, nombre, username, correo, genero, fechaNacimiento, avatar 
                  FROM usuarios 
                  WHERE id_use = :id LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ====================
    // ACTUALIZAR USUARIO
    // ====================
    public function actualizarUsuario($id_use, $nombre, $username, $correo, $genero, $fechaNacimiento, $avatar = null)
    {
        if ($avatar) {
            $query = "UPDATE usuarios 
                      SET nombre = :nombre, username = :username, correo = :correo, genero = :genero, 
                          fechaNacimiento = :fechaNacimiento, avatar = :avatar 
                      WHERE id_use = :id_use";
        } else {
            $query = "UPDATE usuarios 
                      SET nombre = :nombre, username = :username, correo = :correo, genero = :genero, 
                          fechaNacimiento = :fechaNacimiento 
                      WHERE id_use = :id_use";
        }

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":correo", $correo);
        $stmt->bindParam(":genero", $genero);
        $stmt->bindParam(":fechaNacimiento", $fechaNacimiento);
        $stmt->bindParam(":id_use", $id_use, PDO::PARAM_INT);

        if ($avatar) {
            $stmt->bindParam(":avatar", $avatar);
        }

        return $stmt->execute();
    }
}
?>
