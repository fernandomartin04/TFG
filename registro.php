<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once "includes/db.php";

require "includes/PHPMailer/PHPMailer.php";
require "includes/PHPMailer/SMTP.php";
require "includes/PHPMailer/Exception.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST["nombre"]);
    $email = trim($_POST["email"]);
    $contrasena = $_POST["contrasena"];
    $confirmar = $_POST["confirmar"];

    function validarContrasena($pwd) {
        return strlen($pwd) >= 12 &&
               preg_match('/[A-Z]/', $pwd) &&
               preg_match('/[a-z]/', $pwd) &&
               preg_match('/[0-9]/', $pwd) &&
               preg_match('/[\W]/', $pwd);
    }

    if ($contrasena !== $confirmar) {
        $mensaje = "Las contraseñas no coinciden.";
    } elseif (!validarContrasena($contrasena)) {
        $mensaje = "La contraseña debe tener al menos 12 caracteres, una mayúscula, una minúscula, un número y un símbolo.";
    } else {
        if ($conn) {
            $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $mensaje = "El correo electrónico ya está registrado.";
            } else {
                $hash = password_hash($contrasena, PASSWORD_BCRYPT, ["cost" => 12]);
                $stmt = $conn->prepare("INSERT INTO usuarios (nombre, email, contrasena, rol_id) VALUES (?, ?, ?, 1)");
                $stmt->bind_param("sss", $nombre, $email, $hash);

                if ($stmt->execute()) {
                    // Enviar correo bienvenida
                    $mail = new PHPMailer(true);
                    try {
                        $mail->isSMTP();
                        $mail->Host       = 'smtp.ionos.es';
                        $mail->SMTPAuth   = true;
                        $mail->Username   = 'urbanweartfg@espabilacurrotfg202425.online';
                        $mail->Password   = 'MegustaelMineclaft25';
                        $mail->SMTPSecure = 'tls';
                        $mail->Port       = 587;
                        $mail->CharSet    = 'UTF-8';

                        $mail->setFrom('urbanweartfg@espabilacurrotfg202425.online', 'UrbanWear');
                        $mail->addAddress($email, $nombre);

                        $mail->isHTML(true);
                        $mail->Subject = '👋 ¡Bienvenido a UrbanWear!';
                        $mail->Body = '
                        <!DOCTYPE html>
                        <html lang="es">
                        <head>
                          <meta charset="UTF-8" />
                          <title>Bienvenido a UrbanWear</title>
                          <style>
                            body {
                              font-family: Arial, sans-serif;
                              background-color: #f4f4f4;
                              margin: 0; padding: 0;
                              color: #333;
                            }
                            .container {
                              max-width: 600px;
                              margin: 30px auto;
                              background-color: #fff;
                              padding: 25px 30px;
                              border-radius: 10px;
                              box-shadow: 0 4px 10px rgba(0,0,0,0.1);
                            }
                            h1 {
                              color: #007bff;
                              margin-bottom: 15px;
                            }
                            p {
                              font-size: 16px;
                              line-height: 1.5;
                              margin-bottom: 20px;
                            }
                            .btn {
                              display: inline-block;
                              background-color: #007bff;
                              color: #fff !important;
                              padding: 12px 25px;
                              border-radius: 30px;
                              text-decoration: none;
                              font-weight: 600;
                              font-size: 16px;
                              box-shadow: 0 4px 8px rgba(0,123,255,0.3);
                              transition: background-color 0.3s ease;
                            }
                            .btn:hover {
                              background-color: #0056b3;
                            }
                            .footer {
                              font-size: 14px;
                              color: #888;
                              text-align: center;
                              margin-top: 30px;
                              border-top: 1px solid #eee;
                              padding-top: 15px;
                            }
                          </style>
                        </head>
                        <body>
                          <div class="container">
                            <h1>Hola ' . htmlspecialchars($nombre) . ' 👋</h1>
                            <p>Gracias por registrarte en <strong>UrbanWear</strong>.</p>
                            <p><strong>Correo registrado:</strong> ' . htmlspecialchars($email) . '</p>
                            <p>Ya puedes iniciar sesión y empezar a disfrutar de nuestros productos.</p>
                            <p><a href="https://espabilacurrotfg202425.online/login.php" class="btn">Iniciar sesión</a></p>
                            <div class="footer">
                              UrbanWear &copy; ' . date('Y') . '
                            </div>
                          </div>
                        </body>
                        </html>
                        ';

                        $mail->send();
                    } catch (Exception $e) {
                        error_log("Error enviando correo bienvenida en registro: " . $mail->ErrorInfo);
                    }

                    header("Location: index.php");
                    exit();
                } else {
                    $mensaje = "Error al registrar usuario.";
                }
            }
        } else {
            $mensaje = "Error de conexión con la base de datos.";
        }
    }
}
?>

<?php include "includes/header.php"; ?>

<body class="bg-light">
<div class="container">
    <div class="row justify-content-center align-items-center min-vh-100">
        <form class="col-sm-6 border p-4 rounded shadow bg-white" method="POST">
            <h2 class="text-center mb-4">Registro de Usuario</h2>

            <?php if (!empty($mensaje)): ?>
                <div class="alert alert-danger text-center" role="alert">
                    <?= htmlspecialchars($mensaje) ?>
                </div>
            <?php endif; ?>

            <div class="mb-3">
                <label>Nombre de usuario</label>
                <input type="text" class="form-control" name="nombre" required>
            </div>

            <div class="mb-3">
                <label>Correo electrónico</label>
                <input type="email" class="form-control" name="email" required>
            </div>

            <div class="mb-3">
                <label>Contraseña</label>
                <input type="password" class="form-control" name="contrasena" required>
                <small class="text-muted">Mínimo 12 caracteres, con mayúsculas, minúsculas, números y símbolos.</small>
            </div>

            <div class="mb-3">
                <label>Confirmar contraseña</label>
                <input type="password" class="form-control" name="confirmar" required>
            </div>

            <input type="submit" class="btn btn-primary w-100" value="Registrarse">

            <div class="text-center mt-3">
                <p>¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a></p>
            </div>
        </form>
    </div>
</div>
</body>

<?php include "includes/footer.php"; ?>
