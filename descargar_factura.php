<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);

session_start();
require "includes/db.php";
require "includes/fpdf/fpdf.php";

if (!isset($_SESSION['usuario_id']) || !isset($_GET['pedido_id'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$pedido_id = intval($_GET['pedido_id']);

$stmt = $conn->prepare("SELECT * FROM pedidos WHERE id = ? AND usuario_id = ?");
$stmt->bind_param("ii", $pedido_id, $usuario_id);
$stmt->execute();
$pedido = $stmt->get_result()->fetch_assoc();

if (!$pedido) {
    die("No tienes acceso a este pedido.");
}

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

// Logo centrado arriba
$pdf->Image('https://espabilacurrotfg202425.online/img/logo.png', ($pdf->GetPageWidth()/2) - 20, 10, 40);

// Título
$pdf->SetY(55);
$pdf->SetFont('Arial','B',20);
$pdf->SetTextColor(0,102,204);
$pdf->Cell(0,10,utf8_decode('Factura - Pedido #') . $pedido_id,0,1,'C');

$pdf->SetFont('Arial','',12);
$pdf->SetTextColor(0);
$pdf->Ln(5);

// Datos cliente
$pdf->SetX(15);
$pdf->Cell(0,6,utf8_decode('Nombre: ' . $pedido['nombre']),0,1);
$pdf->SetX(15);
$pdf->Cell(0,6,utf8_decode('Dirección: ' . $pedido['direccion']),0,1);
$pdf->SetX(15);
$pdf->Cell(0,6,utf8_decode('Teléfono: ' . $pedido['telefono']),0,1);
$pdf->SetX(15);
$pdf->Cell(0,6,utf8_decode('Método de pago: ' . ucfirst($pedido['metodo_pago'])),0,1);
$pdf->SetX(15);
$pdf->Cell(0,6,utf8_decode('Fecha: ' . $pedido['fecha']),0,1);

$pdf->Ln(10);

// Encabezados tabla
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

// Cupón aplicado
$hay_cupon = !empty($pedido['cupon_codigo']) && $pedido['cupon_descuento'] > 0;

// Línea cupón
if ($hay_cupon) {
    $pdf->SetFont('Arial','',12);
    $pdf->SetX(15);
    $pdf->Cell(155,10,utf8_decode("Cupón aplicado: " . $pedido['cupon_codigo']),1,0,'R');
    $pdf->Cell(35,10,'-'.number_format($pedido['cupon_descuento'],2).' '.chr(128),1,1,'R');
}

// Total final
$pdf->SetFont('Arial','B',14);
$pdf->SetX(15);
$pdf->Cell(155,10,utf8_decode('TOTAL FINAL'),1,0,'R');
$pdf->Cell(35,10,number_format($pedido['total'],2).' '.chr(128),1,1,'R');

// Limpiar buffer
if (ob_get_length()) {
    ob_end_clean();
}

$pdf->Output("I", "Factura_Pedido_$pedido_id.pdf");
exit();
?>
