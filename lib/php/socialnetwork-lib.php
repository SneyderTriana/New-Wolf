<?php
// Este archivo contiene funciones para la cabecera (Headerb) y la barra lateral (Side) de la aplicación.
// Es CRÍTICO que este archivo se incluya usando 'require_once' o 'include_once' en tu script principal
// para evitar el error "Cannot redeclare function".
//
// Requiere que la conexión a la base de datos ($mysqli) esté establecida
// y que session_start() se haya llamado en el script principal (ej. index.php).

/**
 * Define la función Headerb para la cabecera de la aplicación.
 *
 * @param mysqli $mysqli Objeto de conexión a la base de datos.
 * @return void
 */
function Headerb(mysqli $mysqli): void
{
    // Asegura que el usuario esté logueado y que $_SESSION['id'] esté definido.
    $current_user_id = $_SESSION['id'] ?? null;
    $is_logged_in = $_SESSION['loggedin'] ?? false; // Asume una variable de sesión 'loggedin'
    $cuantas = 0; // Inicializa el contador de notificaciones.
    $notifications = []; // Inicializa el array de notificaciones.

    // Procede con las consultas de BD solo si hay un ID de usuario en sesión,
    // el usuario está logueado y la conexión mysqli es válida.
    if ($current_user_id !== null && $is_logged_in && $mysqli instanceof mysqli && !$mysqli->connect_errno) {
        // Prepara la consulta para obtener notificaciones no leídas.
        $stmt_noti = $mysqli->prepare("SELECT id_not, user1, tipo, id_pub FROM notificaciones WHERE user2 = ? AND leido = '0' ORDER BY id_not DESC");

        // Verifica si la preparación de la consulta fue exitosa.
        if ($stmt_noti) {
            // Vincula el parámetro: "i" indica que es un entero.
            $stmt_noti->bind_param("i", $current_user_id);
            // Ejecuta la consulta.
            $stmt_noti->execute();
            // Obtiene el resultado.
            $result_noti = $stmt_noti->get_result();
            // Cuenta el número de filas (notificaciones).
            $cuantas = $result_noti->num_rows;

            // Obtiene todas las notificaciones en un array asociativo para iterar.
            $notifications = $result_noti->fetch_all(MYSQLI_ASSOC);
            // Cierra el statement.
            $stmt_noti->close();
        } else {
            // Registra un error si la preparación falla.
            error_log("Error al preparar el statement de notificaciones en Headerb: " . $mysqli->error);
        }
    }
?>
<header class="main-header">

    <a href="index.php" class="logo">
      <span class="logo-lg"><b>RED</b>SOCIAL</span>
    </a>

    <nav class="navbar navbar-static-top">
      <div class="navbar-custom-menu">
        <ul class="nav navbar-nav">

          <li class="dropdown notifications-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <i class="fa fa-bell-o"></i>
              <span class="label label-warning"><?php echo htmlspecialchars($cuantas); ?></span>
            </a>
            <ul class="dropdown-menu">
              <li class="header">Tienes <?php echo htmlspecialchars($cuantas); ?> notificaciones</li>
              <li>
                <ul class="menu">

                <?php
                // Muestra las notificaciones si hay alguna.
                if ($cuantas > 0) {
                    foreach ($notifications as $no) {
                        $userNameNoti = 'Usuario Desconocido'; // Valor predeterminado

                        // Solo intenta consultar si la conexión es válida y el usuario está logueado
                        if ($mysqli instanceof mysqli && !$mysqli->connect_errno && $is_logged_in) {
                            $stmt_users = $mysqli->prepare("SELECT username FROM usuarios WHERE id_use = ?");
                            
                            if ($stmt_users) {
                                // Vincula el parámetro.
                                $stmt_users->bind_param("i", $no['user1']);
                                // Ejecuta la consulta.
                                $stmt_users->execute();
                                // Obtiene el resultado.
                                $result_users = $stmt_users->get_result();
                                // Si se encuentra el usuario, actualiza $userNameNoti.
                                if ($result_users->num_rows > 0) {
                                    $usa = $result_users->fetch_assoc();
                                    $userNameNoti = $usa['username'];
                                }
                                // Cierra el statement.
                                $stmt_users->close();
                            } else {
                                // Registra un error si la preparación falla.
                                error_log("Error al preparar el statement de usuarios en Headerb (bucle): " . $mysqli->error);
                            }
                        } else {
                             error_log("Conexión mysqli no válida o usuario no logueado para consulta de usuario en Headerb (bucle).");
                        }
                ?>
                    <li>
                        <a href="publicacion.php?id=<?php echo htmlspecialchars($no['id_pub']); ?>">
                            <i class="fa fa-users text-aqua"></i> El usuario <?php echo htmlspecialchars($userNameNoti); ?> <?php echo htmlspecialchars($no['tipo']); ?> tu publicación
                        </a>
                    </li>
                <?php
                    }
                } else {
                    // Mensaje si no hay notificaciones.
                    echo '<li><a href="#"><i class="fa fa-info-circle"></i> No tienes nuevas notificaciones</a></li>';
                }
                ?>
                </ul>
              </li>
              <li class="footer"><a href="#">Ver todas las notificaciones</a></li>
            </ul>
          </li>

          <li class="dropdown user user-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <img src="avatars/<?php echo htmlspecialchars($_SESSION['avatar'] ?? 'default.jpg'); ?>" class="user-image" alt="User Image">
              <span class="hidden-xs"><?php echo htmlspecialchars(ucwords($_SESSION['usuario'] ?? 'Invitado')); ?></span>
            </a>
            <ul class="dropdown-menu">
              <li class="user-header">
                <img src="avatars/<?php echo htmlspecialchars($_SESSION['avatar'] ?? 'default.jpg'); ?>" class="img-circle" alt="User Image">

                <p>
                  <?php echo htmlspecialchars(ucwords($_SESSION['usuario'] ?? 'Invitado')); ?>
                  <small>Miembro desde Mayo 31</small> </p>
              </li>
              <li class="user-body">
                <div class="row">
                  <div class="col-xs-6 text-center">
                    <a href="#">Seguidores</a>
                  </div>
                  <div class="col-xs-6 text-center">
                    <a href="#">Seguidos</a>
                  </div>
                </div>
                </li>
              <li class="user-footer">
                <div class="pull-left">
                  <a href="editarperfil.php?id=<?php echo htmlspecialchars($_SESSION['id'] ?? '');?>" class="btn btn-default btn-flat">Editar perfil</a>
                </div>
                <div class="pull-right">
                  <a href="logout.php" class="btn btn-default btn-flat">Cerrar sesión</a>
                </div>
              </li>
            </ul>
          </li>
          <li>
            <a href="#" data-toggle="control-sidebar"><i class="fa fa-gears"></i></a>
          </li>
        </ul>
      </div>

    </nav>
  </header>
<?php
} // Fin de la función Headerb

/**
 * Define la función Side para la barra lateral de la aplicación.
 *
 * @return void
 */
function Side(): void
{
    // Asegura que las variables de sesión estén definidas.
    $avatar = $_SESSION['avatar'] ?? 'default.jpg';
    $usuario = $_SESSION['usuario'] ?? 'Invitado';
?>
<aside class="main-sidebar">
    <section class="sidebar">
      <div class="user-panel">
        <div class="pull-left image"> <img src="avatars/<?php echo htmlspecialchars($avatar); ?>" class="img-circle" alt="User Image">
        </div>
        <div class="pull-left info">
          <p><?php echo htmlspecialchars(ucwords($usuario)); ?></p>
          <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
        </div>
      </div>
      <form action="#" method="get" class="sidebar-form">
        <div class="input-group">
          <input type="text" name="q" class="form-control" placeholder="Encuentra a tus amigos">
                <span class="input-group-btn">
                  <button type="submit" name="search" id="search-btn" class="btn btn-flat"><i class="fa fa-search"></i>
                  </button>
                </span>
        </div>
      </form>
      <ul class="sidebar-menu">
        <li class="header">MENÚ DE NAVEGACIÓN</li>
        <li>
          <a href="index.php">
            <i class="fa fa-dashboard"></i> <span>Noticias</span>
          </a>
        </li>
        <li>
          <a href="mensajes.php">
            <i class="fa fa-comment"></i> <span>Chat</span>
            <span class="pull-right-container">
              <small class="label pull-right bg-green">1</small> </span>
          </a>
        </li>
        <li>
          <a href="mis_seguidores.php"> <i class="fa fa-user"></i> <span>Mis seguidores</span>
          </a>
        </li>
        <li>
          <a href="seguidos.php"> <i class="fa fa-arrow-right"></i> <span>Seguidos</span>
          </a>
        </li>
        <li>
          <a href="me_gusta.php"> <i class="fa fa-heart"></i> <span>Me gusta</span>
          </a>
        </li>
        </ul> </section>
    </aside>
<?php
} // Fin de la función Side
?>