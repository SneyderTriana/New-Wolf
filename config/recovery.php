<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Rutas de PHPMailer son correctas si recovery.php está en la raíz de login_1
require_once '../lib/PHPMailer-master/src/Exception.php';
require_once '../lib/PHPMailer-master/src/PHPMailer.php';
require_once '../lib/PHPMailer-master/src/SMTP.php';

// Asegúrate de que 'config.php' incluye la conexión a la base de datos ($conexion)
// y que la ruta es correcta desde recovery.php
require_once('config.php'); // O '../config/config.php' si está en la carpeta config

// Es crucial incluir addressDocument.php si BASE_URL o TIENDA se definen allí
// Asumo que tu BASE_URL es 'http://localhost/login_1/'
// Si no tienes una constante así, la definiremos aquí temporalmente o usarás la ruta directa.
// require_once __DIR__ . '/config/addressDocument.php'; 

$email = $_POST['email'];
$query = "SELECT * FROM usuarios WHERE correo = '$email'";
$result = $conexion->query($query); // Asegúrate que $conexion es válido y está conectado
$row = $result->fetch_assoc();

if ($result->num_rows > 0) {
    $mail = new PHPMailer(true);

    try {
        // Configuración del servidor SMTP de Gmail
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Servidor SMTP de Gmail
        $mail->SMTPAuth = true;                     // Habilitar autenticación SMTP
        $mail->Username = 'sneyder.gonzalez156@gmail.com';  // Tu correo real de Gmail
        $mail->Password = 'bdsmsedobjeffxet'; // <--- ¡DEBE SER UNA CONTRASEÑA DE APLICACIÓN DE GMAIL!
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // ¡MUY IMPORTANTE para Gmail!
        $mail->Port = 587;                      // Puerto SMTP

        // Remitente (debe ser el mismo que el Username para evitar problemas de spam)
        $mail->setFrom('sneyder.gonzalez156@gmail.com', 'New Wolf'); // Tu correo real y nombre

        // Destinatario (el correo del usuario que solicitó la recuperación)
        $mail->addAddress($email); // El correo del usuario es $email

        // Contenido del correo
        $mail->isHTML(true);                                  // Establecer formato de correo HTML
        $mail->Subject = 'Recuperación de contraseña';

        // Define la URL base de tu proyecto para construir el enlace
        // Reemplaza 'http://localhost/login_1/' con la URL base real de tu proyecto
        // Si tienes una constante como BASE_URL en addressDocument.php, úsala aquí.
        $baseUrl = 'http://localhost/login_1/';
        $recoveryLink = $baseUrl . 'view/change_password.php?id=' . urlencode($row['id']); // Usa change_password.php

        $mail->Body = 'Hola, este es un correo generado para solicitar tu recuperación de contraseña. Por favor, visita la página de <a href="' . $recoveryLink . '">Recuperación de contraseña</a>';
        // Alternativa de texto plano para clientes de correo sin HTML
        $mail->AltBody = 'Hola, para recuperar tu contraseña, por favor, visita la siguiente URL: ' . $recoveryLink;

        $mail->send();
        // Si el correo se envía con éxito, redirige a 'ok'
        header("Location: /login_1/view/change_password.php?message=ok"); // Consistentemente a change_password.php
        exit(); // Siempre llama a exit() después de header()
    } catch (Exception $e) {
        // Si hay un error al enviar, redirige a 'error'
        // Es crucial capturar el error de PHPMailer para depuración
        error_log("Error al enviar el correo: " . $mail->ErrorInfo);
        header("Location: /login_1/view/recuperarClave.php?message=error"); // Puedes mantener este o cambiarlo
        exit(); // Siempre llama a exit() después de header()
    }

} else {
    // Si el correo no se encuentra en la base de datos
    header("Location: /login_1/view/recuperarClave.php?message=not_found"); // Puedes mantener este o cambiarlo
    exit(); // Siempre llama a exit() después de header()
}

?>