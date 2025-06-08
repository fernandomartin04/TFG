<?php
session_start();
require "includes/db.php";

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$usuario_id  = $_SESSION['usuario_id'];
$producto_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$talla       = isset($_POST['talla']) ? trim($_POST['talla']) : '';

if ($producto_id <= 0 || empty($talla)) {
    die("Error: Faltan datos obligatorios (producto o talla).");
}

// Verificar si ya existe ese producto con la misma talla en el carrito
$query = "SELECT id, cantidad FROM carritos WHERE usuario_id = ? AND producto_id = ? AND talla = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("iis", $usuario_id, $producto_id, $talla);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $nueva_cantidad = $row['cantidad'] + 1;
    $update = $conn->prepare("UPDATE carritos SET cantidad = ? WHERE id = ?");
    $update->bind_param("ii", $nueva_cantidad, $row['id']);
    $update->execute();
} else {
    $insert = $conn->prepare("INSERT INTO carritos (usuario_id, producto_id, cantidad, talla) VALUES (?, ?, 1, ?)");
    $insert->bind_param("iis", $usuario_id, $producto_id, $talla);
    $insert->execute();
}

header("Location: carrito.php");
exit();
