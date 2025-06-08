<?php
// Mostrar errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require "includes/db.php";
session_start();

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = trim($_POST['nombre']);
    $password = $_POST['password'];

    if (!empty($nombre) && !empty($password)) {
        $stmt = $conn->prepare("SELECT id, nombre, contrasena, rol_id FROM usuarios WHERE nombre = ?");
        $stmt->bind_param("s", $nombre);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows === 1) {
            $usuario = $res->fetch_assoc();

            if (password_verify($password, $usuario['contrasena'])) {
                // ✅ Regenerar ID de sesión tras autenticación exitosa
                session_regenerate_id(true);

                // Guardar datos del usuario en la sesión
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['nombre']     = $usuario['nombre'];
                $_SESSION['rol_id']     = $usuario['rol_id'];

                header("Location: index.php");
                exit();
            } else {
                $error = "Contraseña incorrecta.";
            }
        } else {
            $error = "No se encontró ningún usuario con ese nombre.";
        }
    } else {
        $error = "Debes completar todos los campos.";
    }
}
?>

<?php include "includes/header.php"; ?>

<div class="container mt-5">
    <h2>Iniciar sesión</h2>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre de usuario:</label>
            <input type="text" class="form-control" name="nombre" id="nombre" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Contraseña:</label>
            <input type="password" class="form-control" name="password" id="password" required>
        </div>
        <button type="submit" class="btn btn-primary">Iniciar sesión</button>
    </form>

    <div class="mt-3">
        <a href="recuperar.php" class="text-decoration-none">¿Has olvidado tu contraseña?</a>
    </div>
</div>

<?php include "includes/footer.php"; ?>
