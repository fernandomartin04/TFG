<?php
session_start();
include "includes/header.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include "includes/db.php"; // Asegúrate de que la conexión está disponible

    $email = trim($_POST["email"]); // Eliminar espacios en blanco al inicio y al final
    $contrasena = trim($_POST["contrasena"]);

    $stmt = $conn->prepare("SELECT id, nombre, contrasena, rol_id FROM usuario WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows == 1) {
        $row = $resultado->fetch_assoc();
        if (password_verify($contrasena, $row["contrasena"])) {
            $_SESSION["usuario_id"] = $row["id"];
            $_SESSION["usuario_nombre"] = $row["nombre"];
            $_SESSION["usuario_rol"] = $row["rol_id"];
            $_SESSION["usuario_email"] = $email;

            $_SESSION["mensaje"] = "<div class='alert alert-success text-center' role='alert'>Conexión exitosa, redirigiendo...</div>";
            header("Location: index.php");
            exit();
        } else {
            $_SESSION["mensaje"] = "<div class='alert alert-danger text-center' role='alert'>Contraseña incorrecta</div>";
        }
    } else {
        $_SESSION["mensaje"] = "<div class='alert alert-danger text-center' role='alert'>Usuario no encontrado</div>";
    }

    $stmt->close();
}
?>

<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <form class="col-sm-6 border p-4 rounded shadow bg-white" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <h2 class="text-center mb-4">Iniciar Sesión</h2>

                <?php
                if (isset($_SESSION["mensaje"])) {
                    echo $_SESSION["mensaje"];
                    unset($_SESSION["mensaje"]); // Eliminar el mensaje después de mostrarlo
                }
                ?>

                <div class="input-group mb-3">
                    <span class="input-group-text">
                        <i class="bi bi-envelope"></i>
                    </span>
                    <input type="email" class="form-control" name="email" placeholder="Correo electrónico" required>
                </div>

                <div class="input-group mb-3">
                    <span class="input-group-text">
                        <i class="bi bi-key"></i>
                    </span>
                    <input type="password" class="form-control" name="contrasena" placeholder="Contraseña" required>
                </div>

                <div class="d-flex justify-content-between">
                    <input type="submit" class="btn btn-success" value="Iniciar Sesión">
                    <a href="registro.php" class="btn btn-secondary">Registrarse</a>
                </div>
            </form>
        </div>
    </div>
</body>

<?php include "includes/footer.php"; ?>
