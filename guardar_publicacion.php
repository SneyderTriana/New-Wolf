<?php
session_start();
// Comentar estas líneas en producción por seguridad
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'lib/config.php'; // Asegúrate de que este archivo contiene la conexión $mysqli
include 'lib/socialnetwork-lib.php'; // Asegúrate de que este archivo no use funciones mysql_*

// Verifica la conexión a la base de datos
if (!isset($mysqli) || $mysqli->connect_error) {
    die("Error de conexión a la base de datos: " . (isset($mysqli) ? $mysqli->connect_error : "La conexión a MySQLi no está definida en lib/config.php"));
}

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit(); // Siempre usar exit() después de header Location
}

// Obtener ID de usuario logueado de la sesión de forma segura
// Coalesce operator (??) para evitar "Undefined index" y establecer un valor por defecto
$session_user_id = $_SESSION['id'] ?? null;
$session_user_avatar = $_SESSION['avatar'] ?? 'default.jpg'; // Valor por defecto si no está seteado
$session_username = $_SESSION['usuario'] ?? 'Usuario Desconocido'; // Valor por defecto

// --- Depuración: Verifica el ID de sesión ---
// Esto te ayudará a ver si $_SESSION['id'] está llegando correctamente
// Comenta o borra esta línea en producción
echo "DEBUG: session_user_id = " . htmlspecialchars((string)$session_user_id) . "<br>";
// ---------------------------------------------

// Si el ID de usuario de sesión no está disponible, no procesar publicaciones ni álbumes
if ($session_user_id === null || !is_numeric($session_user_id)) {
    echo '<div class="alert alert-danger">Error: ID de usuario de sesión no disponible. Por favor, vuelve a iniciar sesión.</div>';
    // Opcional: Redirigir al login si el ID de sesión es crítico
    // header("Location: login.php");
    // exit();
}


// Procesar el formulario de publicación
if (isset($_POST['publicar'])) {
    $publicacion_contenido = trim($_POST['publicacion'] ?? '');
    $imagen_cargada = false;
    $foto_nombre = '';
    $id_foto_insertada = 0;
    $id_album_publicaciones = 0;

    // Validar el contenido de la publicación
    if (empty($publicacion_contenido) && (!isset($_FILES['foto']) || $_FILES['foto']['error'] === UPLOAD_ERR_NO_FILE)) {
        echo '<div class="alert alert-danger">Debes escribir algo o subir una imagen.</div>';
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
                    echo '<div class="alert alert-danger">Error al subir la imagen.</div>';
                }
            } else {
                echo '<div class="alert alert-danger">Formato de imagen no permitido. Solo JPG, JPEG, PNG, GIF.</div>';
            }
        }

        // Si no hay contenido de texto ni imagen, no procede
        if (empty($publicacion_contenido) && !$imagen_cargada) {
             echo '<div class="alert alert-danger">Debes escribir algo o subir una imagen para publicar.</div>';
        } elseif ($session_user_id !== null && is_numeric($session_user_id)) { // Solo procede si el ID de sesión es válido
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
            } else {
                 echo '<div class="alert alert-danger">Error al preparar consulta de álbum: ' . $mysqli->error . '</div>';
            }

            if ($id_album_publicaciones === 0) { // Si el álbum no existe
                $stmt_create_album = $mysqli->prepare("INSERT INTO albumes (usuario, fecha, nombre) VALUES (?, NOW(), 'Publicaciones')");
                if ($stmt_create_album) {
                    $stmt_create_album->bind_param("i", $session_user_id);
                    if ($stmt_create_album->execute()) {
                         $id_album_publicaciones = $mysqli->insert_id; // Obtener el ID del álbum recién creado
                    } else {
                         echo '<div class="alert alert-danger">Error al ejecutar creación de álbum: ' . $stmt_create_album->error . '</div>';
                    }
                    $stmt_create_album->close();
                } else {
                    echo '<div class="alert alert-danger">Error al preparar la creación del álbum: ' . $mysqli->error . '</div>';
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
                        echo '<div class="alert alert-danger">Error al insertar la foto en la base de datos: ' . $stmt_insert_foto->error . '</div>';
                    }
                    $stmt_insert_foto->close();
                } else {
                    echo '<div class="alert alert-danger">Error al preparar la inserción de foto: ' . $mysqli->error . '</div>';
                }
            }

            // Insertar la publicación
            if ($next_pub_id > 0 && $session_user_id !== null && is_numeric($session_user_id)) { // Asegura que tenemos un ID para la publicación y el user ID
                $imagen_val = ($id_foto_insertada > 0) ? $id_foto_insertada : 0;
                $album_val = ($id_album_publicaciones > 0) ? $id_album_publicaciones : 0; // Asociar al álbum de publicaciones

                $stmt_insert_pub = $mysqli->prepare("INSERT INTO publicaciones (usuario, fecha, contenido, imagen, album, comentarios) VALUES (?, NOW(), ?, ?, ?, 1)");
                if ($stmt_insert_pub) {
                    $stmt_insert_pub->bind_param("isii", $session_user_id, $publicacion_contenido, $imagen_val, $album_val);
                    if ($stmt_insert_pub->execute()) {
                        echo '<script>window.location="index.php"</script>';
                        exit(); // Importante para detener la ejecución y asegurar la redirección
                    } else {
                        echo '<div class="alert alert-danger">Error al insertar la publicación: ' . $stmt_insert_pub->error . '</div>';
                    }
                    $stmt_insert_pub->close();
                } else {
                    echo '<div class="alert alert-danger">Error al preparar la inserción de publicación: ' . $mysqli->error . '</div>';
                }
            } else {
                echo '<div class="alert alert-danger">No se pudo obtener el próximo ID de publicación o el ID de usuario de sesión no es válido.</div>';
            }
        } else {
            echo '<div class="alert alert-danger">Error crítico: ID de usuario de sesión no disponible para crear o buscar álbum.</div>';
        }
    }
}
?>
<!DOCTYPE html>
<html class="no-js">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>REDSOCIAL</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">
    <link rel="stylesheet" href="dist/css/AdminLTE.min.css">
    <link rel="stylesheet" href="dist/css/skins/_all-skins.min.css">
    <link rel="stylesheet" type="text/css" href="css/component.css" />
    <script>(function(e,t,n){var r=e.querySelectorAll("html")[0];r.className=r.className.replace(/(^|\s)no-js(\s|$)/,"$1js$2")})(document,window,0);</script>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
    <script src="js/jquery.jscroll.js"></script>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

<?php
// === CAMBIO CRÍTICO AQUÍ ===
// Pasa los argumentos necesarios a Headerb()
// Asumo que Headerb() necesita la conexión a DB y datos del usuario para mostrar el encabezado
// Es crucial que Headerb() maneje los posibles valores nulos si $_SESSION no está completo
echo Headerb($mysqli, $session_user_id, $session_username, $session_user_avatar);
echo Side(); // Asegúrate de que Side() no tenga problemas similares
?>

    <div class="content-wrapper">

        <section class="content">

        <script type="text/javascript">
        function validarn(e) {
            var tecla = (document.all) ? e.keyCode : e.which;
            if (tecla === 8 || tecla === 9 || tecla === 11) return true; // Backspace, Tab, Vertical Tab
            var patron = /[A-Za-zñ!#$%&()=?¿¡*+0-9-_á-úÁ-Ú :;,.]/;
            var te = String.fromCharCode(tecla);
            return patron.test(te);
        }
        </script>
        <div class="row">
            <div class="col-md-8">
              <div class="row">

                <div class="col-md-12">
                  <div class="box box-primary direct-chat direct-chat-warning">
                    <div class="box-header with-border">
                      <h3 class="box-title">¿Qué estás pensando?</h3>
                      <button type="button" class="btn btn-box-tool" data-widget="collapse">
                        <i class="fa fa-minus"></i>
                      </button>
                    </div>

                    <div class="box-footer">
                      <form action="" method="post" enctype="multipart/form-data">
                        <div class="input-group">
                          <textarea name="publicacion" onkeypress="return validarn(event)" placeholder="¿Qué estás pensando?" class="form-control" cols="200" rows="3"></textarea>
                          <br><br><br><br>

                        <input type="file" name="foto" id="file-1" class="inputfile inputfile-1" data-multiple-caption="{count} files selected"/>
                          <label for="file-1"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="17" viewBox="0 0 20 17"><path d="M10 0l-5.2 4.9h3.3v5.1h3.8v-5.1h3.3l-5.2-4.9zm9.3 11.5l-3.2-2.1h-2l3.4 2.6h-3.5c-.1 0-.2.1-.2.1l-.8 2.3h-6l-.8-2.2c-.1-.1-.1-.2-.2-.2h-3.6l3.4-2.6h-2l-3.2 2.1c-.4.3-.7 1-.6 1.5l.6 3.1c.1.5.7.9 1.2.9h16.3c.6 0 1.1-.4 1.3-.9l.6-3.1c.1-.5-.2-1.2-.7-1-5z"/></svg> <span>Sube una foto</span></label>
                        <br>

                          <button type="submit" name="publicar" class="btn btn-primary btn-flat">Publicar</button>
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
                $(document).ready(function() {
                  $('.scroll').jscroll({
                    loadingHtml: '<img src="images/invisible.png" alt="Loading" />'
                  });
                });
              </script>
              </div>

            <div class="col-md-4">
              <div class="box box-primary">
                <div class="box-header with-border">
                  <h3 class="box-title">Solicitudes de amistad</h3>
                </div>
                <div class="box-body">
                  <ul class="products-list product-list-in-box">
                  <?php
                  // Solicitudes de amistad (mysqli)
                  // Asegúrate de que $_SESSION['id'] es válido antes de usarlo
                  if ($session_user_id !== null && is_numeric($session_user_id)) {
                      $stmt_amistad = $mysqli->prepare("SELECT id_ami, de FROM amigos WHERE para = ? AND estado = '0' ORDER BY id_ami DESC LIMIT 4");
                      if ($stmt_amistad) {
                          $stmt_amistad->bind_param("i", $session_user_id);
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
                                <li class="item">
                                  <div class="product-img">
                                    <img src="avatars/<?php echo htmlspecialchars($us['avatar']); ?>" alt="User Image">
                                  </div>
                                  <div class="product-info">
                                    <?php echo htmlspecialchars($us['usuario']); ?>
                                    <a href="solicitud.php?action=aceptar&id=<?php echo htmlspecialchars($am['id_ami']); ?>"><span class="label label-success pull-right">Aceptar</span></a>
                                    <br>
                                    <a href="solicitud.php?action=rechazar&id=<?php echo htmlspecialchars($am['id_ami']); ?>"><span class="label label-danger pull-right">Rechazar</span></a>
                                    <span class="product-description">
                                      <?php echo htmlspecialchars($us['sexo']); ?>
                                    </span>
                                  </div>
                                </li>
                  <?php
                          }
                          $stmt_amistad->close();
                      }
                  } else {
                      // Mensaje si el ID de sesión no es válido para las solicitudes de amistad
                      echo '<div class="alert alert-info">No se pueden cargar las solicitudes de amistad: ID de usuario no válido.</div>';
                  }
                  ?>
                  </ul>
                </div>
                <div class="box-footer text-center">
                <?php
                $total_solicitudes = 0;
                // Asegúrate de que $_SESSION['id'] es válido antes de usarlo
                if ($session_user_id !== null && is_numeric($session_user_id)) {
                    $stmt_total_sol = $mysqli->prepare("SELECT COUNT(*) FROM amigos WHERE para = ? AND estado = '0'");
                    if ($stmt_total_sol) {
                        $stmt_total_sol->bind_param("i", $session_user_id);
                        $stmt_total_sol->execute();
                        $stmt_total_sol->bind_result($total_solicitudes);
                        $stmt_total_sol->fetch();
                        $stmt_total_sol->close();
                    }
                }

                if ($total_solicitudes > 4) {
                ?>
                  <a href="javascript:void(0)" class="uppercase">Ver todas las solicitudes</a>
                <?php } ?>
                </div>
                </div>
              </div>
            <div class="col-md-4">
              <div class="box box-danger">
                <div class="box-header with-border">
                  <h3 class="box-title">Últimos registrados</h3>
                </div>
                <div class="box-body no-padding">
                  <ul class="users-list clearfix">
                  <?php
                  // Últimos usuarios registrados (mysqli)
                  $stmt_registrados = $mysqli->prepare("SELECT avatar, usuario, fecha_reg FROM usuarios ORDER BY id_use DESC LIMIT 8");
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
                          <img src="avatars/<?php echo htmlspecialchars($reg['avatar']); ?>" alt="User Image" width="100" height="200">
                          <a class="users-list-name" href="perfil.php?id=<?php // echo htmlspecialchars($reg['id_use']); ?>"><?php echo htmlspecialchars($reg['usuario']); ?></a>
                          <span class="users-list-date"><?php echo htmlspecialchars($display_date); ?></span>
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
    <aside class="control-sidebar control-sidebar-dark">
        <ul class="nav nav-tabs nav-justified control-sidebar-tabs">
            <li><a href="#control-sidebar-home-tab" data-toggle="tab"><i class="fa fa-home"></i></a></li>
            <li><a href="#control-sidebar-settings-tab" data-toggle="tab"><i class="fa fa-gears"></i></a></li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane" id="control-sidebar-home-tab">
                <h3 class="control-sidebar-heading">Recent Activity</h3>
                <ul class="control-sidebar-menu">
                    <li>
                        <a href="javascript:void(0)">
                            <i class="menu-icon fa fa-birthday-cake bg-red"></i>

                            <div class="menu-info">
                                <h4 class="control-sidebar-subheading">Langdon's Birthday</h4>

                                <p>Will be 23 on April 24th</p>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="javascript:void(0)">
                            <i class="menu-icon fa fa-user bg-yellow"></i>

                            <div class="menu-info">
                                <h4 class="control-sidebar-subheading">Frodo Updated His Profile</h4>

                                <p>New phone +1(800)555-1234</p>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="javascript:void(0)">
                            <i class="menu-icon fa fa-envelope-o bg-light-blue"></i>

                            <div class="menu-info">
                                <h4 class="control-sidebar-subheading">Nora Joined Mailing List</h4>

                                <p>nora@example.com</p>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="javascript:void(0)">
                            <i class="menu-icon fa fa-file-code-o bg-green"></i>

                            <div class="menu-info">
                                <h4 class="control-sidebar-subheading">Cron Job 254 Executed</h4>

                                <p>Execution time 5 seconds</p>
                            </div>
                        </a>
                    </li>
                </ul>
                <h3 class="control-sidebar-heading">Tasks Progress</h3>
                <ul class="control-sidebar-menu">
                    <li>
                        <a href="javascript:void(0)">
                            <h4 class="control-sidebar-subheading">
                                Custom Template Design
                                <span class="label label-danger pull-right">70%</span>
                            </h4>

                            <div class="progress progress-xxs">
                                <div class="progress-bar progress-bar-danger" style="width: 70%"></div>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="javascript:void(0)">
                            <h4 class="control-sidebar-subheading">
                                Update Resume
                                <span class="label label-success pull-right">95%</span>
                            </h4>

                            <div class="progress progress-xxs">
                                <div class="progress-bar progress-bar-success" style="width: 95%"></div>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="javascript:void(0)">
                            <h4 class="control-sidebar-subheading">
                                Laravel Integration
                                <span class="label label-warning pull-right">50%</span>
                            </h4>

                            <div class="progress progress-xxs">
                                <div class="progress-bar progress-bar-warning" style="width: 50%"></div>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="javascript:void(0)">
                            <h4 class="control-sidebar-subheading">
                                Back End Framework
                                <span class="label label-primary pull-right">68%</span>
                            </h4>

                            <div class="progress progress-xxs">
                                <div class="progress-bar progress-bar-primary" style="width: 68%"></div>
                            </div>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="tab-pane" id="control-sidebar-settings-tab">
                <form method="post">
                    <h3 class="control-sidebar-heading">General Settings</h3>

                    <div class="form-group">
                        <label class="control-sidebar-subheading">
                            Report panel usage
                            <input type="checkbox" class="pull-right" checked>
                        </label>

                        <p>
                            Some information about this general settings option
                        </p>
                    </div>
                    <div class="form-group">
                        <label class="control-sidebar-subheading">
                            Allow mail redirect
                            <input type="checkbox" class="pull-right" checked>
                        </label>

                        <p>
                            Other sets of options are available
                        </p>
                    </div>
                    <div class="form-group">
                        <label class="control-sidebar-subheading">
                            Expose author name in posts
                            <input type="checkbox" class="pull-right" checked>
                        </label>

                        <p>
                            Allow the user to show his name in blog posts
                        </p>
                    </div>
                    <h3 class="control-sidebar-heading">Chat Settings</h3>

                    <div class="form-group">
                        <label class="control-sidebar-subheading">
                            Show me as online
                            <input type="checkbox" class="pull-right" checked>
                        </label>
                    </div>
                    <div class="form-group">
                        <label class="control-sidebar-subheading">
                            Turn off notifications
                            <input type="checkbox" class="pull-right">
                        </label>
                    </div>
                    <div class="form-group">
                        <label class="control-sidebar-subheading">
                            Delete chat history
                            <a href="javascript:void(0)" class="text-red pull-right"><i class="fa fa-trash-o"></i></a>
                        </label>
                    </div>
                    </form>
            </div>
            </div>
    </aside>
    <div class="control-sidebar-bg"></div>

</div>
<script src="bootstrap/js/bootstrap.min.js"></script>
<script src="plugins/fastclick/fastclick.js"></script>
<script src="dist/js/app.min.js"></script>
<script src="plugins/sparkline/jquery.sparkline.min.js"></script>
<script src="plugins/slimScroll/jquery.slimscroll.min.js"></script>
<script src="js/custom-file-input.js"></script>
</body>
</html>