<?php
session_start();
require "includes/db.php";

$usuario_id  = $_SESSION['usuario_id'] ?? null;
$producto_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$talla       = isset($_POST['talla']) ? trim($_POST['talla']) : '';
$cantidad    = isset($_POST['cantidad']) ? intval($_POST['cantidad']) : 1;

if ($producto_id <= 0 || empty($talla) || $cantidad <= 0) {
    die("Error: Faltan datos obligatorios.");
}

if ($usuario_id) {
    // Usuario logueado guardar en base de datos
    $query = "SELECT id, cantidad FROM carritos WHERE usuario_id = ? AND producto_id = ? AND talla = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iis", $usuario_id, $producto_id, $talla);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $nueva_cantidad = $row['cantidad'] + $cantidad;
        $update = $conn->prepare("UPDATE carritos SET cantidad = ? WHERE id = ?");
        $update->bind_param("ii", $nueva_cantidad, $row['id']);
        $update->execute();
    } else {
        $insert = $conn->prepare("INSERT INTO carritos (usuario_id, producto_id, cantidad, talla) VALUES (?, ?, ?, ?)");
        $insert->bind_param("iiis", $usuario_id, $producto_id, $cantidad, $talla);
        $insert->execute();
    }
} else {
    // Usuario no logueado guardar en $_SESSION
    if (!isset($_SESSION['carrito'])) {
        $_SESSION['carrito'] = [];
    }

    $producto_existente = false;

    foreach ($_SESSION['carrito'] as &$item) {
        if ($item['producto_id'] == $producto_id && $item['talla'] === $talla) {
            $item['cantidad'] += $cantidad;
            $producto_existente = true;
            break;
        }
    }
    unset($item); // Por seguridad

    if (!$producto_existente) {
        $_SESSION['carrito'][] = [
            'producto_id' => $producto_id,
            'cantidad'    => $cantidad,
            'talla'       => $talla
        ];
    }
}

header("Location: carrito.php");
exit();
