<?php
// Incluye archivos de configuración y conexión a la base de datos.
require_once __DIR__ . '/../config/addressDocument.php';
require_once HEADER;
include __DIR__ . '/../config/conexion_mysqli_oop.php';

// Habilita la visualización de errores para depuración.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Verifica la conexión a la base de datos; si falla, termina la ejecución.
if (!isset($mysqli) || $mysqli->connect_error) {
    die("Error crítico: La conexión a la base de datos no está disponible. Verifique lib/config.php");
}

// Inicia la sesión si no ha sido iniciada.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Define la ruta base para los avatares.
$avatar_path = AVATAR;
?>

<script type="text/javascript" src="js/likes.js"></script>
<script type="text/javascript">
// Define una constante JavaScript con la ruta de los avatares.
const AVATAR_PATH = "<?php echo $avatar_path; ?>";

$(document).ready(function () {
    // Función para escapar caracteres HTML especiales y prevenir XSS.
    function htmlspecialchars(str) {
        if (typeof str !== 'string') return str;
        var map = {
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
        };
        return str.replace(/[&<>"']/g, function (m) { return map[m]; });
    }

    // Maneja el evento de presionar Enter en el campo de comentario.
    $(".enviar-btn").keypress(function (event) {
        if (event.which == 13) { // Si la tecla es Enter (código 13).
            event.preventDefault(); // Evita el envío del formulario.

            // Obtiene el ID de la publicación y los datos del usuario/comentario.
            var getpID = $(this).parent().attr('id').replace('record-', '');
            var usuario = $("input#usuario").val();
            var comentario = $("#comentario-" + getpID).val();
            var publicacion = getpID;
            var avatar = $("input#avatar").val();
            var nombre = $("input#nombre").val();

            // Genera la fecha y hora actual para mostrar.
            var now = new Date();
            var date_show = now.getDate() + '-' + (now.getMonth() + 1) + '-' + now.getFullYear() + ' ' +
                            now.getHours().toString().padStart(2, '0') + ':' +
                            now.getMinutes().toString().padStart(2, '0') + ':' +
                            now.getSeconds().toString().padStart(2, '0');

            // Valida que el comentario no esté vacío.
            if (comentario.trim() === '') {
                alert('Debes añadir un comentario');
                return false;
            }

            // Realiza la petición AJAX para agregar el comentario.
            $.ajax({
                type: "POST",
                url: "agregarcomentario.php",
                data: 'usuario=' + encodeURIComponent(usuario) + '&comentario=' + encodeURIComponent(comentario) + '&publicacion=' + encodeURIComponent(publicacion),
                success: function (response) {
                    if (response.trim() === "success") {
                        // Si el comentario se añadió con éxito, lo agrega dinámicamente al DOM.
                        $('#nuevocomentario' + getpID).append(
                            '<div class="box-comment">' +
                            '<img class="img-circle img-sm" src="' + AVATAR_PATH + '/' + htmlspecialchars(avatar) + '" alt="Avatar">' +
                            '<div class="comment-text">' +
                            '<span class="username"> ' + htmlspecialchars(nombre) +
                            '<span class="text-muted pull-right">' + date_show + '</span></span>' +
                            htmlspecialchars(comentario) +
                            '</div>' +
                            '</div>'
                        );
                        // Limpia el campo de comentario.
                        $("#comentario-" + getpID).val('');
                    } else {
                        // Muestra un mensaje de error si la respuesta no es "success".
                        alert("Error al agregar comentario: " + response);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    // Muestra un mensaje de error en caso de fallo de comunicación.
                    alert("Hubo un error de comunicación al enviar el comentario.");
                }
            });
        }
    });
});
</script>

<?php
// Configuración para la paginación de publicaciones.
$CantidadMostrar = 5; // Número de publicaciones a mostrar por página.
// Determina la página actual, por defecto 1.
$compag = (int)(!isset($_GET['pag'])) ? 1 : $_GET['pag'];

// Consulta el número total de publicaciones en la base de datos.
$stmt_total_reg = $mysqli->prepare("SELECT COUNT(*) AS total FROM publicaciones");
$stmt_total_reg->execute();
$result_total_reg = $stmt_total_reg->get_result();
$totalr = $result_total_reg->fetch_assoc()['total'];
$stmt_total_reg->close();

// Calcula el número total de páginas.
$TotalRegistro = ceil($totalr / $CantidadMostrar);
// Calcula el número de la siguiente página para la navegación.
$IncrimentNum = (($compag + 1) <= $TotalRegistro) ? ($compag + 1) : 0;
// Calcula el offset para la consulta SQL (desde dónde empezar a traer registros).
$offset = ($compag - 1) * $CantidadMostrar;

// Prepara y ejecuta la consulta para obtener las publicaciones de la página actual.
$stmt_publicaciones = $mysqli->prepare("SELECT id_pub, usuario, contenido, fecha, imagen, likes FROM publicaciones ORDER BY id_pub DESC LIMIT ?, ?");
$stmt_publicaciones->bind_param("ii", $offset, $CantidadMostrar);
$stmt_publicaciones->execute();
$consulta = $stmt_publicaciones->get_result();

// Muestra un mensaje si no hay publicaciones.
if ($consulta->num_rows === 0) echo "<p style='text-align: center; margin-top: 20px;'>No hay publicaciones disponibles.</p>";

// Itera sobre cada publicación obtenida.
while ($lista = $consulta->fetch_assoc()) {
    $userid = $lista['usuario'];
    // Consulta los datos del usuario que realizó la publicación.
    $stmt_usuariob = $mysqli->prepare("SELECT id_use, username, avatar FROM usuarios WHERE id_use = ?");
    $stmt_usuariob->bind_param("i", $userid);
    $stmt_usuariob->execute();
    $use = $stmt_usuariob->get_result()->fetch_assoc();
    $stmt_usuariob->close();

    $fot_ruta = '';
    // Si la publicación tiene una imagen, obtiene la ruta de la foto.
    if ($lista['imagen'] != 0) {
        $stmt_fotos = $mysqli->prepare("SELECT ruta FROM fotos WHERE publicacion = ?");
        $stmt_fotos->bind_param("i", $lista['id_pub']);
        $stmt_fotos->execute();
        $row_foto = $stmt_fotos->get_result()->fetch_assoc();
        if ($row_foto) $fot_ruta = $row_foto['ruta'];
        $stmt_fotos->close();
    }
?>
<div class="box box-widget">
    <div class="box-header with-border">
        <div class="user-block">
            <img class="img-circle" src="<?php echo AVATAR . '/' . htmlspecialchars($use['avatar'] ?? ''); ?>" alt="User Image">
            <span class="description" onclick="location.href='perfil.php?id=<?php echo htmlspecialchars($use['id_use'] ?? ''); ?>';" style="cursor:pointer; color: #3C8DBC;">
                <?php echo htmlspecialchars($use['username'] ?? ''); ?>
            </span>
            <span class="description"><?php echo htmlspecialchars($lista['fecha'] ?? ''); ?></span>
        </div>
        <div class="box-tools">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
        </div>
    </div>
    <div class="box-body">
        <p><?php echo htmlspecialchars($lista['contenido'] ?? ''); ?></p>
        <?php if ($lista['imagen'] != 0 && !empty($fot_ruta)) { ?>
            <img src="publicaciones/<?php echo htmlspecialchars($fot_ruta); ?>" width="100%">
        <?php } ?>
        <br><br>
        <?php
        // Consulta el número de comentarios para la publicación actual.
        $stmt_num_comen = $mysqli->prepare("SELECT COUNT(*) AS num_comen FROM comentarios WHERE publicacion = ?");
        $stmt_num_comen->bind_param("i", $lista['id_pub']);
        $stmt_num_comen->execute();
        $numcomen = $stmt_num_comen->get_result()->fetch_assoc()['num_comen'];
        $stmt_num_comen->close();
        ?>
        <ul class="list-inline">
            <?php
            $user_has_liked = false;
            // Verifica si el usuario actual ya le dio "me gusta" a esta publicación.
            if (isset($_SESSION['id'])) {
                $stmt_likes_query = $mysqli->prepare("SELECT id_lik FROM likes WHERE post = ? AND usuario = ?");
                $stmt_likes_query->bind_param("ii", $lista['id_pub'], $_SESSION['id']);
                $stmt_likes_query->execute();
                if ($stmt_likes_query->get_result()->num_rows > 0) $user_has_liked = true;
                $stmt_likes_query->close();
            }
            ?>
            <li><div class="btn btn-default btn-xs like" id="<?php echo htmlspecialchars($lista['id_pub']); ?>">
                <i class="fa fa-thumbs-o-up"></i> <?php echo $user_has_liked ? 'No me gusta' : 'Me gusta'; ?> </div>
                <span id="likes_<?php echo htmlspecialchars($lista['id_pub']); ?>"> (<?php echo htmlspecialchars($lista['likes']); ?>)</span>
            </li>
            <li class="pull-right">
                <span class="link-black text-sm"><i class="fa fa-comments-o margin-r-5"></i> Comentarios (<?php echo htmlspecialchars($numcomen); ?>)</span>
            </li>
        </ul>
    </div>
    <div class="box-footer box-comments">
        <?php
        // Consulta los últimos 2 comentarios de la publicación.
        $stmt_comentarios = $mysqli->prepare("SELECT id_com, usuario, comentario, fecha FROM comentarios WHERE publicacion = ? ORDER BY id_com DESC LIMIT 2");
        $stmt_comentarios->bind_param("i", $lista['id_pub']);
        $stmt_comentarios->execute();
        $result_comentarios = $stmt_comentarios->get_result();
        // Itera sobre los comentarios para mostrarlos.
        while ($com = $result_comentarios->fetch_assoc()) {
            // Consulta los datos del usuario que realizó el comentario.
            $stmt_usuarioc = $mysqli->prepare("SELECT username, avatar FROM usuarios WHERE id_use = ?");
            $stmt_usuarioc->bind_param("i", $com['usuario']);
            $stmt_usuarioc->execute();
            $usec = $stmt_usuarioc->get_result()->fetch_assoc();
            $stmt_usuarioc->close();
        ?>
        <div class="box-comment">
            <img class="img-circle img-sm" src="<?php echo AVATAR . '/' . htmlspecialchars($usec['avatar'] ?? ''); ?>" alt="Avatar">
            <div class="comment-text">
                <span class="username">
                    <?php echo htmlspecialchars($usec['username'] ?? ''); ?>
                    <span class="text-muted pull-right"><?php echo htmlspecialchars($com['fecha'] ?? ''); ?></span>
                </span>
                <?php echo htmlspecialchars($com['comentario'] ?? ''); ?>
            </div>
        </div>
        <?php } $stmt_comentarios->close(); ?>
        <?php if ($numcomen > 2) { ?>
        <br><center><span onclick="location.href='publicacion.php?id=<?php echo htmlspecialchars($lista['id_pub']); ?>';" style="cursor:pointer; color: #3C8DBC;">Ver todos los comentarios</span></center>
        <?php } ?>
        <div id="nuevocomentario<?php echo htmlspecialchars($lista['id_pub']); ?>"></div>
        <br>
        <form method="post" action="">
            <label id="record-<?php echo htmlspecialchars($lista['id_pub']); ?>">
                <input type="text" class="enviar-btn form-control input-sm" style="width: 800px;" placeholder="Escribe un comentario" name="comentario" id="comentario-<?php echo htmlspecialchars($lista['id_pub']); ?>">
                <input type="hidden" name="usuario" value="<?php echo htmlspecialchars($_SESSION['id'] ?? ''); ?>" id="usuario">
                <input type="hidden" name="publicacion" value="<?php echo htmlspecialchars($lista['id_pub']); ?>" id="publicacion">
                <input type="hidden" name="avatar" value="<?php echo htmlspecialchars($_SESSION['avatar'] ?? ''); ?>" id="avatar">
                <input type="hidden" name="nombre" value="<?php echo htmlspecialchars($_SESSION['usuario'] ?? ''); ?>" id="nombre">
            </label>
        </form>
    </div>
</div><br><br>
<?php }
$stmt_publicaciones->close();
// Muestra el enlace "Siguiente" para la paginación si hay más publicaciones.
if ($IncrimentNum > 0) echo "<a href=\"publicaciones.php?pag=$IncrimentNum\">Siguiente</a>";
?>