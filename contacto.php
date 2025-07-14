<link rel="stylesheet" href="/login_1/assets/styles/style.css"> <!-- Enlace a hoja de estilos -->

<?php
// Se requiere el archivo de configuración que contiene rutas importantes
require_once __DIR__ . '/../config/addressDocument.php';
// Se carga la cabecera de la página desde una constante definida
require_once HEADER;
require_once PRESENTACION;

// Se incluyen los archivos necesarios de PHPMailer desde la carpeta lib
require_once __DIR__ . '/../lib/PHPMailer-master/src/PHPMailer.php';
require_once __DIR__ . '/../lib/PHPMailer-master/src/SMTP.php';
require_once __DIR__ . '/../lib/PHPMailer-master/src/Exception.php';

// Se importan las clases necesarias del espacio de nombres de PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Se definen constantes con las credenciales del servidor SMTP
// ⚠️ Esto no es seguro para producción, solo debe usarse en desarrollo
define('SMTP_USERNAME', 'sneyder.gonzalez156@gmail.com'); // Usuario SMTP (correo de envío)
define('SMTP_PASSWORD', 'bdsmsedobjeffxet'); // Contraseña SMTP (clave de aplicación)
define('CONTACT_EMAIL_TO', 'sneyder.gonzalez156@gmail.com'); // Correo de destino
define('CONTACT_FROM_NAME', 'New Wolf'); // Nombre que aparecerá como remitente

// Se verifica si el formulario fue enviado mediante método POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Se obtiene y limpia el nombre del remitente
    $name = htmlspecialchars($_POST["name"] ?? '');
    // Se limpia y filtra el correo ingresado
    $email = filter_var($_POST["email"] ?? '', FILTER_SANITIZE_EMAIL);
    // Se obtiene y limpia el mensaje ingresado
    $message = htmlspecialchars($_POST["message"] ?? '');

    // Si alguno de los campos está vacío, se redirige con mensaje de error
    if (empty($name) || empty($email) || empty($message)) {
        header("Location: contacto.php?msg=empty_fields");
        exit;
    }

    // Se valida que el correo tenga un formato válido
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: contacto.php?msg=invalid_email");
        exit;
    }

    // Se construye el cuerpo del correo en formato HTML
    $mensaje_correo = "
        <h2>Nuevo mensaje recibido</h2>
        <p><strong>Nombre:</strong> $name</p>
        <p><strong>Email:</strong> $email</p>
        <p><strong>Mensaje:</strong><br>" . nl2br($message) . "</p>
    ";

    // Se crea una nueva instancia de PHPMailer con manejo de excepciones
    $phpmailer = new PHPMailer(true);

    try {
        // Configuración del servidor SMTP
        $phpmailer->isSMTP(); // Se especifica que se usará SMTP
        $phpmailer->SMTPAuth = true; // Habilita autenticación SMTP
        $phpmailer->Host = 'smtp.gmail.com'; // Servidor SMTP de Gmail
        $phpmailer->Username = SMTP_USERNAME; // Usuario SMTP
        $phpmailer->Password = SMTP_PASSWORD; // Contraseña SMTP
        $phpmailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Tipo de cifrado
        $phpmailer->Port = 587; // Puerto usado por TLS

        // $phpmailer->SMTPDebug = SMTP::DEBUG_SERVER; // Mostrar detalles de depuración (opcional)

        // Datos del remitente y destinatario
        $phpmailer->setFrom(SMTP_USERNAME, CONTACT_FROM_NAME); // Remitente
        $phpmailer->addAddress(CONTACT_EMAIL_TO); // Destinatario

        // Asunto y contenido del mensaje
        $phpmailer->Subject = "Mensaje recibido de tu web: " . $name; // Asunto con el nombre del remitente
        $phpmailer->Body = $mensaje_correo; // Cuerpo del mensaje en HTML
        $phpmailer->isHTML(true); // Indica que el mensaje es HTML
        $phpmailer->CharSet = 'UTF-8'; // Codificación de caracteres

        // Envía el correo
        $phpmailer->send();

        // Redirige con mensaje de éxito
        header("Location: contacto.php?msg=ok");
        exit;

    } catch (Exception $e) {
        // Si ocurre un error, redirige con el mensaje de error codificado
        header("Location: contacto.php?msg=off&error=" . urlencode($phpmailer->ErrorInfo));
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacto</title>
    <link rel="stylesheet" href="path/to/your/styles.css"> <!-- Ruta de estilo adicional -->
</head>

<body>
    <!-- Contenedor del formulario de contacto -->
    <div class="contenedor-form">
        <h2>Te escuchamos</h2>
        <form action="" method="POST"> <!-- Formulario que se envía a la misma página -->
            <input type="hidden" name="action" value="send_contact"> <!-- Campo oculto adicional -->

            <!-- Campo para el nombre -->
            <div class="contenedor-input">
                <input type="text" name="name" id="name" required>
                <label for="name">Nombre</label>
            </div>

            <!-- Campo para el correo electrónico -->
            <div class="contenedor-input">
                <input type="email" name="email" id="email" required>
                <label for="email">Email</label>
            </div>

            <!-- Campo para el mensaje -->
            <div class="contenedor-input">
                <textarea name="message" id="message" required></textarea>
                <label for="message">Mensaje</label>
            </div>

            <!-- Botón para enviar el formulario -->
            <button type="submit" class="btn">Enviar mensaje</button>
        </form>
    </div>

    <!-- Sección que muestra mensajes según el estado del envío -->
    <?php if (isset($_GET['msg'])): ?>
        <div
            style="margin-top: 20px; padding: 10px; border: 1px solid; max-width: 400px; margin-left: auto; margin-right: auto;">
            <?php
            // Se evalúa el valor del parámetro 'msg' en la URL para mostrar mensajes adecuados
            if ($_GET['msg'] == 'ok') {
                echo '<p style="color: green;">¡Mensaje enviado correctamente!</p>';
            } elseif ($_GET['msg'] == 'empty_fields') {
                echo '<p style="color: orange;">Error: Por favor, complete todos los campos.</p>';
            } elseif ($_GET['msg'] == 'invalid_email') {
                echo '<p style="color: orange;">Error: Por favor, ingrese un email válido.</p>';
            } else {
                // Se muestra el mensaje de error devuelto por PHPMailer
                echo '<p style="color: red;">Error al enviar el mensaje. Detalles: ' . htmlspecialchars($_GET['error'] ?? 'Desconocido') . '</p>';
            }
            ?>
        </div>
    <?php endif; ?>

    <!-- Se incluye el pie de página definido por una constante -->
    <?php require_once FOOTER ?>
</body>

</html>