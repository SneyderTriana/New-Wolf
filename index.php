<?php
// Incluye el controlador que maneja las operaciones del usuario (registro, login, etc.)
require_once "controller/UsuarioController.php";

// Inicia la sesión para gestionar variables de sesión
session_start();

// Crea una instancia del controlador de usuario
$controller = new UsuarioController();

// Verifica si la solicitud es POST y si se envió la acción correspondiente
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["action"])) {

    // Acción: Registro de usuario
    if ($_POST["action"] == "register") {
        // Verifica que se hayan enviado todos los campos necesarios
        if (
            isset($_POST['username'], $_POST['password'], $_POST['nombre'], $_POST['correo'], $_POST['genero'], $_POST['fechaNacimiento'])
        ) {
            // Obtiene los datos del formulario
            $username = $_POST['username'];
            $password = $_POST['password'];
            $nombre = $_POST['nombre'];
            $correo = $_POST['correo'];
            $genero = $_POST['genero'];
            $fechaNacimiento = $_POST['fechaNacimiento'];

            // Llama al método registrar del controlador y muestra el resultado
            if ($controller->registrar($username, $password, $nombre, $correo, $genero, $fechaNacimiento)) {
                echo "Usuario registrado correctamente.";
            } else {
                echo "Error al registrar el usuario.";
            }
        } else {
            echo "Faltan datos en el formulario.";
        }
    }

    // Acción: Inicio de sesión
    if ($_POST["action"] == "login") {
        // Obtiene las credenciales ingresadas
        $username = $_POST["username"];
        $password = $_POST["password"];

        // Llama al método login del controlador
        $user = $controller->login($username, $password);

        // Si el usuario es válido, lo guarda en sesión y redirige
        if ($user) {
            // Guarda el array/objeto completo del usuario (si lo necesitas)
            $_SESSION["user"] = $user;

            // Almacenar datos específicos del usuario en la sesión para fácil acceso
            // Asegúrate de que las claves ('id_use', 'usuario', 'avatar') coinciden con las devueltas por tu método login
            $_SESSION["id"] = $user['id_use'] ?? null; // ID del usuario
            $_SESSION["usuario"] = $user['username'] ?? null; // Nombre de usuario (campo 'username' en tu tabla)
            $_SESSION["avatar"] = $user['avatar'] ?? 'default.jpg'; // Nombre del archivo del avatar
            $_SESSION["privado"] = $user['privado'] ?? 0; // Estado de privacidad (campo 'privado' en tu tabla)

            header("Location: index.php"); // Redirige a la página principal
            exit; // Detiene la ejecución del script después de la redirección
        } else {
            echo "Usuario o contraseña incorrectos.";
        }
    }
}

// Acción: Cerrar sesión
if (isset($_GET["action"]) && $_GET["action"] == "logout") {
    session_destroy(); // Destruye la sesión
    header("location: index.php"); // Redirige al inicio
    exit; // Detiene la ejecución del script
}

// Si el usuario está logueado, carga el panel principal
if (isset($_SESSION["user"])) {
    require_once "view/dashboard.php";
} else {
    // Si no está logueado, muestra el formulario de inicio de sesión
    require_once "view/login.php";
}
?>