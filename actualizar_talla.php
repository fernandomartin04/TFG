include "includes/header.php";
<?php
require "includes/db.php";

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$carrito_id = $_POST['carrito_id'] ?? null;
$nueva_talla = $_POST['nueva_talla'] ?? null;
$usuario_id = $_SESSION['id'];

if (!$carrito_id || !$nueva_talla) {
    header("Location: carrito.php");
    exit();
}

// Verificamos que el item pertenezca al usuario antes de actualizar
$sql = "UPDATE carritos SET talla = ? WHERE id = ? AND usuario_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sii", $nueva_talla, $carrito_id, $usuario_id);
$stmt->execute();

header("Location: carrito.php");
exit();
?>