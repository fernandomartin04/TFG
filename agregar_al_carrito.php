<?php
session_start();
require "includes/db.php";

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['id'];
$producto_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$talla = isset($_GET['talla']) ? $_GET['talla'] : null;

if ($producto_id > 0) {
    if ($talla !== null) {
        $check = $conn->prepare("SELECT cantidad FROM carritos WHERE usuario_id = ? AND producto_id = ? AND talla = ?");
        $check->bind_param("iis", $usuario_id, $producto_id, $talla);
    } else {
        $check = $conn->prepare("SELECT cantidad FROM carritos WHERE usuario_id = ? AND producto_id = ? AND talla IS NULL");
        $check->bind_param("ii", $usuario_id, $producto_id);
    }

    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        if ($talla !== null) {
            $update = $conn->prepare("UPDATE carritos SET cantidad = cantidad + 1 WHERE usuario_id = ? AND producto_id = ? AND talla = ?");
            $update->bind_param("iis", $usuario_id, $producto_id, $talla);
        } else {
            $update = $conn->prepare("UPDATE carritos SET cantidad = cantidad + 1 WHERE usuario_id = ? AND producto_id = ? AND talla IS NULL");
            $update->bind_param("ii", $usuario_id, $producto_id);
        }
        $update->execute();
    } else {
        $insert = $conn->prepare("INSERT INTO carritos (usuario_id, producto_id, cantidad, talla) VALUES (?, ?, 1, ?)");
        $insert->bind_param("iis", $usuario_id, $producto_id, $talla);
        $insert->execute();
    }
}

header("Location: carrito.php");
exit();
?>
