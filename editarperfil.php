<?php
require_once "../controller/UsuarioController.php";
session_start();
require_once __DIR__ . '/../config/addressDocument.php';
require_once HEADER;
// Verifica si el usuario ha iniciado sesión
if (!isset($_SESSION["id"])) {
    header("Location: login.php");
    exit();
}

$controller = new UsuarioController();

// Procesar actualización del perfil
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["actualizar"])) {
    $controller->actualizarPerfil();
}

// Obtener datos actuales del usuario
$datos = $controller->obtenerDatosUsuario();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar mi perfil</title>
    <link rel="stylesheet" href="../assets/styles/perfil.css">
</head>
<body>
            <link rel="stylesheet" href="../assets/styles/style.css">
<div class="wrapper">
    <!-- PANEL IZQUIERDO -->
    <aside class="main-sidebar">
        <div class="user-panel">
            <img src="../assets/avatars/<?php echo htmlspecialchars($datos['avatar']); ?>" alt="Avatar">
            <p><?php echo htmlspecialchars($datos['username']); ?></p>
        </div>
        <ul class="sidebar-menu">
            <li><a href="perfil.php"><i class="icon-home"></i> Perfil</a></li>
            <li><a href="#"><i class="icon-comment"></i> Chat</a></li>
            <li><a href="#"><i class="icon-users"></i> Seguidores</a></li>
            <li><a href="#"><i class="icon-heart"></i> Me gusta</a></li>
        </ul>
    </aside>

    <!-- CONTENIDO CENTRAL -->
    <div class="content-wrapper">
        <section class="content-section">
            <div class="row-container">
                <!-- FORMULARIO -->
                <div class="profile-form-column">
                    <div class="box primary-box">
                        <div class="box-header">
                            <h3 class="box-title">Editar mi perfil</h3>
                        </div>
                        <div class="box-body">
                            <form method="POST" enctype="multipart/form-data">
                                <label>Nombre:</label>
                                <input type="text" name="nombre" value="<?php echo htmlspecialchars($datos['nombre']); ?>" required>

                                <label>Usuario:</label>
                                <input type="text" name="username" value="<?php echo htmlspecialchars($datos['username']); ?>" required>

                                <label>Email:</label>
                                <input type="email" name="correo" value="<?php echo htmlspecialchars($datos['correo']); ?>" required>

                                <label>Género:</label>
                                <select name="genero" required>
                                    <option value="Masculino" <?php if ($datos['genero'] === 'Masculino') echo 'selected'; ?>>Masculino</option>
                                    <option value="Femenino" <?php if ($datos['genero'] === 'Femenino') echo 'selected'; ?>>Femenino</option>
                                    <option value="Otro" <?php if ($datos['genero'] === 'Otro') echo 'selected'; ?>>Otro</option>
                                </select>

                                <label>Fecha de nacimiento:</label>
                                <input type="date" name="fechaNacimiento" value="<?php echo htmlspecialchars($datos['fechaNacimiento']); ?>" required>

                                <label>Avatar (opcional):</label>
                                <input type="file" name="avatar">

                                <button type="submit" name="actualizar" class="btn-edit">Guardar cambios</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- PANEL DERECHO -->
                <div class="sidebar-column">
                    <div class="box danger-box">
                        <div class="box-header">
                            <h3 class="box-title">Solicitudes de amistad</h3>
                        </div>
                        <div class="box-body user-list-items">
                            <p>No hay nuevas solicitudes.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
</body>
</html>
