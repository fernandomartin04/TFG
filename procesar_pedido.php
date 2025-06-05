<?php
session_start();
require "includes/db.php";
require 'includes/PHPMailer/src/PHPMailer.php';
require 'includes/PHPMailer/src/SMTP.php';
require 'includes/PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_id = $_SESSION['id'];
    $nombre = $_POST['nombre'];
    $direccion = $_POST['direccion'];
    $telefono = $_POST['telefono'];
    $metodo_pago = $_POST['metodo_pago'];

    $conn->begin_transaction();

    try {
        $insert_pedido = $conn->prepare("INSERT INTO pedidos (usuario_id, nombre, direccion, telefono, metodo_pago, fecha) VALUES (?, ?, ?, ?, ?, NOW())");
        $insert_pedido->bind_param("issss", $usuario_id, $nombre, $direccion, $telefono, $metodo_pago);
        $insert_pedido->execute();
        $pedido_id = $insert_pedido->insert_id;

        $query = "SELECT c.producto_id, c.cantidad, c.talla, p.nombre, p.precio
                  FROM carritos c
                  JOIN productos p ON c.producto_id = p.id
                  WHERE c.usuario_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $detalle = "";
        while ($row = $result->fetch_assoc()) {
            $producto_id = $row['producto_id'];
            $cantidad = $row['cantidad'];
            $talla = $row['talla'];
            $precio = $row['precio'];

            $insert_detalle = $conn->prepare("INSERT INTO pedidos_detalle (pedido_id, producto_id, cantidad, talla, precio_unitario) VALUES (?, ?, ?, ?, ?)");
            $insert_detalle->bind_param("iiisd", $pedido_id, $producto_id, $cantidad, $talla, $precio);
            $insert_detalle->execute();

            $detalle .= "{$row['nombre']} (Talla: {$talla}) x{$cantidad} - " . number_format($precio, 2) . "€\n";
        }

        $conn->query("DELETE FROM carritos WHERE usuario_id = $usuario_id");
        $conn->commit();

        // CONFIGURACIÓN DE SMTP2GO
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'mail.smtp2go.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'TU_USUARIO_SMTP2GO';
        $mail->Password = 'TU_CONTRASEÑA_SMTP2GO';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('noreply@urbanwear.com', 'UrbanWear');
        $mail->addAddress('correo@cliente.com'); // puedes usar también el correo del usuario logueado si lo tienes

        $mail->Subject = 'Confirmación de Pedido - UrbanWear';
        $mail->Body = "¡Gracias por tu compra, $nombre!\n\nDetalles del pedido:\n$detalle\nDirección: $direccion\nTeléfono: $telefono\nMétodo de pago: $metodo_pago\n\nRecibirás tu pedido pronto.";

        $mail->send();

        header("Location: gracias.php");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        echo "Error al procesar el pedido: " . $e->getMessage();
    }
} else {
    header("Location: carrito.php");
    exit();
}
?>
