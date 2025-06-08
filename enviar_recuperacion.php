<?php
session_start();
require "includes/db.php";
require "includes/PHPMailer/PHPMailer.php";
require "includes/PHPMailer/SMTP.php";
require "includes/PHPMailer/Exception.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$_SESSION['mensaje_recuperacion'] = '';
$_SESSION['tipo_mensaje_recuperacion'] = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);

    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE nombre = ? AND email = ?");
    $stmt->bind_param("ss", $nombre, $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 1) {
        $usuario = $res->fetch_assoc();
        $token = bin2hex(random_bytes(16));
        $expira = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $conn->query("DELETE FROM tokens_recuperacion WHERE usuario_id = {$usuario['id']}");
        $stmt = $conn->prepare("INSERT INTO tokens_recuperacion (usuario_id, token, expira) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $usuario['id'], $token, $expira);
        $stmt->execute();

        // Enlace
        $enlace = "https://espabilacurrotfg202425.online/resetear.php?token=$token";

        // Cuerpo HTML estilizado
        $mailBody = "
        <div style='font-family: Arial, sans-serif; padding: 20px; background-color: #f4f4f4;'>
            <div style='max-width: 600px; margin: auto; background-color: #ffffff; border-radius: 5px; overflow: hidden; border: 1px solid #dddddd;'>
                <div style='background-color: #007bff; padding: 20px; text-align: center; color: white;'>
                    <img src='https://espabilacurrotfg202425.online/img/logo.png' alt='UrbanWear' style='max-width: 100px; margin-bottom: 10px;'>
                    <h1 style='margin: 0;'>UrbanWear</h1>
                    <p style='margin: 0;'>Recuperación de contraseña</p>
                </div>
                <div style='padding: 20px;'>
                    <p>Hola <strong>" . htmlspecialchars($nombre) . "</strong>,</p>
                    <p>Hemos recibido una solicitud para restablecer tu contraseña. Si no fuiste tú, puedes ignorar este correo.</p>
                    <p>Haz clic en el siguiente botón para establecer una nueva contraseña:</p>
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='$enlace' style='background-color: #007bff; color: white; padding: 12px 25px; border-radius: 5px; text-decoration: none; display: inline-block;'>Restablecer contraseña</a>
                    </div>
                    <p>Este enlace caducará en 1 hora.</p>
                    <p>Si el botón anterior no funciona, copia y pega este enlace en tu navegador:</p>
                    <p style='word-break: break-all;'>$enlace</p>
                </div>
                <div style='background-color: #f2f2f2; text-align: center; font-size: 12px; padding: 10px; color: #888;'>
                    © 2025 UrbanWear. Todos los derechos reservados.
                </div>
            </div>
        </div>";

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.ionos.es';
            $mail->SMTPAuth = true;
            $mail->Username = 'urbanweartfg@espabilacurrotfg202425.online';
            $mail->Password = 'MegustaelMineclaft25';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('urbanweartfg@espabilacurrotfg202425.online', 'UrbanWear');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = 'Recuperación de contraseña - UrbanWear';
            $mail->Body = $mailBody;

            $mail->send();

            $_SESSION['mensaje_recuperacion'] = "✅ Te hemos enviado un enlace a tu correo.";
            $_SESSION['tipo_mensaje_recuperacion'] = "success";
        } catch (Exception $e) {
            $_SESSION['mensaje_recuperacion'] = "❌ Error al enviar el correo.";
            $_SESSION['tipo_mensaje_recuperacion'] = "danger";
        }
    } else {
        $_SESSION['mensaje_recuperacion'] = "❌ Usuario o correo incorrecto.";
        $_SESSION['tipo_mensaje_recuperacion'] = "warning";
    }

    header("Location: recuperar.php");
    exit;
}
