<?php
session_start();

if ($_SESSION['rol_id'] != 3) {
    header("Location: login.php");
    exit();
}

include "includes/header.php";

if (isset($_POST['id'])) {
    if ($conn) {
        $id = $_POST['id'];

        $queryObtenerUsuario = "SELECT * FROM usuarios WHERE id = $id";
        $resultado = mysqli_query($conn, $queryObtenerUsuario);

        if ($resultado && mysqli_num_rows($resultado) != false) {
            $usuario = mysqli_fetch_assoc($resultado);

            $nombreUsuario = $usuario['nombre']; // Utilizamos el nombre de usuario obtenido

            // Ahora eliminar el usuario
            $queryEliminarUsuario = "DELETE FROM usuarios WHERE id = $id";
            if (mysqli_query($conn, $queryEliminarUsuario)) {
                header("Location: panel_admin.php");
                exit();
            } else {
                echo "Error al eliminar el usuario: " . mysqli_error($conn);
            }
        } else {
            echo "Error al obtener información del usuario.";
        }
    } else {
        echo "Error: No se pudo conectar a MySQL.";
    }
}
?>