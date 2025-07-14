<?php

require_once __DIR__ . '/../model/usuario.php';

class UsuarioController
{
    private $usuarioModel;

    public function __construct()
    {
        $this->usuarioModel = new Usuario();
    }

    public function login($username, $password)
    {
        return $this->usuarioModel->login($username, $password);
    }

    public function registrar($username, $password, $nombre, $correo, $genero, $fechaNacimiento)
    {
        return $this->usuarioModel->registrar($username, $password, $nombre, $correo, $genero, $fechaNacimiento);
    }

    public function obtenerDatosUsuario()
    {
        // Este método debe retornar los datos del usuario logueado
        return $this->usuarioModel->obtenerUsuarioPorId($_SESSION['id']);
    }

    public function actualizarPerfil()
    {
        if (
            isset($_POST['nombre'], $_POST['username'], $_POST['correo'], $_POST['genero'], $_POST['fechaNacimiento'])
        ) {
            $avatar = null;
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $avatar = basename($_FILES['avatar']['name']);
                move_uploaded_file($_FILES['avatar']['tmp_name'], "../assets/avatars/$avatar");
            }

            return $this->usuarioModel->actualizarUsuario(
                $_SESSION['id'],
                $_POST['nombre'],
                $_POST['username'],
                $_POST['correo'],
                $_POST['genero'],
                $_POST['fechaNacimiento'],
                $avatar
            );
        }
        return false;
    }
}
?>
