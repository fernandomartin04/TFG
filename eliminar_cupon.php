<?php
include "includes/db.php";
session_start();

// Solo admins pueden eliminar
if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 3) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    $query = "DELETE FROM cupones WHERE id = $id";
    if (mysqli_query($conn, $query)) {
        header("Location: pamel_admin.php"); 
        exit();
    } else {
        echo "Error al eliminar: " . mysqli_error($conn);
    }
} else {
    echo "ID no proporcionado.";
}
?>
