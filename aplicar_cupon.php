<?php
session_start();
require "includes/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = trim($_POST['codigo_cupon'] ?? '');

    if (!$codigo) {
        $_SESSION['mensaje_cupon'] = "Introduce un código de cupón.";
        header("Location: carrito.php");
        exit();
    }

    $stmt = $conn->prepare("SELECT * FROM cupones WHERE codigo = ? AND activo = 1 AND fecha_inicio <= CURDATE() AND fecha_fin >= CURDATE()");
    $stmt->bind_param("s", $codigo);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 0) {
        $_SESSION['mensaje_cupon'] = "Cupón inválido o expirado.";
        unset($_SESSION['cupon_aplicado']);
        header("Location: carrito.php");
        exit();
    }

    $cupon = $resultado->fetch_assoc();

    $_SESSION['cupon_aplicado'] = [
        'codigo' => $cupon['codigo'],
        'tipo'   => $cupon['tipo'],   // 'porcentaje' o 'cantidad'
        'valor'  => $cupon['valor']
    ];

    $_SESSION['mensaje_cupon'] = "Cupón '{$cupon['codigo']}' aplicado correctamente.";
    header("Location: carrito.php");
    exit();
}

header("Location: carrito.php");
exit();
