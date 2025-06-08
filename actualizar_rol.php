<?php
session_start();
require_once "includes/db.php";

// Solo el administrador puede cambiar roles
if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 3) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['nuevo_rol'])) {
    $usuario_id = (int) $_POST['id'];
    $nuevo_rol = (int) $_POST['nuevo_rol'];

    $roles_validos = [1, 2, 3];
    if (!in_array($nuevo_rol, $roles_validos)) {
        header("Location: panel_admin.php?error=rol_invalido");
        exit();
    }

    $stmt = $conn->prepare("UPDATE usuarios SET rol_id = ? WHERE id = ?");
    $stmt->bind_param("ii", $nuevo_rol, $usuario_id);

    if ($stmt->execute()) {
        header("Location: panel_admin.php?success=rol_actualizado");
        exit();
    } else {
        header("Location: panel_admin.php?error=error_actualizar");
        exit();
    }
} else {
    header("Location: panel_admin.php");
    exit();
}
