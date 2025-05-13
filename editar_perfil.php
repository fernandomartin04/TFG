<?php
session_start();
include "includes/header.php";

if (!isset($_SESSION['nombre'])) {
    header("Location: login.php");
    exit();
}

$nombreUsuario = $_SESSION['nombre'];
$query = "SELECT * FROM usuarios WHERE nombre = '$nombreUsuario'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = htmlspecialchars($_POST['nombre']);
    $email = htmlspecialchars($_POST['email']);
    $contrasena = htmlspecialchars($_POST['contrasena']);
    $contrasena2 = htmlspecialchars($_POST['contrasena2']);
    $contrasena_codificada = base64_encode($contrasena);

    // Validación de email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "El correo no es válido.";
    } elseif ($contrasena != $contrasena2) {
        $error = "Las contraseñas no coinciden.";
    } else {
        // Actualizar los datos
        $updateQuery = "UPDATE usuarios SET nombre = '$nombre', email = '$email', contrasena = '$contrasena_codificada' WHERE nombre = '$nombreUsuario'";
        if (mysqli_query($conn, $updateQuery)) {
            $_SESSION['nombre'] = $nombre;  // Actualizamos la sesión con el nuevo nombre
            header("Location: perfil.php");  // Redirigir al perfil con mensaje de éxito
        } else {
            $error = "Error al actualizar los datos.";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil</title>
    <!-- Agrega los links y scripts necesarios -->
</head>
<body>
    <h1>Editar Perfil</h1>
    <?php if (isset($error)) { echo "<p class='error'>$error</p>"; } ?>
    <form method="POST" action="editar_perfil.php">
        <label for="nombre">Nombre:</label>
        <input type="text" name="nombre" id="nombre" value="<?php echo $user['nombre']; ?>" required><br>
        
        <label for="email">Correo:</label>
        <input type="email" name="email" id="email" value="<?php echo $user['email']; ?>" required><br>

        <label for="contrasena">Nueva Contraseña:</label>
        <input type="password" name="contrasena" id="contrasena"><br>

        <label for="contrasena2">Repetir Contraseña:</label>
        <input type="password" name="contrasena2" id="contrasena2"><br>

        <button type="submit">Actualizar</button>
    </form>
</body>
</html>
