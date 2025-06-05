<?php
session_start();
include "includes/header.php"; // Incluye conexión a BD

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST["nombre"]);
    $contrasena = $_POST["contrasena"];

    if ($conn) {
        // Consulta preparada segura para evitar inyección SQL
        $stmt = $conn->prepare("SELECT id, nombre, email, contrasena, rol_id FROM usuarios WHERE nombre = ? OR email = ?");
        $stmt->bind_param("ss", $nombre, $nombre);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows === 1) {
            $row = $resultado->fetch_assoc();

            // Verifica la contraseña hasheada
            if (password_verify($contrasena, $row['contrasena'])) {
                $_SESSION['id'] = $row['id'];
                $_SESSION['nombre'] = $row['nombre'];
                $_SESSION['rol_id'] = $row['rol_id'];

                header("Location: index.php");
                exit();
            } else {
                $mensaje = "Usuario o contraseña incorrectos.";
            }
        } else {
            $mensaje = "Usuario o contraseña incorrectos.";
        }
    } else {
        $mensaje = "Error de conexión a la base de datos.";
    }
}
?>

<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <form class="col-sm-6 border p-4 rounded shadow bg-white" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>" method="post">
                <h2 class="text-center mb-4">Iniciar Sesión</h2>

                <?php if (!empty($mensaje)): ?>
                    <div class='alert alert-danger text-center' role='alert'>
                        <?= htmlspecialchars($mensaje) ?>
                    </div>
                <?php endif; ?>

                <div class="input-group mb-3">
                    <span class="input-group-text">
                        <i class="bi bi-person"></i>
                    </span>
                    <input type="text" class="form-control" name="nombre" placeholder="Usuario o Correo Electrónico" required>
                </div>

                <div class="input-group mb-3">
                    <span class="input-group-text">
                        <i class="bi bi-key"></i>
                    </span>
                    <input type="password" class="form-control" name="contrasena" placeholder="Contraseña" required>
                </div>

                <div class="d-flex justify-content-between">
                    <input type="submit" class="btn btn-success" value="Iniciar Sesión">
                </div>

                <div class="text-center mt-3">
                    <p>¿No tienes cuenta? <a href="registro.php">Regístrate aquí</a></p>
                </div>
            </form>
        </div>
    </div>
</body>

<?php include "includes/footer.php"; ?>
