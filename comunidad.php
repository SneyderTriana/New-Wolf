<link rel="stylesheet" href="../assets/styles/perfil.css">
<?php
// Inicia la sesión de PHP
session_start();
// Incluye el archivo de configuración de la dirección del documento
require_once __DIR__ . '/../config/addressDocument.php';
// Incluye la cabecera común del sitio web
require_once HEADER;
// Asegura que este archivo contiene la conexión $mysqli
include '../config/conexion_mysqli_oop.php';
// Asegura que este archivo no use funciones mysql_*
include '../lib/php/socialnetwork-lib.php';

// Muestra errores para depuración (¡desactívalo en producción!)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Verifica la conexión a la base de datos
if (!isset($mysqli) || $mysqli->connect_error) {
    die("Error de conexión a la base de datos: " . (isset($mysqli) ? $mysqli->connect_error : "La conexión a MySQLi no está definida en lib/config.php"));
}

// Redirige si el usuario no está logueado
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

// Obtener ID de usuario logueado de la sesión de forma segura
$session_user_id = $_SESSION['id'] ?? null;
$session_user_avatar = $_SESSION['avatar'] ?? 'default.jpg'; // Valor por defecto
$session_username = $_SESSION['usuario'] ?? 'Usuario Desconocido'; // Valor por defecto

// Procesar el formulario de publicación
if (isset($_POST['publicar'])) {
    $publicacion_contenido = trim($_POST['publicacion'] ?? '');
    $imagen_cargada = false;
    $foto_nombre = '';
    $id_foto_insertada = 0;
    $id_album_publicaciones = 0;

    // Validar el contenido de la publicación
    if (empty($publicacion_contenido) && (!isset($_FILES['foto']) || $_FILES['foto']['error'] === UPLOAD_ERR_NO_FILE)) {
        // Reemplazar clase Bootstrap 'alert alert-danger' por una clase personalizada
        echo '<div class="mensaje-error">Debes escribir algo o subir una imagen.</div>';
    } else {
        // Manejo de la imagen
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $file_tmp_name = $_FILES['foto']['tmp_name'];
            $file_name = $_FILES['foto']['name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];

            if (in_array($file_ext, $allowed_ext)) {
                // Generar un nombre único para la imagen
                $unique_name = uniqid('pub_', true) . '.' . $file_ext;
                $destination = "publicaciones/" . $unique_name;

                if (move_uploaded_file($file_tmp_name, $destination)) {
                    $foto_nombre = $unique_name;
                    $imagen_cargada = true;
                } else {
                    // Reemplazar clase Bootstrap
                    echo '<div class="mensaje-error">Error al subir la imagen.</div>';
                }
            } else {
                // Reemplazar clase Bootstrap
                echo '<div class="mensaje-error">Formato de imagen no permitido. Solo JPG, JPEG, PNG, GIF.</div>';
            }
        }

        if (empty($publicacion_contenido) && !$imagen_cargada) {
            // Si no hay contenido de texto ni imagen, no procede
            // Reemplazar clase Bootstrap
            echo '<div class="mensaje-error">Debes escribir algo o subir una imagen para publicar.</div>';
        } else {
            // Obtener el ID de la próxima publicación para asociar la foto (si aplica)
            $next_pub_id = 0;
            $result = $mysqli->query("SHOW TABLE STATUS LIKE 'publicaciones'");
            if ($result) {
                $data = $result->fetch_assoc();
                $next_pub_id = $data['Auto_increment'];
                $result->free();
            }

            // Verificar o crear el álbum "Publicaciones"
            $stmt_album = $mysqli->prepare("SELECT id_alb FROM albumes WHERE usuario = ? AND nombre = 'Publicaciones'");
            if ($stmt_album) {
                $stmt_album->bind_param("i", $session_user_id);
                $stmt_album->execute();
                $stmt_album->bind_result($id_album_publicaciones);
                $stmt_album->fetch();
                $stmt_album->close();
            }

            if ($id_album_publicaciones === 0) { // Si el álbum no existe
                $stmt_create_album = $mysqli->prepare("INSERT INTO albumes (usuario, fecha, nombre) VALUES (?, NOW(), 'Publicaciones')");
                if ($stmt_create_album) {
                    $stmt_create_album->bind_param("i", $session_user_id);
                    $stmt_create_album->execute();
                    $id_album_publicaciones = $mysqli->insert_id; // Obtener el ID del álbum recién creado
                    $stmt_create_album->close();
                } else {
                    // Reemplazar clase Bootstrap
                    echo '<div class="mensaje-error">Error al crear el álbum de publicaciones: ' . $mysqli->error . '</div>';
                }
            }

            // Insertar la foto si se cargó una
            if ($imagen_cargada && $id_album_publicaciones !== 0) {
                $stmt_insert_foto = $mysqli->prepare("INSERT INTO fotos (usuario, fecha, ruta, album, publicacion) VALUES (?, NOW(), ?, ?, ?)");
                if ($stmt_insert_foto) {
                    $stmt_insert_foto->bind_param("isii", $session_user_id, $foto_nombre, $id_album_publicaciones, $next_pub_id);
                    if ($stmt_insert_foto->execute()) {
                        $id_foto_insertada = $mysqli->insert_id;
                    } else {
                        // Reemplazar clase Bootstrap
                        echo '<div class="mensaje-error">Error al insertar la foto en la base de datos: ' . $stmt_insert_foto->error . '</div>';
                    }
                    $stmt_insert_foto->close();
                } else {
                    // Reemplazar clase Bootstrap
                    echo '<div class="mensaje-error">Error al preparar la inserción de foto: ' . $mysqli->error . '</div>';
                }
            }

            // Insertar la publicación
            if ($next_pub_id > 0) { // Asegura que tenemos un ID para la publicación
                $imagen_val = ($id_foto_insertada > 0) ? $id_foto_insertada : 0;
                $album_val = ($id_album_publicaciones > 0) ? $id_album_publicaciones : 0; // Asociar al álbum de publicaciones

                $stmt_insert_pub = $mysqli->prepare("INSERT INTO publicaciones (usuario, fecha, contenido, imagen, album, comentarios) VALUES (?, NOW(), ?, ?, ?, 1)");
                if ($stmt_insert_pub) {
                    $stmt_insert_pub->bind_param("isii", $session_user_id, $publicacion_contenido, $imagen_val, $album_val);
                    if ($stmt_insert_pub->execute()) {
                        echo '<script>window.location="comunidad.php"</script>';
                        exit(); // Importante para detener la ejecución y asegurar la redirección
                    } else {
                        // Reemplazar clase Bootstrap
                        echo '<div class="mensaje-error">Error al insertar la publicación: ' . $stmt_insert_pub->error . '</div>';
                    }
                    $stmt_insert_pub->close();
                } else {
                    // Reemplazar clase Bootstrap
                    echo '<div class="mensaje-error">Error al preparar la inserción de publicación: ' . $mysqli->error . '</div>';
                }
            } else {
                // Reemplazar clase Bootstrap
                echo '<div class="mensaje-error">No se pudo obtener el próximo ID de publicación.</div>';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html class="no-js">

<head>
    
    <meta charset="utf-8">
    
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <link rel="stylesheet" type="text/css" href="css/component.css" />
    <link rel="stylesheet" type="text/css" href="css/custom.css" />
    
    <script>(function (e, t, n) { var r = e.querySelectorAll("html")[0]; r.className = r.className.replace(/(^|\s)no-js(\s|$)/, "$1js$2") })(document, window, 0);</script>

    
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
    
    <script src="js/jquery.jscroll.js"></script>
</head>

<body class="cuerpo-principal">
    <div class="contenedor-general">

        <?php
        // === CAMBIO CRÍTICO AQUÍ ===
// Pasa los argumentos necesarios a Headerb()
// Asumo que Headerb() necesita la conexión a DB y datos del usuario para mostrar el encabezado
        echo Headerb($mysqli, $session_user_id, $session_username, $session_user_avatar);
        echo Side(); // Asegúrate de que Side() no tenga problemas similares
        ?>

        <div class="contenido-principal">

            <section class="seccion-contenido">

                <script type="text/javascript">
                    function validarn(e) {
                        var tecla = (document.all) ? e.keyCode : e.which;
                        if (tecla === 8 || tecla === 9 || tecla === 11) return true; // Backspace, Tab, Vertical Tab
                        var patron = /[A-Za-zñ!#$%&()=?¿¡*+0-9-_á-úÁ-Ú :;,.]/;
                        var te = String.fromCharCode(tecla);
                        return patron.test(te);
                    }
                </script>
                <div class="fila">
                    <div class="columna-principal">
                        <div class="fila">

                            <div class="columna-completa">
                                <div class="caja publicacion-caja">
                                    <div class="caja-cabecera">
                                        <h3 class="titulo-caja">¿Qué estás pensando?</h3>
                                        <button type="button" class="btn-colapso" data-widget="collapse">
                                            <i class="icono-menos"></i>
                                        </button>
                                    </div>

                                    <div class="caja-pie">
                                        <form action="" method="post" enctype="multipart/form-data">
                                            <div class="grupo-entrada">
                                                <textarea name="publicacion" onkeypress="return validarn(event)"
                                                    placeholder="¿Qué estás pensando?" class="campo-texto-publicacion"
                                                    cols="200" rows="3"></textarea>
                                                <br><br><br><br>

                                                <input type="file" name="foto" id="file-1" class="inputfile inputfile-1"
                                                    data-multiple-caption="{count} files selected" />
                                                <label for="file-1"><svg xmlns="http://www.w3.org/2000/svg" width="20"
                                                        height="17" viewBox="0 0 20 17">
                                                        <path
                                                            d="M10 0l-5.2 4.9h3.3v5.1h3.8v-5.1h3.3l-5.2-4.9zm9.3 11.5l-3.2-2.1h-2l3.4 2.6h-3.5c-.1 0-.2.1-.2.1l-.8 2.3h-6l-.8-2.2c-.1-.1-.1-.2-.2-.2h-3.6l3.4-2.6h-2l-3.2 2.1c-.4.3-.7 1-.6 1.5l.6 3.1c.1.5.7.9 1.2.9h16.3c.6 0 1.1-.4 1.3-.9l.6-3.1c.1-.5-.2-1.2-.7-1-5z" />
                                                    </svg> <span>Sube una foto</span></label>
                                                <br>

                                                <button type="submit" name="publicar"
                                                    class="boton boton-publicar">Publicar</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="scroll">
                            <?php require_once 'publicaciones.php'; ?>
                        </div>

                        <script>
                            //Simple codigo para hacer la paginacion scroll
                            $(document).ready(function () {
                                $('.scroll').jscroll({
                                    loadingHtml: '<img src="images/invisible.png" alt="Loading" />'
                                });
                            });
                        </script>
                    </div>

                    <div class="columna-lateral">
                        <div class="caja solicitudes-amistad">
                            <div class="caja-cabecera">
                                <h3 class="titulo-caja">Solicitudes de amistad</h3>
                            </div>
                            <div class="caja-cuerpo">
                                <ul class="lista-productos">
                                    <?php
                                    // Solicitudes de amistad (mysqli)
                                    $stmt_amistad = $mysqli->prepare("SELECT id_ami, de FROM amigos WHERE para = ? AND estado = '0' ORDER BY id_ami DESC LIMIT 4");
                                    if ($stmt_amistad) {
                                        $stmt_amistad->bind_param("i", $_SESSION['id']);
                                        $stmt_amistad->execute();
                                        $result_amistad = $stmt_amistad->get_result();
                                        while ($am = $result_amistad->fetch_assoc()) {
                                            $stmt_user_solicitud = $mysqli->prepare("SELECT usuario, avatar, sexo FROM usuarios WHERE id_use = ?");
                                            $us = ['usuario' => 'Usuario Desconocido', 'avatar' => 'default.jpg', 'sexo' => 'No especificado'];
                                            if ($stmt_user_solicitud) {
                                                $stmt_user_solicitud->bind_param("i", $am['de']);
                                                $stmt_user_solicitud->execute();
                                                $result_user_solicitud = $stmt_user_solicitud->get_result();
                                                if ($result_user_solicitud->num_rows > 0) {
                                                    $us = $result_user_solicitud->fetch_assoc();
                                                }
                                                $stmt_user_solicitud->close();
                                            }
                                            ?>
                                            <li class="elemento-lista">
                                                <div class="imagen-producto">
                                                    <img src="avatars/<?php echo htmlspecialchars($us['avatar']); ?>"
                                                        alt="User Image">
                                                </div>
                                                <div class="info-producto">
                                                    <?php echo htmlspecialchars($us['usuario']); ?>
                                                    <a
                                                        href="solicitud.php?action=aceptar&id=<?php echo htmlspecialchars($am['id_ami']); ?>"><span
                                                            class="etiqueta etiqueta-exito">Aceptar</span></a>
                                                    <br>
                                                    <a
                                                        href="solicitud.php?action=rechazar&id=<?php echo htmlspecialchars($am['id_ami']); ?>"><span
                                                            class="etiqueta etiqueta-peligro">Rechazar</span></a>
                                                    <span class="descripcion-producto">
                                                        <?php echo htmlspecialchars($us['sexo']); ?>
                                                    </span>
                                                </div>
                                            </li>
                                            <?php
                                        }
                                        $stmt_amistad->close();
                                    }
                                    ?>
                                </ul>
                            </div>
                            <div class="caja-pie texto-centrado">
                                <?php
                                $total_solicitudes = 0;
                                $stmt_total_sol = $mysqli->prepare("SELECT COUNT(*) FROM amigos WHERE para = ? AND estado = '0'");
                                if ($stmt_total_sol) {
                                    $stmt_total_sol->bind_param("i", $_SESSION['id']);
                                    $stmt_total_sol->execute();
                                    $stmt_total_sol->bind_result($total_solicitudes);
                                    $stmt_total_sol->fetch();
                                    $stmt_total_sol->close();
                                }

                                if ($total_solicitudes > 4) {
                                    ?>
                                    <a href="javascript:void(0)" class="enlace-mayusculas">Ver todas las solicitudes</a>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <div class="columna-lateral">
                        <div class="caja ultimos-registrados">
                            <div class="caja-cabecera">
                                <h3 class="titulo-caja">Últimos registrados</h3>
                            </div>
                            <div class="caja-cuerpo sin-relleno">
                                <ul class="lista-usuarios">
                                    <?php
                                    // Últimos usuarios registrados (mysqli)
                                    $stmt_registrados = $mysqli->prepare("SELECT avatar, username, fecha_reg FROM usuarios ORDER BY id_use DESC LIMIT 8");
                                    if ($stmt_registrados) {
                                        $stmt_registrados->execute();
                                        $result_registrados = $stmt_registrados->get_result();
                                        while ($reg = $result_registrados->fetch_assoc()) {
                                            // Calcular si el registro es "Hoy"
                                            $fecha_registro = new DateTime($reg['fecha_reg']);
                                            $hoy = new DateTime();
                                            $display_date = $fecha_registro->format('Y-m-d') === $hoy->format('Y-m-d') ? 'Hoy' : $fecha_registro->format('d/m/Y');
                                            ?>
                                            <li>
                                                <img src="avatars/<?php echo htmlspecialchars($reg['avatar']); ?>"
                                                    alt="User Image" width="100" height="200">
                                                <a class="nombre-usuario-lista"
                                                    href="perfil.php?id=<?php // Aquí faltaría el ID del usuario si quisieras enlazarlo ?>"><?php echo htmlspecialchars($reg['username']); ?></a>
                                                <span
                                                    class="fecha-usuario-lista"><?php echo htmlspecialchars($display_date); ?></span>
                                            </li>
                                            <?php
                                        }
                                        $stmt_registrados->close();
                                    }
                                    ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
        <aside class="barra-lateral-control">
            <ul class="pestañas-barra-lateral">
                <li><a href="#control-sidebar-home-tab" data-toggle="tab"><i class="fa fa-home"></i></a></li>
                <li><a href="#control-sidebar-settings-tab" data-toggle="tab"><i class="fa fa-gears"></i></a></li>
            </ul>
            <div class="contenido-pestaña">
                <div class="panel-pestaña" id="control-sidebar-home-tab">
                    <h3 class="encabezado-barra-lateral">Actividad Reciente</h3>
                    <ul class="menu-barra-lateral">
                        <li>
                            <a href="javascript:void(0)">
                                <i class="icono-menu icono-cumpleaños"></i>

                                <div class="info-menu">
                                    <h4 class="subtitulo-barra-lateral">Cumpleaños de Langdon</h4>

                                    <p>Será 23 el 24 de abril</p>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a href="javascript:void(0)">
                                <i class="icono-menu icono-usuario"></i>

                                <div class="info-menu">
                                    <h4 class="subtitulo-barra-lateral">Frodo Actualizó su Perfil</h4>

                                    <p>Nuevo teléfono +1(800)555-1234</p>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a href="javascript:void(0)">
                                <i class="icono-menu icono-email"></i>

                                <div class="info-menu">
                                    <h4 class="subtitulo-barra-lateral">Nora se unió a la lista de correo</h4>

                                    <p>nora@example.com</p>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a href="javascript:void(0)">
                                <i class="icono-menu icono-codigo"></i>

                                <div class="info-menu">
                                    <h4 class="subtitulo-barra-lateral">Trabajo Cron 254 Ejecutado</h4>

                                    <p>Tiempo de ejecución 5 segundos</p>
                                </div>
                            </a>
                        </li>
                    </ul>
                    <h3 class="encabezado-barra-lateral">Progreso de Tareas</h3>
                    <ul class="menu-barra-lateral">
                        <li>
                            <a href="javascript:void(0)">
                                <h4 class="subtitulo-barra-lateral">
                                    Diseño de Plantilla Personalizada
                                    <span class="etiqueta etiqueta-peligro">70%</span>
                                </h4>

                                <div class="barra-progreso">
                                    <div class="barra-progreso-rojo" style="width: 70%"></div>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a href="javascript:void(0)">
                                <h4 class="subtitulo-barra-lateral">
                                    Actualizar Currículum
                                    <span class="etiqueta etiqueta-exito">95%</span>
                                </h4>

                                <div class="barra-progreso">
                                    <div class="barra-progreso-verde" style="width: 95%"></div>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a href="javascript:void(0)">
                                <h4 class="subtitulo-barra-lateral">
                                    Integración Laravel
                                    <span class="etiqueta etiqueta-advertencia">50%</span>
                                </h4>

                                <div class="barra-progreso">
                                    <div class="barra-progreso-amarillo" style="width: 50%"></div>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a href="javascript:void(0)">
                                <h4 class="subtitulo-barra-lateral">
                                    Framework Back End
                                    <span class="etiqueta etiqueta-primaria">68%</span>
                                </h4>

                                <div class="barra-progreso">
                                    <div class="barra-progreso-azul" style="width: 68%"></div>
                                </div>
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="panel-pestaña" id="control-sidebar-settings-tab">
                    <form method="post">
                        <h3 class="encabezado-barra-lateral">Configuración General</h3>

                        <div class="grupo-formulario">
                            <label class="subtitulo-barra-lateral">
                                Reportar uso del panel
                                <input type="checkbox" class="flotar-derecha" checked>
                            </label>

                            <p>
                                Alguna información sobre esta opción de configuración general
                            </p>
                        </div>
                        <div class="grupo-formulario">
                            <label class="subtitulo-barra-lateral">
                                Permitir redirección de correo
                                <input type="checkbox" class="flotar-derecha" checked>
                            </label>

                            <p>
                                Otros conjuntos de opciones están disponibles
                            </p>
                        </div>
                        <div class="grupo-formulario">
                            <label class="subtitulo-barra-lateral">
                                Exponer nombre de autor en publicaciones
                                <input type="checkbox" class="flotar-derecha" checked>
                            </label>

                            <p>
                                Permitir al usuario mostrar su nombre en las publicaciones del blog
                            </p>
                        </div>
                        <h3 class="encabezado-barra-lateral">Configuración de Chat</h3>

                        <div class="grupo-formulario">
                            <label class="subtitulo-barra-lateral">
                                Mostrarme como en línea
                                <input type="checkbox" class="flotar-derecha" checked>
                            </label>
                        </div>
                        <div class="grupo-formulario">
                            <label class="subtitulo-barra-lateral">
                                Desactivar notificaciones
                                <input type="checkbox" class="flotar-derecha">
                            </label>
                        </div>
                        <div class="grupo-formulario">
                            <label class="subtitulo-barra-lateral">
                                Eliminar historial de chat
                                <a href="javascript:void(0)" class="texto-rojo flotar-derecha"><i
                                        class="icono-basura"></i></a>
                            </label>
                        </div>
                    </form>
                </div>
            </div>
            
        </aside>
        <div class="fondo-barra-lateral-control"></div>

    </div>
    <script src="js/custom-file-input.js"></script>
</body>

</html>