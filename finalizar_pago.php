<?php
session_start();
require "includes/db.php";
require "includes/fpdf/fpdf.php";
require "includes/PHPMailer/PHPMailer.php";
require "includes/PHPMailer/SMTP.php";
require "includes/PHPMailer/Exception.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function generarHashUsuario() {
    return substr(hash('sha256', time() . rand()), 0, 16);
}

$usuario_id = $_SESSION['usuario_id'] ?? null;
$datos = $_SESSION['datos_formulario'] ?? null;
$carrito_items = $_SESSION['carrito_para_pedido'] ?? [];

unset($_SESSION['datos_formulario'], $_SESSION['carrito_para_pedido']);

if (!$datos || empty($carrito_items)) {
    header("Location: carrito.php");
    exit();
}

$cupon = $_SESSION['cupon_aplicado'] ?? null;

// Obtener productos actualizados
$productos = [];
$total_sin_descuento = 0;

foreach ($carrito_items as $item) {
    $stmt = $conn->prepare("SELECT id, nombre, precio FROM productos WHERE id = ?");
    $stmt->bind_param("i", $item['producto_id']);
    $stmt->execute();
    $producto = $stmt->get_result()->fetch_assoc();

    if ($producto) {
        $subtotal = $producto['precio'] * $item['cantidad'];
        $total_sin_descuento += $subtotal;

        $productos[] = [
            'producto_id' => $item['producto_id'],
            'nombre'      => $producto['nombre'],
            'precio'      => $producto['precio'],
            'cantidad'    => $item['cantidad'],
            'talla'       => $item['talla']
        ];
    }
}

// Calcular descuento
$descuento = 0;
$codigo_cupon = null;
if ($cupon) {
    $codigo_cupon = $cupon['codigo'];
    if ($cupon['tipo'] === 'porcentaje') {
        $descuento = ($total_sin_descuento * $cupon['valor']) / 100;
    } elseif ($cupon['tipo'] === 'fijo') {
        $descuento = min($cupon['valor'], $total_sin_descuento);
    }
}

$total_final = $total_sin_descuento - $descuento;

// Insertar pedido
$usuario_id_param = $usuario_id ?? null;
$usuario_hash = $usuario_id ? null : generarHashUsuario();

$stmt = $conn->prepare("INSERT INTO pedidos (usuario_id, usuario_hash, nombre, direccion, telefono, metodo_pago, fecha, total, cupon_codigo, cupon_descuento) 
                        VALUES (?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?)");
$stmt->bind_param(
    "isssssdss",
    $usuario_id_param,
    $usuario_hash,
    $datos['nombre'],
    $datos['direccion'],
    $datos['telefono'],
    $datos['metodo_pago'],
    $total_final,
    $codigo_cupon,
    $descuento
);
$stmt->execute();
$pedido_id = $stmt->insert_id;

// Insertar detalles y descontar stock
$detalle = $conn->prepare("INSERT INTO pedidos_detalle (pedido_id, producto_id, cantidad, talla, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?, ?)");
foreach ($productos as $row) {
    $producto_id = (int) $row['producto_id'];
    $cantidad    = (int) $row['cantidad'];
    $talla       = (string) $row['talla'];
    $precio      = (float) $row['precio'];
    $subtotal    = $precio * $cantidad;
    $proporcion = $subtotal / $total_sin_descuento;
    $subtotal_con_descuento = $subtotal - ($descuento * $proporcion);

    $detalle->bind_param("iiisdd", $pedido_id, $producto_id, $cantidad, $talla, $precio, $subtotal_con_descuento);
    $detalle->execute();

    $update = $conn->prepare("UPDATE stock_productos_tallas SET stock = stock - ? WHERE producto_id = ? AND talla = ?");
    $update->bind_param("iis", $cantidad, $producto_id, $talla);
    $update->execute();
}

// Vaciar carrito
if ($usuario_id) {
    $stmt = $conn->prepare("DELETE FROM carritos WHERE usuario_id = ?");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
} else {
    unset($_SESSION['carrito']);
}
unset($_SESSION['cupon_aplicado']);

// Crear factura PDF
$stmt = $conn->prepare("SELECT pd.cantidad, pd.talla, pd.precio_unitario, pd.subtotal, p.nombre 
                        FROM pedidos_detalle pd 
                        JOIN productos p ON pd.producto_id = p.id 
                        WHERE pd.pedido_id = ?");
$stmt->bind_param("i", $pedido_id);
$stmt->execute();
$result = $stmt->get_result();

class PDF extends FPDF {
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->SetTextColor(128);
        $this->Cell(0,10,utf8_decode('© 2025 UrbanWear. Todos los derechos reservados.'),0,0,'C');
    }
}

$pdf = new PDF();
$pdf->AddPage();
$pdf->SetAutoPageBreak(true, 30);
$pdf->Image('https://espabilacurrotfg202425.online/img/logo.png', ($pdf->GetPageWidth()/2) - 20, 10, 40);
$pdf->SetY(55);
$pdf->SetFont('Arial','B',20);
$pdf->SetTextColor(0,102,204);
$pdf->Cell(0,10,utf8_decode('Factura - Pedido #') . $pedido_id,0,1,'C');
$pdf->SetFont('Arial','',12);
$pdf->SetTextColor(0);
$pdf->Ln(5);
$pdf->SetX(15);
$pdf->Cell(0,6,utf8_decode('Nombre: ' . $datos['nombre']),0,1);
$pdf->SetX(15);
$pdf->Cell(0,6,utf8_decode('Dirección: ' . $datos['direccion']),0,1);
$pdf->SetX(15);
$pdf->Cell(0,6,utf8_decode('Teléfono: ' . $datos['telefono']),0,1);
$pdf->SetX(15);
$pdf->Cell(0,6,utf8_decode('Método de pago: ' . ucfirst($datos['metodo_pago'])),0,1);
$pdf->SetX(15);
$pdf->Cell(0,6,utf8_decode('Fecha: ' . date('Y-m-d H:i:s')),0,1);
$pdf->Ln(10);
$pdf->SetFillColor(0,102,204);
$pdf->SetTextColor(255);
$pdf->SetFont('Arial','B',12);
$pdf->SetX(15);
$pdf->Cell(70,10,utf8_decode('Producto'),1,0,'C',true);
$pdf->Cell(25,10,utf8_decode('Talla'),1,0,'C',true);
$pdf->Cell(25,10,utf8_decode('Cantidad'),1,0,'C',true);
$pdf->Cell(35,10,utf8_decode('Precio Unit.'),1,0,'C',true);
$pdf->Cell(35,10,utf8_decode('Subtotal'),1,1,'C',true);
$pdf->SetFont('Arial','',12);
$pdf->SetTextColor(0);

$total_bruto = 0;
while ($row = $result->fetch_assoc()) {
    $nombre_producto = utf8_decode($row['nombre']);
    $subtotal = $row['subtotal'];
    $total_bruto += $subtotal;

    $pdf->SetX(15);
    $pdf->Cell(70,10,$nombre_producto,1);
    $pdf->Cell(25,10,$row['talla'],1,0,'C');
    $pdf->Cell(25,10,$row['cantidad'],1,0,'C');
    $pdf->Cell(35,10,number_format($row['precio_unitario'],2).' '.chr(128),1,0,'R');
    $pdf->Cell(35,10,number_format($subtotal,2).' '.chr(128),1,1,'R');
}

if (!empty($codigo_cupon) && $descuento > 0) {
    $pdf->SetX(15);
    $pdf->Cell(155,10,utf8_decode("Cupón aplicado: $codigo_cupon"),1,0,'R');
    $pdf->Cell(35,10,'-'.number_format($descuento,2).' '.chr(128),1,1,'R');
}

$pdf->SetFont('Arial','B',14);
$pdf->SetX(15);
$pdf->Cell(155,10,utf8_decode('TOTAL FINAL'),1,0,'R');
$pdf->Cell(35,10,number_format($total_final,2).' '.chr(128),1,1,'R');

$pdf_path = sys_get_temp_dir() . "/factura_pedido_$pedido_id.pdf";
$pdf->Output("F", $pdf_path);

$mailBody = '
<div style="font-family: Arial, sans-serif; background: #f4f4f4; padding: 30px;">
  <div style="max-width: 600px; margin: auto; background: white; border: 1px solid #ddd; border-radius: 5px; overflow: hidden;">
    <div style="background: #007bff; color: white; text-align: center; padding: 20px;">
      <h2 style="margin: 0;">UrbanWear</h2>
      <p style="margin: 0;">Confirmación de Pedido</p>
    </div>
    <div style="padding: 30px; text-align: center;">
      <img src="https://espabilacurrotfg202425.online/img/logo.png" alt="UrbanWear" style="max-width: 100px; margin-bottom: 20px;">
      <p>Hola <strong>' . htmlspecialchars($datos['nombre']) . '</strong>,</p>
      <p>Gracias por tu compra. Aquí tienes los detalles de tu pedido:</p>
      <table width="100%" cellpadding="10" cellspacing="0" style="border-collapse: collapse; font-size: 14px; margin-top: 20px;">
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

foreach ($productos as $row) {
    $precio = $row['precio'];
    $cantidad = $row['cantidad'];
    $subtotal = $precio * $cantidad;
    $proporcion = $subtotal / $total_sin_descuento;
    $subtotal_con_descuento = $subtotal - ($descuento * $proporcion);

    $mailBody .= '
        <tr>
          <td style="border: 1px solid #ddd;">' . htmlspecialchars($row['nombre']) . '</td>
          <td style="border: 1px solid #ddd; text-align: center;">' . $cantidad . '</td>
          <td style="border: 1px solid #ddd; text-align: center;">' . $row['talla'] . '</td>
          <td style="border: 1px solid #ddd; text-align: right;">' . number_format($precio, 2) . ' €</td>
          <td style="border: 1px solid #ddd; text-align: right;">' . number_format($subtotal_con_descuento, 2) . ' €</td>
        </tr>';
}

$mailBody .= '
        <tr>
          <td colspan="4" style="text-align: right; border: 1px solid #ddd;"><strong>Subtotal</strong></td>
          <td style="border: 1px solid #ddd; text-align: right;">' . number_format($total_sin_descuento, 2) . ' €</td>
        </tr>';

if ($descuento > 0) {
    $mailBody .= '
        <tr style="background: #fdecea;">
          <td colspan="4" style="text-align: right; border: 1px solid #ddd;"><strong>Descuento aplicado</strong><br><small>(Cupón: <em>' . $codigo_cupon . '</em>)</small></td>
          <td style="border: 1px solid #ddd; text-align: right;">- ' . number_format($descuento, 2) . ' €</td>
        </tr>';
}

$mailBody .= '
        <tr style="background: #eafaf1;">
          <td colspan="4" style="text-align: right; border: 1px solid #ddd;"><strong>Total final</strong></td>
          <td style="border: 1px solid #ddd; text-align: right;"><strong>' . number_format($total_final, 2) . ' €</strong></td>
        </tr>
      </tbody>
    </table>';

if ($datos['metodo_pago'] === 'transferencia') {
    $mailBody .= '
      <div style="margin-top: 30px; text-align: left;">
        <h3>Pago por transferencia</h3>
        <p>Por favor, realiza una transferencia a esta cuenta:</p>
        <ul>
          <li><strong>IBAN:</strong> ES12 3456 7890 1234 5678 9012</li>
          <li><strong>Titular:</strong> UrbanWear SL</li>
          <li><strong>Concepto:</strong> Pedido #' . $pedido_id . '</li>
        </ul>
        <p>Tu pedido se procesará tras recibir el pago.</p>
      </div>';
}

$mailBody .= '
      <p style="margin-top: 30px;">Si tienes dudas, escríbenos a <a href="mailto:urbanweartfg@espabilacurrotfg202425.online">urbanweartfg@espabilacurrotfg202425.online</a>.</p>
    </div>
    <div style="background: #f2f2f2; text-align: center; padding: 15px; font-size: 12px; color: #777;">
      © 2025 UrbanWear. Todos los derechos reservados.
    </div>
  </div>
</div>';

// Enviar correo
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
    $mail->isHTML(true);
    $mail->Subject = "Confirmación de pedido #$pedido_id";
    $mail->CharSet = 'UTF-8';
    $mail->Body = $mailBody;
    $mail->addAttachment($pdf_path, "Factura_Pedido_$pedido_id.pdf");
    $mail->send();
} catch (Exception $e) {
    error_log("Error al enviar correo: " . $mail->ErrorInfo);
}

$_SESSION['mensaje_exito'] = "Pedido realizado con éxito.";
header("Location: " . ($usuario_id ? "mis_pedidos.php" : "gracias.php"));
exit();
