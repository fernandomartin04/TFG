<?php
session_start();
require "includes/db.php";

$usuario_id   = $_SESSION['usuario_id'] ?? null;
$producto_id  = isset($_POST['producto_id']) ? intval($_POST['producto_id']) : 0;
$nueva_talla  = $_POST['talla'] ?? '';

$tallas_validas = ['XS', 'S', 'M', 'L', 'XL'];

if ($producto_id > 0 && in_array($nueva_talla, $tallas_validas)) {
    // Verificamos si hay stock suficiente para esa talla
    $stmt = $conn->prepare("SELECT stock FROM stock_productos_tallas WHERE producto_id = ? AND talla = ?");
    $stmt->bind_param("is", $producto_id, $nueva_talla);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row && $row['stock'] > 0) {
        // Si hay stock, actualizamos la talla
        if ($usuario_id) {
            $stmt = $conn->prepare("UPDATE carritos SET talla = ? WHERE usuario_id = ? AND producto_id = ?");
            $stmt->bind_param("sii", $nueva_talla, $usuario_id, $producto_id);
            $stmt->execute();
        } else {
            if (!empty($_SESSION['carrito'])) {
                foreach ($_SESSION['carrito'] as &$item) {
                    if ($item['producto_id'] == $producto_id) {
                        $item['talla'] = $nueva_talla;
                        break;
                    }
                }
                unset($item);
            }
        }
    } else {
        // No hay stock redirigimos con mensaje
        $_SESSION['error_carrito'] = "No hay stock disponible para la talla seleccionada.";
    }
}

header("Location: carrito.php");
exit();
