<?php
session_start();
include "includes/header.php"; // incluye conexión BD

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST["nombre"]);
    $email = trim($_POST["email"]);
    $contrasena = $_POST["contrasena"];

    // Validación de contraseña (mínimo 12 caracteres, etc.)
    function validarContrasena($pwd) {
        return strlen($pwd) >= 12 &&
               preg_match('/[A-Z]/', $pwd) &&
               preg_match('/[a-z]/', $pwd) &&
               preg_match('/[0-9]/', $pwd) &&
               preg_match('/[\W]/', $pwd);
    }

    if (!validarContrasena($contrasena)) {
        $mensaje = "La contraseña debe tener al menos 12 caracteres, una mayúscula, una minúscula, un número y un símbolo.";
    } else {
        if ($conn) {
            // Verificar si el correo ya existe
            $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $mensaje = "El correo electrónico ya está registrado.";
            } else {
                // Insertar nuevo usuario con contraseña hasheada
                $hash = password_hash($contrasena, PASSWORD_BCRYPT, ["cost" => 12]);

                $stmt = $conn->prepare("INSERT INTO usuarios (nombre, email, contrasena, rol_id) VALUES (?, ?, ?, 1)");
                $stmt->bind_param("sss", $nombre, $email, $hash);
                if ($stmt->execute()) {
                    header("Location: login.php");
                    exit();
                } else {
                    $mensaje = "Error al registrar usuario.";
                }
            }
        } else {
            $mensaje = "Error de conexión con la base de datos.";
        }
    }
}
?>

<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <form class="col-sm-6 border p-4 rounded shadow bg-white" method="POST">
                <h2 class="text-center mb-4">Registro de Usuario</h2>

                <?php if (!empty($mensaje)): ?>
                    <div class="alert alert-info text-center" role="alert">
                        <?= htmlspecialchars($mensaje) ?>
                    </div>
                <?php endif; ?>

                <div class="mb-3">
                    <label>Nombre</label>
                    <input type="text" class="form-control" name="nombre" required>
                </div>

                <div class="mb-3">
                    <label>Correo electrónico</label>
                    <input type="email" class="form-control" name="email" required>
                </div>

                <div class="mb-3">
                    <label>Contraseña</label>
                    <input type="password" class="form-control" name="contrasena" required>
                    <small class="text-muted">Mínimo 12 caracteres, con mayúsculas, minúsculas, números y símbolos.</small>
                </div>

                <input type="submit" class="btn btn-primary w-100" value="Registrarse">

                <div class="text-center mt-3">
                    <p>¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a></p>
                </div>
            </form>
        </div>
    </div>
</body>

<?php include "includes/footer.php"; ?>
