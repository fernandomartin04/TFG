<?php
session_start();
require "includes/db.php";

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['id'];
$producto_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($producto_id > 0) {
    $stmt = $conn->prepare("DELETE FROM carritos WHERE usuario_id = ? AND producto_id = ?");
    $stmt->bind_param("ii", $usuario_id, $producto_id);
    $stmt->execute();
}

header("Location: carrito.php");
exit();
?>
