<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';
require_once __DIR__ . '/PHPMailer/Exception.php';

define('SMTP_USER', 'urbanweartfg@espabilacurrotfg202425.online');
define('SMTP_PASS', 'MegustaelMineclaft25');

function enviarCorreoPedido($correoDestino, $nombreCliente, $pedidoHTML) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.ionos.es';
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom(SMTP_USER, 'UrbanWear');
        $mail->addAddress($correoDestino, $nombreCliente);

        $mail->isHTML(true);
        $mail->Subject = '🧾 Confirmación de tu pedido - UrbanWear';
        $mail->Body    = $pedidoHTML;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Error enviando correo pedido a $correoDestino: " . $mail->ErrorInfo);
        return false;
    }
}

function enviarCorreoBienvenida($correoDestino, $nombreUsuario) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.ionos.es';
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom(SMTP_USER, 'UrbanWear');
        $mail->addAddress($correoDestino, $nombreUsuario);

        $mail->isHTML(true);
        $mail->Subject = '👋 ¡Bienvenido a UrbanWear!';
        $mail->Body = "
            <h2>Hola $nombreUsuario 👋</h2>
            <p>Gracias por registrarte en <strong>UrbanWear</strong>. Ya puedes iniciar sesión y disfrutar de nuestros productos.</p>
            <p><a href='https://espabilacurrotfg202425.online/login.php'>Iniciar sesión</a></p>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Error enviando correo bienvenida a $correoDestino: " . $mail->ErrorInfo);
        return false;
    }
}
