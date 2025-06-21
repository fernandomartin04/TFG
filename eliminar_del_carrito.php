<?php
session_start();
require "includes/db.php";

$usuario_id   = $_SESSION['usuario_id'] ?? null;
$producto_id  = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$talla        = isset($_GET['talla']) ? $_GET['talla'] : '';

if ($producto_id > 0) {
    if ($usuario_id) {
        // Usuario logueado eliminar de la base de datos
        $stmt = $conn->prepare("DELETE FROM carritos WHERE usuario_id = ? AND producto_id = ? AND talla = ?");
        $stmt->bind_param("iis", $usuario_id, $producto_id, $talla);
        $stmt->execute();
    } else {
        // Usuario no logueado eliminar de $_SESSION['carrito']
        if (!empty($_SESSION['carrito'])) {
            foreach ($_SESSION['carrito'] as $index => $item) {
                if ($item['producto_id'] == $producto_id && $item['talla'] === $talla) {
                    unset($_SESSION['carrito'][$index]);
                    $_SESSION['carrito'] = array_values($_SESSION['carrito']); // Reindexar
                    break;
                }
            }
        }
    }
}

header("Location: carrito.php");
exit();
