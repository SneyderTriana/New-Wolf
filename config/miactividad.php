<link rel="stylesheet" href="../assets/styles/perfil.css">
<?php
// Incluye la conexión a la base de datos mysqli
include __DIR__ . '/../config/conexion_mysqli_proc.php';
// Asegúrate de que socialnetwork-lib.php no use funciones mysql_*
include_once __DIR__ . '/../lib/php/socialnetwork-lib.php';

// Muestra errores para depuración (¡desactívalo en producción!)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Verifica la conexión a la base de datos
if (!isset($mysqli) || $mysqli->connect_error) {
    die("Error de conexión a la base de datos: " . (isset($mysqli) ? $mysqli->connect_error : "La conexión a MySQLi no está definida en lib/config.php"));
}

// Redirecciona si el usuario no está logueado
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

// Obtener ID de usuario logueado de la sesión de forma segura
$session_user_id = $_SESSION['id'] ?? null;
$session_user_avatar = $_SESSION['avatar'] ?? 'default.jpg'; // Valor por defecto
$session_username = $_SESSION['usuario'] ?? 'Usuario Desconocido'; // Valor por defecto

// Asegúrate de que 'likes.js' y el script de comentarios AJAX funcionen correctamente con los nuevos IDs/clases si los cambias.
// También, el archivo 'agregarcomentario.php' debe estar actualizado para usar mysqli.
?>
<script type="text/javascript" src="js/likes.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $(".enviar-btn").keypress(function (event) {
            // Si la tecla presionada es Enter (13)
            if (event.which == 13) {
                // Obtiene el ID de la publicación del atributo 'id' del elemento padre
                var getpID = $(this).parent().attr('id').replace('record-', '');

                // Obtener valores de campos ocultos del formulario
                var usuario = $("#usuario-" + getpID).val();
                var comentario = $("#comentario-" + getpID).val();
                var publicacion = getpID;
                var avatar = $("#avatar-" + getpID).val();
                var nombre = $("#nombre-" + getpID).val();

                // Formatea la fecha y hora actual
                var now = new Date();
                var date_show = now.getDate() + '-' + (now.getMonth() + 1) + '-' + now.getFullYear() + ' ' + now.getHours() + ':' + ('0' + now.getMinutes()).slice(-2) + ':' + ('0' + now.getSeconds()).slice(-2);

                // Valida que el comentario no esté vacío
                if (comentario.trim() === '') {
                    alert('Debes añadir un comentario');
                    return false;
                }

                // Prepara los datos para la solicitud AJAX
                var dataString = 'usuario=' + encodeURIComponent(usuario) + '&comentario=' + encodeURIComponent(comentario) + '&publicacion=' + encodeURIComponent(publicacion);

                // Realiza la solicitud AJAX para agregar el comentario
                $.ajax({
                    type: "POST", // Método POST
                    url: "agregarcomentario.php", // URL del script de procesamiento
                    data: dataString, // Datos a enviar
                    success: function () {
                        // Añade el nuevo comentario al DOM
                        $('#nuevocomentario' + getpID).append(
                            '<div class="comment-item">' +
                            '<img class="comment-avatar" src="avatars/' + avatar + '">' +
                            '<div class="comment-content">' +
                            '<span class="comment-username"> ' + nombre + '<span class="comment-date">' + date_show + '</span></span>' +
                            '<p class="comment-text-body">' + comentario + '</p>' +
                            '</div>' +
                            '</div>'
                        );
                        // Limpia el campo de comentario después de enviar
                        $("#comentario-" + getpID).val('');
                    },
                    error: function (xhr, status, error) {
                        // Muestra error en consola y alerta
                        console.error("Error al agregar comentario:", status, error);
                        alert("Error al agregar el comentario. Por favor, inténtalo de nuevo.");
                    }
                });
                return false; // Evita el envío del formulario tradicional
            }
        });
    });
</script>

<?php
$CantidadMostrar = 5; // Número de publicaciones a mostrar por página
// Sanea y valida el ID del usuario del GET
$aid = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);

// Si el ID de usuario es inválido, muestra un mensaje y termina
if ($aid === false || $aid === null || $aid <= 0) {
    echo "<p class='error-message'>ID de usuario inválido.</p>";
    exit();
}

// Contar el total de registros de publicaciones
$stmt_total_reg = $mysqli->prepare("SELECT COUNT(*) FROM publicaciones WHERE usuario = ?");
if ($stmt_total_reg) {
    $stmt_total_reg->bind_param("i", $aid); // Vincula el ID de usuario
    $stmt_total_reg->execute(); // Ejecuta la consulta
    $stmt_total_reg->bind_result($totalr); // Vincula el resultado
    $stmt_total_reg->fetch(); // Obtiene el resultado
    $stmt_total_reg->close(); // Cierra la sentencia
} else {
    echo "<p class='error-message'>Error al preparar la consulta de conteo de publicaciones: " . $mysqli->error . "</p>";
    $totalr = 0; // Asegura que $totalr tenga un valor en caso de error
}

// Calcula el número total de páginas
$TotalRegistro = ceil($totalr / $CantidadMostrar);

// Valida la variable de paginación
$compag = (int) ($_GET['pag'] ?? 1);
if ($compag < 1) {
    $compag = 1;
} elseif ($compag > $TotalRegistro && $TotalRegistro > 0) {
    $compag = $TotalRegistro;
}

// Calcula el número de página siguiente para la paginación
$IncrimentNum = (($compag + 1) <= $TotalRegistro) ? ($compag + 1) : 0;

// Consulta SQL para obtener las publicaciones
$offset = ($compag - 1) * $CantidadMostrar; // Calcula el offset
$stmt_publicaciones = $mysqli->prepare("SELECT id_pub, usuario, contenido, imagen, fecha, likes FROM publicaciones WHERE usuario = ? ORDER BY id_pub DESC LIMIT ?, ?");

if ($stmt_publicaciones) {
    $stmt_publicaciones->bind_param("iii", $aid, $offset, $CantidadMostrar); // Vincula los parámetros
    $stmt_publicaciones->execute(); // Ejecuta la consulta
    $result_publicaciones = $stmt_publicaciones->get_result(); // Obtiene el resultado

    // Itera sobre cada publicación
    while ($lista = $result_publicaciones->fetch_assoc()) {
        $userid = $lista['usuario']; // ID del usuario de la publicación

        // Obtener datos del usuario de la publicación
        $stmt_usuario = $mysqli->prepare("SELECT id_use, username, avatar FROM usuarios WHERE id_use = ?");
        $use = ['id_use' => '', 'usuario' => 'Desconocido', 'avatar' => 'default.jpg']; // Valores por defecto
        if ($stmt_usuario) {
            $stmt_usuario->bind_param("i", $userid);
            $stmt_usuario->execute();
            $result_usuario = $stmt_usuario->get_result();
            if ($result_usuario->num_rows > 0) {
                $use = $result_usuario->fetch_assoc();
            }
            $stmt_usuario->close();
        }

        // Obtener datos de la foto si existe
        $fot = ['ruta' => '']; // Valor por defecto
        if ($lista['imagen'] != 0) {
            $stmt_fotos = $mysqli->prepare("SELECT ruta FROM fotos WHERE publicacion = ?");
            if ($stmt_fotos) {
                $stmt_fotos->bind_param("i", $lista['id_pub']);
                $stmt_fotos->execute();
                $result_fotos = $stmt_fotos->get_result();
                if ($result_fotos->num_rows > 0) {
                    $fot = $result_fotos->fetch_assoc();
                }
                $stmt_fotos->close();
            }
        }
        ?>
        <div class="publication-card">
            <div class="publication-header">
                <div class="user-info">
                    <img class="user-avatar" src="avatars/<?php echo htmlspecialchars($use['avatar']); ?>" alt="User Avatar">
                    <span class="username-link"
                        onclick="location.href='perfil.php?id=<?php echo htmlspecialchars($use['id_use']); ?>';"><?php echo htmlspecialchars($use['username']); ?></span>
                    <span class="post-date"><?php echo htmlspecialchars($lista['fecha']); ?></span>
                </div>
                <div class="publication-tools">
                    <button type="button" class="tool-button collapse-button">
                        <span>&#9660;</span> </button>
                </div>
            </div>
            <div class="publication-body">
                <p class="post-content"><?php echo htmlspecialchars($lista['contenido']); ?></p>

                <?php if ($lista['imagen'] != 0 && !empty($fot['ruta'])) { ?>
                    <img class="post-image" src="publicaciones/<?php echo htmlspecialchars($fot['ruta']); ?>"
                        alt="Publicación con imagen">
                <?php } ?>

                <div class="space-divider"></div>
                <?php
                // Contar comentarios
                $numcomen = 0;
                $stmt_num_comen = $mysqli->prepare("SELECT COUNT(*) FROM comentarios WHERE publicacion = ?");
                if ($stmt_num_comen) {
                    $stmt_num_comen->bind_param("i", $lista['id_pub']);
                    $stmt_num_comen->execute();
                    $stmt_num_comen->bind_result($numcomen);
                    $stmt_num_comen->fetch();
                    $stmt_num_comen->close();
                }
                ?>
                <ul class="post-actions-list">
                    <?php
                    // Verificar si el usuario actual ya dio "me gusta"
                    $user_liked = false;
                    if ($session_user_id !== null) {
                        $stmt_like_check = $mysqli->prepare("SELECT id_use FROM likes WHERE post = ? AND usuario = ?");
                        if ($stmt_like_check) {
                            $stmt_like_check->bind_param("ii", $lista['id_pub'], $session_user_id);
                            $stmt_like_check->execute();
                            $stmt_like_check->store_result();
                            if ($stmt_like_check->num_rows > 0) {
                                $user_liked = true;
                            }
                            $stmt_like_check->close();
                        }
                    }
                    ?>
                    <li class="action-item">
                        <button type="button" class="button button-default like-button"
                            id="<?php echo htmlspecialchars($lista['id_pub']); ?>">
                            <span class="icon-thumbs-up"></span> <?php echo $user_liked ? 'No me gusta' : 'Me gusta'; ?>
                        </button>
                        <span class="like-count" id="likes_<?php echo htmlspecialchars($lista['id_pub']); ?>">
                            (<?php echo htmlspecialchars($lista['likes']); ?>)</span>
                    </li>

                    <li class="action-item-right">
                        <span class="comments-link"><span class="icon-comment"></span> Comentarios
                            (<?php echo htmlspecialchars($numcomen); ?>)</span>
                    </li>
                </ul>
            </div>
            <div class="publication-comments">
                <?php
                // Mostrar los últimos 2 comentarios
                $stmt_comentarios = $mysqli->prepare("SELECT id_com, usuario, comentario, fecha FROM comentarios WHERE publicacion = ? ORDER BY id_com DESC LIMIT 2");
                if ($stmt_comentarios) {
                    $stmt_comentarios->bind_param("i", $lista['id_pub']);
                    $stmt_comentarios->execute();
                    $result_comentarios = $stmt_comentarios->get_result();
                    while ($com = $result_comentarios->fetch_assoc()) {
                        // Obtener datos del usuario del comentario
                        $usuarioc = ['usuario' => 'Desconocido', 'avatar' => 'default.jpg']; // Valores por defecto
                        $stmt_usuarioc = $mysqli->prepare("SELECT usuario, avatar FROM usuarios WHERE id_use = ?");
                        if ($stmt_usuarioc) {
                            $stmt_usuarioc->bind_param("i", $com['usuario']);
                            $stmt_usuarioc->execute();
                            $result_usuarioc = $stmt_usuarioc->get_result();
                            if ($result_usuarioc->num_rows > 0) {
                                $usuarioc = $result_usuarioc->fetch_assoc();
                            }
                            $stmt_usuarioc->close();
                        }
                        ?>
                        <div class="comment-item">
                            <img class="comment-avatar" src="avatars/<?php echo htmlspecialchars($usuarioc['avatar']); ?>"
                                alt="Avatar Comentario">
                            <div class="comment-content">
                                <span class="comment-username">
                                    <?php echo htmlspecialchars($usuarioc['usuario']); ?>
                                    <span class="comment-date"><?php echo htmlspecialchars($com['fecha']); ?></span>
                                </span>
                                <p class="comment-text-body"><?php echo htmlspecialchars($com['comentario']); ?></p>
                            </div>
                        </div>
                        <?php
                    }
                    $stmt_comentarios->close();
                }
                ?>

                <?php if ($numcomen > 2) { ?>
                    <div class="view-more-comments">
                        <a href="publicacion.php?id=<?php echo htmlspecialchars($lista['id_pub']); ?>" class="link-primary">Ver
                            todos los comentarios</a>
                    </div>
                <?php } ?>

                <div id="nuevocomentario<?php echo htmlspecialchars($lista['id_pub']); ?>"></div>
                <div class="space-divider"></div>
                <form method="post" action="" class="comment-form">
                    <label for="comentario-<?php echo htmlspecialchars($lista['id_pub']); ?>"
                        id="record-<?php echo htmlspecialchars($lista['id_pub']); ?>" class="comment-input-label">
                        <input type="text" class="enviar-btn form-input" placeholder="Escribe un comentario" name="comentario"
                            id="comentario-<?php echo htmlspecialchars($lista['id_pub']); ?>">
                        <input type="hidden" name="usuario" value="<?php echo htmlspecialchars($session_user_id); ?>"
                            id="usuario-<?php echo htmlspecialchars($lista['id_pub']); ?>">
                        <input type="hidden" name="publicacion" value="<?php echo htmlspecialchars($lista['id_pub']); ?>"
                            id="publicacion-<?php echo htmlspecialchars($lista['id_pub']); ?>">
                        <input type="hidden" name="avatar" value="<?php echo htmlspecialchars($session_user_avatar); ?>"
                            id="avatar-<?php echo htmlspecialchars($lista['id_pub']); ?>">
                        <input type="hidden" name="nombre" value="<?php echo htmlspecialchars($session_username); ?>"
                            id="nombre-<?php echo htmlspecialchars($lista['id_pub']); ?>">
                    </label>
                </form>
            </div>
        </div>
        <?php
    }
    $stmt_publicaciones->close(); // Cierra la sentencia de publicaciones
} else {
    echo "<p class='error-message'>Error al preparar la consulta de publicaciones: " . $mysqli->error . "</p>";
}
?>

<div class="pagination-container">
    <?php
    // Enlace a la siguiente página de publicaciones
    if ($IncrimentNum > 0) {
        echo "<a href=\"miactividad.php?id=" . htmlspecialchars($aid) . "&pag=" . htmlspecialchars($IncrimentNum) . "\" class=\"button button-primary\">Siguiente</a>";
    }
    ?>
</div>

<script>
    $(document).ready(function () {
        // Inicializa jscroll para carga infinita
        $('.scroll').jscroll({
            loadingHtml: '<img src="images/invisible.png" alt="Cargando..." />' // Imagen de carga
        });

        // Funcionalidad para el botón de colapsar
        $('.collapse-button').on('click', function () {
            var $cardBody = $(this).closest('.publication-card').find('.publication-body');
            $cardBody.slideToggle(); // Alterna la visibilidad del cuerpo de la publicación
            // Cambia el icono de la flecha
            var $icon = $(this).find('span');
            if ($icon.text() === '▼') {
                $icon.text('▲');
            } else {
                $icon.text('▼');
            }
        });
    });
</script>