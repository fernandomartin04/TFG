<?php
session_start();
require "includes/db.php";

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$carrito_id = isset($_POST['carrito_id']) ? (int) $_POST['carrito_id'] : 0;
$nueva_talla = isset($_POST['talla']) ? $_POST['talla'] : null;

if ($carrito_id > 0) {
    $stmt = $conn->prepare("UPDATE carritos SET talla = ? WHERE id = ?");
    $stmt->bind_param("si", $nueva_talla, $carrito_id);
    $stmt->execute();
}

header("Location: carrito.php");
exit();
