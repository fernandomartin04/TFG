<?php
session_start();
require "includes/db.php";
require "includes/PHPMailer/PHPMailer.php";
require "includes/PHPMailer/SMTP.php";
require "includes/PHPMailer/Exception.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 3) {
    header("Location: index.php");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "Pedido no válido.";
    exit();
}

$pedido_id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT p.id, p.nombre, p.direccion, p.telefono, p.fecha, u.nombre AS cliente, u.email
                        FROM pedidos p
                        JOIN usuarios u ON p.usuario_id = u.id
                        WHERE p.id = ?");
$stmt->bind_param("i", $pedido_id);
$stmt->execute();
$pedido = $stmt->get_result()->fetch_assoc();

if (!$pedido) {
    echo "Pedido no encontrado.";
    exit();
}

function enviarCorreoEstado($pedido, $detalles, $estado_texto, $titulo) {
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
        $mail->addAddress($pedido['email']);
        $mail->isHTML(true);
        $mail->Subject = $titulo;
        $mail->CharSet = 'UTF-8';

        $body = '<div style="font-family: Arial, sans-serif; background: #f4f4f4; padding: 30px;">
        <div style="max-width: 600px; margin: auto; background: white; border: 1px solid #ddd; border-radius: 5px;">
            <div style="background: #007bff; color: white; padding: 20px; text-align: center;">
                <img src="https://espabilacurrotfg202425.online/img/logo.png" alt="UrbanWear" style="max-width: 100px; margin-bottom: 10px;">
                <h2 style="margin: 0;">' . $titulo . '</h2>
            </div>
            <div style="padding: 20px;">
                <p>Hola <strong>' . htmlspecialchars($pedido['cliente']) . '</strong>,</p>
                <p>' . $estado_texto . '</p>
                <h4>Detalles del pedido #' . $pedido['id'] . '</h4>
                <table width="100%" cellpadding="10" cellspacing="0" style="border-collapse: collapse; margin-top: 15px;">
                    <thead>
                        <tr style="background: #f2f2f2;">
                            <th style="border: 1px solid #ddd;">Producto</th>
                            <th style="border: 1px solid #ddd;">Cantidad</th>
                            <th style="border: 1px solid #ddd;">Talla</th>
                            <th style="border: 1px solid #ddd;">Precio</th>
                            <th style="border: 1px solid #ddd;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>';

        $total = 0;
        foreach ($detalles as $row) {
            $total += $row['subtotal'];
            $body .= '<tr>
                        <td style="border: 1px solid #ddd;">' . htmlspecialchars($row['nombre']) . '</td>
                        <td style="border: 1px solid #ddd; text-align: center;">' . $row['cantidad'] . '</td>
                        <td style="border: 1px solid #ddd; text-align: center;">' . $row['talla'] . '</td>
                        <td style="border: 1px solid #ddd; text-align: right;">' . number_format($row['precio_unitario'], 2) . ' €</td>
                        <td style="border: 1px solid #ddd; text-align: right;">' . number_format($row['subtotal'], 2) . ' €</td>
                    </tr>';
        }

        $body .= '<tr style="background: #eafaf1;">
                    <td colspan="4" style="text-align: right; border: 1px solid #ddd;"><strong>Total</strong></td>
                    <td style="border: 1px solid #ddd; text-align: right;"><strong>' . number_format($total, 2) . ' €</strong></td>
                </tr>
                </tbody></table>
                <p style="margin-top: 20px;">Gracias por confiar en nosotros.<br>UrbanWear</p>
            </div>
            <div style="background: #f2f2f2; text-align: center; padding: 15px; font-size: 12px; color: #777;">
                © 2025 UrbanWear. Todos los derechos reservados.
            </div>
        </div>
    </div>';

        $mail->Body = $body;
        $mail->send();
        echo '<div class="alert alert-info">Correo enviado correctamente al cliente.</div>';
    } catch (Exception $e) {
        echo '<div class="alert alert-danger">Error al enviar el correo: ' . $mail->ErrorInfo . '</div>';
    }
}

// Obtener productos del pedido
$stmt = $conn->prepare("SELECT pd.cantidad, pd.talla, pd.precio_unitario, pd.subtotal, p.nombre 
                        FROM pedidos_detalle pd
                        JOIN productos p ON pd.producto_id = p.id
                        WHERE pd.pedido_id = ?");
$stmt->bind_param("i", $pedido_id);
$stmt->execute();
$detalles_resultado = $stmt->get_result();
$detalles = $detalles_resultado->fetch_all(MYSQLI_ASSOC);

// Enviar pedido en preparación
if (isset($_POST['enviar_preparacion'])) {
    enviarCorreoEstado($pedido, $detalles, "Tu pedido está siendo preparado y pronto será enviado.", "Tu pedido está en preparación");
}

// Enviar pedido enviado
if (isset($_POST['pedido_enviado'])) {
    enviarCorreoEstado($pedido, $detalles, "¡Tu pedido ya ha sido enviado! Pronto lo recibirás en la dirección indicada.", "Tu pedido ha sido enviado");
}

// Eliminar pedido
if (isset($_POST['eliminar_pedido'])) {
    $conn->query("DELETE FROM pedidos_detalle WHERE pedido_id = $pedido_id");
    $conn->query("DELETE FROM pedidos WHERE id = $pedido_id");
    echo '<div class="alert alert-danger">Pedido eliminado correctamente.</div>';
    echo '<a href="admin_pedidos.php" class="btn btn-secondary mt-3">Volver a pedidos</a>';
    exit();
}

// Actualizar datos del cliente
if (isset($_POST['guardar_datos'])) {
    $nuevo_nombre    = trim($_POST['nuevo_nombre']);
    $nueva_direccion = trim($_POST['nueva_direccion']);
    $nuevo_telefono  = trim($_POST['nuevo_telefono']);

    $stmt = $conn->prepare("UPDATE pedidos SET nombre = ?, direccion = ?, telefono = ? WHERE id = ?");
    $stmt->bind_param("sssi", $nuevo_nombre, $nueva_direccion, $nuevo_telefono, $pedido_id);
    $stmt->execute();

    echo "<div class='alert alert-success'>Datos del cliente actualizados correctamente.</div>";
    header("Location: ver_pedido.php?id=$pedido_id");
    exit();
}

include "includes/header.php";
?>

<div class="container mt-5">
    <h3>Detalles del pedido #<?= $pedido['id'] ?></h3>
    <p><strong>Cliente:</strong> <?= htmlspecialchars($pedido['cliente']) ?></p>
    <p><strong>Destinatario:</strong> <?= htmlspecialchars($pedido['nombre']) ?></p>
    <p><strong>Dirección:</strong> <?= htmlspecialchars($pedido['direccion']) ?></p>
    <p><strong>Teléfono:</strong> <?= htmlspecialchars($pedido['telefono']) ?></p>
    <p><strong>Fecha:</strong> <?= $pedido['fecha'] ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($pedido['email']) ?></p>

    <form method="POST" class="d-inline">
        <button type="submit" name="enviar_preparacion" class="btn btn-primary mt-2">Enviar correo: Pedido en preparación</button>
    </form>

    <form method="POST" class="d-inline">
        <button type="submit" name="pedido_enviado" class="btn btn-success mt-2">Enviar correo: Pedido enviado</button>
    </form>

    <form method="POST" class="d-inline" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este pedido?');">
        <button type="submit" name="eliminar_pedido" class="btn btn-danger mt-2">Eliminar pedido</button>
    </form>

    <div class="mt-4 p-3 border rounded bg-light">
        <h5>Editar datos del destinatario</h5>
        <form method="POST">
            <div class="mb-2">
                <label class="form-label">Nombre</label>
                <input type="text" name="nuevo_nombre" class="form-control" value="<?= htmlspecialchars($pedido['nombre']) ?>" required>
            </div>
            <div class="mb-2">
                <label class="form-label">Dirección</label>
                <input type="text" name="nueva_direccion" class="form-control" value="<?= htmlspecialchars($pedido['direccion']) ?>" required>
            </div>
            <div class="mb-2">
                <label class="form-label">Teléfono</label>
                <input type="text" name="nuevo_telefono" class="form-control" value="<?= htmlspecialchars($pedido['telefono']) ?>" required>
            </div>
            <button class="btn btn-success" name="guardar_datos" type="submit">Guardar cambios</button>
        </form>
    </div>

    <h4 class="mt-5">Productos del pedido</h4>
    <table class="table table-bordered">
        <thead class="table-secondary">
            <tr>
                <th>Producto</th>
                <th>Talla</th>
                <th>Cantidad</th>
                <th>Precio unitario</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $total = 0;
            foreach ($detalles as $row):
                $total += $row['subtotal'];
            ?>
            <tr>
                <td><?= htmlspecialchars($row['nombre']) ?></td>
                <td><?= $row['talla'] ?></td>
                <td><?= $row['cantidad'] ?></td>
                <td><?= number_format($row['precio_unitario'], 2) ?> €</td>
                <td><?= number_format($row['subtotal'], 2) ?> €</td>
            </tr>
            <?php endforeach; ?>
            <tr class="table-dark">
                <td colspan="4" class="text-end"><strong>Total:</strong></td>
                <td><strong><?= number_format($total, 2) ?> €</strong></td>
            </tr>
        </tbody>
    </table>

    <a href="admin_pedidos.php" class="btn btn-secondary mt-3">Volver</a>
</div>

<?php include "includes/footer.php"; ?>
