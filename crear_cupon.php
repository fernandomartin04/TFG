<?php
session_start();
require_once "includes/db.php";

if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 3) {
    // Solo admins
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = trim($_POST['codigo']);
    $tipo = $_POST['tipo'];
    $valor = floatval($_POST['valor']);
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];
    $activo = isset($_POST['activo']) ? 1 : 0;

    // Validaciones básicas
    if (!$codigo || !in_array($tipo, ['porcentaje', 'cantidad']) || $valor <= 0 || !$fecha_inicio || !$fecha_fin) {
        die("Datos del cupón no válidos.");
    }

    // Comprobar que el código no existe ya
    $stmtCheck = $conn->prepare("SELECT id FROM cupones WHERE codigo = ?");
    $stmtCheck->bind_param("s", $codigo);
    $stmtCheck->execute();
    $stmtCheck->store_result();

    if ($stmtCheck->num_rows > 0) {
        die("El código de cupón ya existe.");
    }

    // Insertar en la base de datos
    $stmt = $conn->prepare("INSERT INTO cupones (codigo, tipo, valor, fecha_inicio, fecha_fin, activo) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdssi", $codigo, $tipo, $valor, $fecha_inicio, $fecha_fin, $activo);

    if ($stmt->execute()) {
        header("Location: panel_admin.php?msg=cupon_creado");
        exit();
    } else {
        die("Error al crear el cupón.");
    }
} else {
    header("Location: panel_admin.php");
    exit();
}
