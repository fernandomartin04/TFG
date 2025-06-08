<?php
session_start();
require_once "includes/db.php";
require_once "includes/PHPMailer/PHPMailer.php";
require_once "includes/PHPMailer/SMTP.php";
require_once "includes/PHPMailer/Exception.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Recoger datos del formulario POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: confirmar_pedido.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'] ?? null;

$datos = [
    'nombre'       => trim($_POST['nombre'] ?? ''),
    'direccion'    => trim($_POST['direccion'] ?? ''),
    'telefono'     => trim($_POST['telefono'] ?? ''),
    'email'        => trim($_POST['email'] ?? ''),
    'metodo_pago'  => trim($_POST['metodo_pago'] ?? '')
];

// Validación de datos
$errores = [];

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

if (!empty($errores)) {
    $_SESSION['errores_formulario'] = $errores;
    $_SESSION['datos_formulario'] = $datos;
    header("Location: confirmar_pedido.php");
    exit();
}

// Guardar datos válidos para finalizar_pago.php o validar_pago.php
$_SESSION['datos_formulario'] = $datos;

// Guardar carrito actual en sesión para procesarlo después
$carrito_items = [];

if ($usuario_id) {
    // Usuario logueado → leer del carrito en base de datos
    $stmt = $conn->prepare("SELECT producto_id, cantidad, talla FROM carritos WHERE usuario_id = ?");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($fila = $result->fetch_assoc()) {
        $carrito_items[] = $fila;
    }
} elseif (!empty($_SESSION['carrito'])) {
    // Usuario no logueado → usar carrito en sesión
    $carrito_items = $_SESSION['carrito'];
}

if (empty($carrito_items)) {
    $_SESSION['mensaje_cupon'] = "Tu carrito está vacío.";
    header("Location: carrito.php");
    exit();
}

$_SESSION['carrito_para_pedido'] = $carrito_items;

// Redirigir según método de pago
if ($datos['metodo_pago'] === 'tarjeta') {
    header("Location: validar_pago.php");
} else {
    header("Location: finalizar_pago.php");
}
exit();
