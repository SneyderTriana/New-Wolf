<?php
// Inicia la sesión PHP.
session_start();

// Incluye archivos de configuración y librerías.
require_once __DIR__ . '/../config/addressDocument.php';
require_once HEADER; 

// Activa el reporte de errores para depuración.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluye la conexión a la base de datos y la librería de red social.
include '../config/conexion_mysqli_oop.php';
include '../lib/php/socialnetwork-lib.php'; 

// Verifica la conexión a la base de datos.
if (!isset($mysqli) || $mysqli->connect_errno) {
    die("Error en la conexión a la base de datos.");
}

// Redirecciona si el usuario no ha iniciado sesión.
if (!isset($_SESSION['id'])) {
    header('Location: login.php'); 
    exit();
}

// Procesar parámetros GET para el perfil si el ID está presente.
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $pag = isset($_GET['perfil']) ? $_GET['perfil'] : 'miactividad';

    // Obtiene información del usuario del perfil (nombre, avatar, etc.).
    $stmt_user = $mysqli->prepare("SELECT id_use, nombre, avatar, verificado, privada FROM usuarios WHERE id_use = ?");
    $stmt_user->bind_param("i", $id);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();
    $use = $result_user->fetch_assoc();
    $stmt_user->close();

    // Redirige si el usuario no existe.
    if (!$use) {
        header("Location: error.php?msg=Usuario no encontrado"); 
        exit();
    }

    // Obtiene información de la relación de amistad entre el usuario logueado y el perfil visto.
    $stmt_amigos = $mysqli->prepare("SELECT * FROM amigos WHERE (de = ? AND para = ?) OR (de = ? AND para = ?)");
    $stmt_amigos->bind_param("iiii", $_SESSION['id'], $id, $id, $_SESSION['id']);
    $stmt_amigos->execute();
    $result_amigos = $stmt_amigos->get_result();
    $ami = $result_amigos->fetch_assoc();
    $num_amigos = $result_amigos->num_rows;
    $stmt_amigos->close();

    // Bloque de manejo de acciones de amistad (seguir/dejar de seguir) por POST.
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Lógica para enviar solicitud de amistad (seguir).
        if (isset($_POST['seguir'])) {
            $stmt_add = $mysqli->prepare("INSERT INTO amigos (de, para, fecha, estado) VALUES (?, ?, NOW(), '0')");
            $stmt_add->bind_param("ii", $_SESSION['id'], $id);
            if (!$stmt_add->execute()) {
                echo "Error al enviar solicitud de amistad: " . $stmt_add->error;
            }
            $stmt_add->close();
            header("Location: perfil.php?id=" . $id);
            exit();
        }

        // Lógica para seguir directamente (perfiles no privados).
        if (isset($_POST['seguirdirecto'])) {
            $stmt_add = $mysqli->prepare("INSERT INTO amigos (de, para, fecha, estado) VALUES (?, ?, NOW(), '1')");
            $stmt_add->bind_param("ii", $_SESSION['id'], $id);
            if (!$stmt_add->execute()) {
                echo "Error al seguir directamente: " . $stmt_add->error;
            }
            $stmt_add->close();
            header("Location: perfil.php?id=" . $id);
            exit();
        }

        // Lógica para dejar de seguir (eliminar relación de amistad).
        if (isset($_POST['dejarseguir'])) {
            $stmt_delete = $mysqli->prepare("DELETE FROM amigos WHERE (de = ? AND para = ?) OR (de = ? AND para = ?)");
            $stmt_delete->bind_param("iiii", $_SESSION['id'], $id, $id, $_SESSION['id']);
            if (!$stmt_delete->execute()) {
                echo "Error al dejar de seguir: " . $stmt_delete->error;
            }
            $stmt_delete->close();
            header("Location: perfil.php?id=" . $id);
            exit();
        }
    }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($use['nombre']); ?> </title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <link rel="stylesheet" href="../assets/styles/perfil.css">
        <link rel="stylesheet" href="../assets/styles/style.css">
    <link rel="stylesheet" href="../assets/styles/main.css"> 
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
    <script src="js/jquery.jscroll.js"></script>
</head>
<body>
<div class="wrapper">
    <aside class="main-sidebar">
        <div class="user-panel">
            <img src="../assets/avatars/default.jpg" alt="Avatar" />
            <p>Kirito_156</p>
        </div>
        <ul class="sidebar-menu">
            <li><a href="#"><i class="icon-home"></i> Noticias</a></li>
            <li><a href="#"><i class="icon-comment"></i> Chat</a></li>
            <li><a href="#"><i class="icon-users"></i> Seguidores</a></li>
            <li><a href="#"><i class="icon-heart"></i> Me gusta</a></li>
        </ul>
    </aside>

    <div class="content-wrapper">
        <section class="content-section">
            <div class="row-container">
                
                <div class="profile-form-column">
                    <div class="box primary-box">
                        <div class="box-header">
                            <h3 class="box-title">Perfil de usuario</h3>
                        </div>
                        <div class="box-body">
                            <div class="profile-box-body">
                                <img class="profile-user-img" src="avatars/<?php echo htmlspecialchars($use['avatar']); ?>" alt="User profile picture">
                                <h3 class="profile-username"><?php echo htmlspecialchars($use['nombre']); ?></h3>
                                
                                <?php // Muestra el ícono de verificado si aplica.
                                if ($use['verificado'] != 0) { ?>
                                    <center><span class="icon-verified">✔</span></center> 
                                <?php } ?>
                                
                                <p class="text-muted-center">Software Engineer</p>

                                <ul class="list-unbordered">
                                    <li class="list-item">
                                        <b>Followers</b> <span class="pull-right">1,322</span>
                                    </li>
                                    <li class="list-item">
                                        <b>Following</b> <span class="pull-right">543</span>
                                    </li>
                                    <li class="list-item">
                                        <b>Friends</b> <span class="pull-right">13,287</span>
                                    </li>
                                </ul>

                                <?php // Sección para seguir/dejar de seguir si no es el perfil propio.
                                if ($_SESSION['id'] != $id) { ?>
                                    <form action="" method="post">
                                        <?php // Estado: Esperando respuesta (solicitud enviada).
                                        if ($num_amigos >= 1 && $ami['estado'] == 0) { ?>
                                            <center>
                                                <h4>Esperando respuesta</h4>
                                            </center>
                                        <?php } else { ?>
                                            <?php // Perfil privado: Opción de enviar solicitud (estado 0) si no hay relación o la relación no está aceptada.
                                            if ($use['privada'] == 1 && ($num_amigos == 0 || $ami['estado'] == 0)) { ?>
                                                <input type="submit" class="button button-primary" name="seguir" value="Enviar solicitud de amistad">
                                            <?php } ?>

                                            <?php // Perfil privado: Opción de dejar de seguir (estado 1) si ya son amigos.
                                            if ($use['privada'] == 1 && $num_amigos >= 1 && $ami['estado'] == 1) { ?>
                                                <input type="submit" class="button button-danger" name="dejarseguir" value="Dejar de seguir">
                                            <?php } ?>

                                            <?php // Perfil público: Opción de Seguir directamente (estado 1).
                                            if ($use['privada'] == 0 && ($num_amigos == 0 || $ami['estado'] == 0)) { ?>
                                                <input type="submit" class="button button-primary" name="seguirdirecto" value="Seguir">
                                            <?php } ?>

                                            <?php // Perfil público: Opción de dejar de seguir.
                                            if ($use['privada'] == 0 && $num_amigos >= 1 && $ami['estado'] == 1) { ?>
                                                <input type="submit" class="button button-danger" name="dejarseguir" value="Dejar de seguir">
                                            <?php } ?>
                                        <?php } ?>
                                    </form>
                                <?php } ?>

                                <br>
                                <a href="chat.php?usuario=<?php echo htmlspecialchars($id); ?>"><input type="button" class="button button-default" value="Enviar chat"></a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="sidebar-column">
                    <div class="tabs-custom">
                        <ul class="nav-tabs">
                            <li class="<?php echo $pag == 'miactividad' ? 'active' : ''; ?>"><a href="?id=<?php echo htmlspecialchars($id); ?>&perfil=miactividad">Actividad</a></li>
                            <li class="<?php echo $pag == 'informacion' ? 'active' : ''; ?>"><a href="?id=<?php echo htmlspecialchars($id); ?>&perfil=informacion">Información</a></li>
                            <li class="<?php echo $pag == 'fotos' ? 'active' : ''; ?>"><a href="?id=<?php echo htmlspecialchars($id); ?>&perfil=fotos">Fotos</a></li>
                        </ul>
                        <div class="tab-content">
                            <div class="scroll-content">
                                <?php // Controla la visibilidad del contenido según si el perfil es privado y el estado de amistad.
                                if (
                                    $use['privada'] != 1 ||
                                    ($use['privada'] == 1 && $num_amigos >= 1 && $ami['estado'] == 1) ||
                                    ($_SESSION['id'] == $id)
                                ) {
                                    $valid_pages = ['miactividad', 'informacion', 'fotos']; 
                                    $current_page = in_array($pag, $valid_pages) ? $pag : 'miactividad';

                                    // Incluye el contenido de la pestaña seleccionada (Actividad/Información/Fotos).
                                    require_once '../config/' . $current_page . '.php';
                                } else {
                                ?>
                                    <center>
                                        <h2>Este perfil es privado, envía una solicitud.</h2>
                                    </center>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="box danger-box">
                        <div class="box-header">
                            <h3 class="box-title">Solicitudes de amistad</h3>
                        </div>
                        <div class="box-body user-list-items">
                            </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <div class="info-box">
        <div class="info-box-header">
            <h3 class="info-box-title">About Me</h3>
        </div>
        <div class="info-box-body">
            <strong><i class="icon-book"></i> Education</strong>
            <p class="text-muted">
                B.S. in Computer Science from the University of Tennessee at Knoxville
            </p>
            <hr>
            <strong><i class="icon-map-marker"></i> Location</strong>
            <p class="text-muted">Malibu, California</p>
            <hr>
            <strong><i class="icon-pencil"></i> Skills</strong>
            <p>
                <span class="tag tag-danger">UI Design</span>
                <span class="tag tag-success">Coding</span>
                <span class="tag tag-info">Javascript</span>
                <span class="tag tag-warning">PHP</span>
                <span class="tag tag-primary">Node.js</span>
            </p>
            <hr>
            <strong><i class="icon-notes"></i> Notes</strong>
            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam fermentum enim neque.</p>
        </div>
    </div>
    
    <aside class="control-sidebar">
        <ul class="control-sidebar-nav">
            <li><a href="#control-sidebar-home-tab" data-toggle="tab"><span class="icon-home"></span></a></li>
            <li><a href="#control-sidebar-settings-tab" data-toggle="tab"><span class="icon-settings"></span></a></li>
        </ul>
        <div class="control-sidebar-content">
            <div class="tab-pane-content" id="control-sidebar-home-tab">
                <h3 class="sidebar-heading">Actividad Reciente</h3>
                <ul class="sidebar-menu">
                    <li>
                        <a href="javascript:void(0)">
                            <span class="menu-icon bg-red">🎂</span>
                            <div class="menu-info">
                                <h4 class="subheading">Cumpleaños de Langdon</h4>
                                <p>Cumplirá 23 el 24 de abril</p>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="javascript:void(0)">
                            <span class="menu-icon bg-yellow">👤</span>
                            <div class="menu-info">
                                <h4 class="subheading">Frodo actualizó su perfil</h4>
                                <p>Nuevo teléfono +1(800)555-1234</p>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="javascript:void(0)">
                            <span class="menu-icon bg-light-blue">✉️</span>
                            <div class="menu-info">
                                <h4 class="subheading">Nora se unió a la lista de correo</h4>
                                <p>nora@example.com</p>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="javascript:void(0)">
                            <span class="menu-icon bg-green">⚙️</span>
                            <div class="menu-info">
                                <h4 class="subheading">Trabajo Cron 254 ejecutado</h4>
                                <p>Tiempo de ejecución 5 segundos</p>
                            </div>
                        </a>
                    </li>
                </ul>
                <h3 class="sidebar-heading">Progreso de Tareas</h3>
                <ul class="sidebar-menu">
                    <li>
                        <a href="javascript:void(0)">
                            <h4 class="subheading">
                                Diseño de Plantilla Personalizada
                                <span class="label-progress danger-progress">70%</span>
                            </h4>
                            <div class="progress-bar-container">
                                <div class="progress-bar danger-bar" style="width: 70%"></div>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="javascript:void(0)">
                            <h4 class="subheading">
                                Actualizar Currículum
                                <span class="label-progress success-progress">95%</span>
                            </h4>
                            <div class="progress-bar-container">
                                <div class="progress-bar success-bar" style="width: 95%"></div>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="javascript:void(0)">
                            <h4 class="subheading">
                                Integración Laravel
                                <span class="label-progress warning-progress">50%</span>
                            </h4>
                            <div class="progress-bar-container">
                                <div class="progress-bar warning-bar" style="width: 50%"></div>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="javascript:void(0)">
                            <h4 class="subheading">
                                Framework de Back End
                                <span class="label-progress primary-progress">68%</span>
                            </h4>
                            <div class="progress-bar-container">
                                <div class="progress-bar primary-bar" style="width: 68%"></div>
                            </div>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="tab-pane-content" id="control-sidebar-stats-tab">Contenido de la pestaña de estadísticas</div>
            <div class="tab-pane-content" id="control-sidebar-settings-tab">
                <form method="post">
                    <h3 class="sidebar-heading">Configuración General</h3>
                    <div class="form-group">
                        <label class="subheading-label">
                            Reportar uso del panel
                            <input type="checkbox" class="pull-right" checked>
                        </label>
                        <p>
                            Alguna información sobre esta opción de configuración general
                        </p>
                    </div>
                    <div class="form-group">
                        <label class="subheading-label">
                            Permitir redirección de correo
                            <input type="checkbox" class="pull-right" checked>
                        </label>
                        <p>
                            Otras opciones disponibles
                        </p>
                    </div>
                    <div class="form-group">
                        <label class="subheading-label">
                            Exponer nombre del autor en publicaciones
                            <input type="checkbox" class="pull-right" checked>
                        </label>
                        <p>
                            Permitir al usuario mostrar su nombre en las publicaciones del blog
                        </p>
                    </div>
                    <h3 class="sidebar-heading">Configuración de Chat</h3>
                    <div class="form-group">
                        <label class="subheading-label">
                            Mostrarme como en línea
                            <input type="checkbox" class="pull-right" checked>
                        </label>
                    </div>
                    <div class="form-group">
                        <label class="subheading-label">
                            Desactivar notificaciones
                            <input type="checkbox" class="pull-right">
                        </label>
                    </div>
                    <div class="form-group">
                        <label class="subheading-label">
                            Eliminar historial de chat
                            <a href="javascript:void(0)" class="text-red pull-right"><span class="icon-trash">🗑️</span></a>
                        </label>
                    </div>
                </form>
            </div>
        </div>
    </aside>
    <div class="control-sidebar-bg"></div>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script src="js/jquery.jscroll.js"></script>
<script src="../assets/js/custom-tabs.js"></script> 
<script src="../assets/js/custom-sidebar.js"></script> 
</body>
</html>
<?php
// Cierre del bloque 'if' que verifica si se proporcionó un ID de usuario.
} else {
    // Si no se proporciona un ID de usuario en la URL, muestra un mensaje de error.
    echo "<p style='text-align: center; color: red;'>Error: No se ha especificado un ID de usuario para el perfil.</p>";
}
?>