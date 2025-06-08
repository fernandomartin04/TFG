<?php
include "includes/header.php";
require "includes/PHPMailer/PHPMailer.php";
require "includes/PHPMailer/SMTP.php";
require "includes/PHPMailer/Exception.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mensaje = "";
$exito = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre  = trim($_POST["nombre"] ?? "");
    $email   = trim($_POST["email"] ?? "");
    $mensaje_form = trim($_POST["mensaje"] ?? "");

    if (empty($nombre) || empty($email) || empty($mensaje_form)) {
        $mensaje = "Por favor, completa todos los campos.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensaje = "El correo electrónico no es válido.";
    } else {
        $mail = new PHPMailer(true);
        $mail2 = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.ionos.es';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'urbanweartfg@espabilacurrotfg202425.online';
            $mail->Password   = 'MegustaelMineclaft25';
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            $mail->setFrom('urbanweartfg@espabilacurrotfg202425.online', 'UrbanWear Contacto');
            $mail->addAddress('urbanweartfg@espabilacurrotfg202425.online');
            $mail->addReplyTo($email, $nombre);

            $mail->isHTML(true);
            $mail->Subject = 'Nuevo mensaje desde formulario de contacto';
            $mail->Body = "
                <h2>Nuevo mensaje de contacto</h2>
                <p><strong>Nombre:</strong> " . htmlspecialchars($nombre) . "</p>
                <p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>
                <p><strong>Mensaje:</strong><br>" . nl2br(htmlspecialchars($mensaje_form)) . "</p>
            ";
            $mail->send();

            $mail2->isSMTP();
            $mail2->Host       = 'smtp.ionos.es';
            $mail2->SMTPAuth   = true;
            $mail2->Username   = 'urbanweartfg@espabilacurrotfg202425.online';
            $mail2->Password   = 'MegustaelMineclaft25';
            $mail2->SMTPSecure = 'tls';
            $mail2->Port       = 587;

            $mail2->setFrom('urbanweartfg@espabilacurrotfg202425.online', 'UrbanWear');
            $mail2->addAddress($email, $nombre);
            $mail2->CharSet = 'UTF-8';

            $mail2->isHTML(true);
            $mail2->Subject = 'Confirmación de recepción de tu mensaje';
            $mail2->Body = '
<table width="100%" cellpadding="0" cellspacing="0" style="font-family: Arial, sans-serif; background:#f9f9f9; padding:20px;">
  <tr>
    <td>
      <table align="center" cellpadding="0" cellspacing="0" width="600" style="background:#ffffff; border-radius:8px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
        <tr>
          <td style="padding: 20px; text-align: center; background-color: #007bff; color: #ffffff; border-top-left-radius: 8px; border-top-right-radius: 8px;">
            <h1 style="margin:0; font-size: 24px;">Hola ' . htmlspecialchars($nombre) . ' 👋</h1>
          </td>
        </tr>
        <tr>
          <td style="padding: 20px; color: #333333; font-size: 16px; line-height: 1.5;">
            <p>Hemos recibido tu mensaje y lo estamos gestionando.</p>
            <p><strong>Tu mensaje fue:</strong></p>
            <blockquote style="margin: 15px 0; padding: 15px; background-color: #f1f1f1; border-left: 5px solid #007bff; font-style: italic; color: #555;">
              ' . nl2br(htmlspecialchars($mensaje_form)) . '
            </blockquote>
            <p>Te responderemos lo antes posible.</p>
            <p>Gracias por confiar en <strong>UrbanWear</strong>.</p>
          </td>
        </tr>
        <tr>
          <td style="padding: 15px; font-size: 12px; color: #999999; text-align: center; border-bottom-left-radius: 8px; border-bottom-right-radius: 8px; background:#f0f0f0;">
            &copy; ' . date('Y') . ' UrbanWear. Todos los derechos reservados.
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>';

            $mail2->send();

            $exito = true;
            $mensaje = "Gracias por contactarnos. Te responderemos lo antes posible.";
        } catch (Exception $e) {
            $mensaje = "Error al enviar el mensaje: " . $mail->ErrorInfo;
        }
    }
}
?>

<div class="container my-5">
  <h1>Contacto</h1>

  <?php if (!empty($mensaje)): ?>
      <div class="alert <?= $exito ? 'alert-success' : 'alert-danger' ?>" role="alert">
          <?= htmlspecialchars($mensaje) ?>
      </div>
  <?php endif; ?>

  <p>Si tienes alguna consulta, sugerencia o quieres ponerte en contacto con nosotros, estaremos encantados de ayudarte.</p>

  <ul>
    <li><strong>Correo electrónico:</strong> <a href="mailto:urbanweartfg@espabilacurrotfg202425.online">urbanweartfg@espabilacurrotfg202425.online</a></li>
    <li><strong>Teléfono:</strong> +34 954 123 456</li>
    <li><strong>Dirección:</strong> Calle Virgen de Luján, 45, 41005 Sevilla, España</li>
  </ul>

  <h2>Formulario de contacto</h2>
  <form method="POST" class="mb-5">
    <div class="mb-3">
      <label for="nombre" class="form-label">Nombre completo</label>
      <input type="text" id="nombre" name="nombre" class="form-control" required value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">
    </div>

    <div class="mb-3">
      <label for="email" class="form-label">Correo electrónico</label>
      <input type="email" id="email" name="email" class="form-control" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
    </div>

    <div class="mb-3">
      <label for="mensaje" class="form-label">Mensaje</label>
      <textarea id="mensaje" name="mensaje" class="form-control" rows="5" required><?= htmlspecialchars($_POST['mensaje'] ?? '') ?></textarea>
    </div>

    <button type="submit" class="btn btn-primary">Enviar mensaje</button>
  </form>
</div>

<?php include "includes/footer.php"; ?>
