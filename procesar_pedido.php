<?php
session_start();
require "includes/db.php";
require "includes/PHPMailer/PHPMailer.php";
require "includes/PHPMailer/SMTP.php";
require "includes/PHPMailer/Exception.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$errores = [];
$datos = [
    'nombre' => '',
    'direccion' => '',
    'telefono' => '',
    'email' => '',
    'metodo_pago' => ''
];

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos['nombre'] = trim($_POST['nombre'] ?? '');
    $datos['direccion'] = trim($_POST['direccion'] ?? '');
    $datos['telefono'] = trim($_POST['telefono'] ?? '');
    $datos['email'] = trim($_POST['email'] ?? '');
    $datos['metodo_pago'] = trim($_POST['metodo_pago'] ?? '');

    if (empty($datos['nombre']) || !preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/u', $datos['nombre'])) {
        $errores['nombre'] = "Nombre inválido.";
    }

    if (empty($datos['direccion'])) {
        $errores['direccion'] = "Dirección obligatoria.";
    }

    if (empty($datos['telefono']) || !preg_match('/^[0-9]{9,15}$/', $datos['telefono'])) {
        $errores['telefono'] = "Teléfono inválido.";
    }

    if (!empty($datos['email']) && !filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
        $errores['email'] = "Correo electrónico inválido.";
    }

    if (empty($datos['metodo_pago'])) {
        $errores['metodo_pago'] = "Selecciona un método de pago.";
    }

    if (empty($errores)) {
        // Obtener carrito
        $sql = "SELECT c.producto_id, c.cantidad, c.talla, p.nombre, p.precio
                FROM carritos c
                JOIN productos p ON c.producto_id = p.id
                WHERE c.usuario_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            echo "<div class='container mt-4 alert alert-warning'>Tu carrito está vacío.</div>";
        } else {
            $stmt = $conn->prepare("INSERT INTO pedidos (usuario_id, nombre, direccion, telefono, metodo_pago, fecha) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("issss", $usuario_id, $datos['nombre'], $datos['direccion'], $datos['telefono'], $datos['metodo_pago']);
            $stmt->execute();
            $pedido_id = $stmt->insert_id;

            $detalle = $conn->prepare("INSERT INTO pedidos_detalle (pedido_id, producto_id, cantidad, talla, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?, ?)");
            $total = 0;

            $mailBody = "<h1>Confirmación de Pedido</h1><table border='1' cellpadding='5'><tr><th>Producto</th><th>Cantidad</th><th>Talla</th><th>Precio</th><th>Subtotal</th></tr>";

            while ($row = $result->fetch_assoc()) {
                $producto_id = $row['producto_id'];
                $cantidad = $row['cantidad'];
                $talla = $row['talla'];
                $precio = $row['precio'];
                $subtotal = $cantidad * $precio;
                $total += $subtotal;

                $detalle->bind_param("iiisdd", $pedido_id, $producto_id, $cantidad, $talla, $precio, $subtotal);
                $detalle->execute();

                $mailBody .= "<tr><td>{$row['nombre']}</td><td>$cantidad</td><td>$talla</td><td>$precio €</td><td>$subtotal €</td></tr>";
            }

            $mailBody .= "<tr><td colspan='4'><strong>Total</strong></td><td><strong>$total €</strong></td></tr></table>";

            $conn->query("DELETE FROM carritos WHERE usuario_id = $usuario_id");

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
                $mail->addAddress($datos['email'] ?: 'currito231@hotmail.com');
                $mail->CharSet = 'UTF-8';
                $mail->isHTML(true);
                $mail->Subject = "Confirmación de pedido #$pedido_id";
                $mail->Body = $mailBody;
                $mail->send();
            } catch (Exception $e) {
                error_log("Error al enviar correo: " . $mail->ErrorInfo);
            }

            header("Location: mis_pedidos.php");
            exit();
        }
    }
}
?>

<?php include "includes/header.php"; ?>

<div class="container mt-5">
    <h2>Confirmar Pedido</h2>
    <form method="POST">
        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre completo</label>
            <input type="text" class="form-control <?= isset($errores['nombre']) ? 'is-invalid' : '' ?>" id="nombre" name="nombre" value="<?= htmlspecialchars($datos['nombre']) ?>" required>
            <div class="invalid-feedback"><?= $errores['nombre'] ?? '' ?></div>
        </div>

        <div class="mb-3">
            <label for="direccion" class="form-label">Dirección</label>
            <textarea class="form-control <?= isset($errores['direccion']) ? 'is-invalid' : '' ?>" id="direccion" name="direccion" required><?= htmlspecialchars($datos['direccion']) ?></textarea>
            <div class="invalid-feedback"><?= $errores['direccion'] ?? '' ?></div>
        </div>

        <div class="mb-3">
            <label for="telefono" class="form-label">Teléfono</label>
            <input type="text" class="form-control <?= isset($errores['telefono']) ? 'is-invalid' : '' ?>" id="telefono" name="telefono" value="<?= htmlspecialchars($datos['telefono']) ?>" required>
            <div class="invalid-feedback"><?= $errores['telefono'] ?? '' ?></div>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Correo electrónico (opcional)</label>
            <input type="email" class="form-control <?= isset($errores['email']) ? 'is-invalid' : '' ?>" id="email" name="email" value="<?= htmlspecialchars($datos['email']) ?>">
            <div class="invalid-feedback"><?= $errores['email'] ?? '' ?></div>
        </div>

        <div class="mb-3">
            <label for="metodo_pago" class="form-label">Método de pago</label>
            <select class="form-select <?= isset($errores['metodo_pago']) ? 'is-invalid' : '' ?>" name="metodo_pago" required>
                <option value="">Seleccionar...</option>
                <option value="tarjeta" <?= $datos['metodo_pago'] === 'tarjeta' ? 'selected' : '' ?>>Tarjeta</option>
                <option value="paypal" <?= $datos['metodo_pago'] === 'paypal' ? 'selected' : '' ?>>PayPal</option>
                <option value="transferencia" <?= $datos['metodo_pago'] === 'transferencia' ? 'selected' : '' ?>>Transferencia</option>
            </select>
            <div class="invalid-feedback"><?= $errores['metodo_pago'] ?? '' ?></div>
        </div>

        <button type="submit" class="btn btn-success">Confirmar pedido</button>
    </form>
</div>
