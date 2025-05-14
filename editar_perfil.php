<?php include "includes/header.php"; ?>

<?php
// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$id_usuario = $_SESSION['id'];

// Obtener datos actuales del usuario
$query = "SELECT * FROM usuarios WHERE id = $id_usuario";
$resultado = mysqli_query($conn, $query);

if ($resultado && mysqli_num_rows($resultado) > 0) {
    $usuario = mysqli_fetch_assoc($resultado);
} else {
    echo "<div class='container mt-4 alert alert-danger'>Error al cargar los datos del usuario.</div>";
    include "includes/footer.php";
    exit();
}

// Actualizar datos si se envía el formulario
if (isset($_POST['guardar'])) {
    $nuevo_nombre = trim($_POST['nombre']);
    $nuevo_email = trim($_POST['email']);

    if (filter_var($nuevo_email, FILTER_VALIDATE_EMAIL)) {
        $query_actualizar = "UPDATE usuarios SET nombre = '$nuevo_nombre', email = '$nuevo_email' WHERE id = $id_usuario";
        if (mysqli_query($conn, $query_actualizar)) {
            echo "<div class='container mt-4 alert alert-success'>Datos actualizados correctamente.</div>";
            $_SESSION['nombre'] = $nuevo_nombre;
        } else {
            echo "<div class='container mt-4 alert alert-danger'>Error al actualizar los datos: " . mysqli_error($conn) . "</div>";
        }
    } else {
        echo "<div class='container mt-4 alert alert-warning'>El correo electrónico no es válido.</div>";
    }
}

// Cambiar contraseña
if (isset($_POST['cambiar_password'])) {
    $actual = base64_encode(trim($_POST['password_actual']));
    $nueva = base64_encode(trim($_POST['nueva_password']));
    $confirmacion = base64_encode(trim($_POST['confirmar_password']));

    if ($actual !== $usuario['contrasena']) {
        echo "<div class='container mt-4 alert alert-danger'>La contraseña actual no es válida.</div>";
    } elseif ($nueva !== $confirmacion) {
        echo "<div class='container mt-4 alert alert-warning'>Las nuevas contraseñas no coinciden.</div>";
    } else {
        $query_cambio = "UPDATE usuarios SET contrasena = '$nueva' WHERE id = $id_usuario";
        if (mysqli_query($conn, $query_cambio)) {
            echo "<div class='container mt-4 alert alert-success'>Contraseña actualizada correctamente.</div>";
        } else {
            echo "<div class='container mt-4 alert alert-danger'>Error al actualizar la contraseña: " . mysqli_error($conn) . "</div>";
        }
    }
}
?>

<div class="container mt-5">
    <h2>Editar Perfil</h2>
    <form method="POST">
        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre de usuario</label>
            <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($usuario['usuario']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Correo electrónico</label>
            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
        </div>
        <button type="submit" class="btn btn-primary" name="guardar">Guardar cambios</button>
    </form>

    <hr class="my-4">

    <h4>Cambiar Contraseña</h4>
    <form method="POST">
        <div class="mb-3">
            <label for="password_actual" class="form-label">Contraseña actual</label>
            <input type="password" class="form-control" id="password_actual" name="password_actual" required>
        </div>
        <div class="mb-3">
            <label for="nueva_password" class="form-label">Nueva contraseña</label>
            <input type="password" class="form-control" id="nueva_password" name="nueva_password" required>
        </div>
        <div class="mb-3">
            <label for="confirmar_password" class="form-label">Confirmar nueva contraseña</label>
            <input type="password" class="form-control" id="confirmar_password" name="confirmar_password" required>
        </div>
        <button type="submit" class="btn btn-warning" name="cambiar_password">Cambiar contraseña</button>
    </form>
</div>

<?php include "includes/footer.php"; ?>
