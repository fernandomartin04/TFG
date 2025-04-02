<?php
session_start();
include "includes/header.php"; // Incluye el db.php

//date_default_timezone_set('Europe/Madrid');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST["nombre"]);
    $contrasena = trim($_POST["contrasena"]); // Evito espacios en blanco al principio y al final
    $contrasenaCodificada = base64_encode($contrasena); // Codifico la contraseña
    if ($conn) {
        // Consulta para verificar el usuario por nombre o email
        $query = "SELECT * FROM usuarios WHERE (nombre='" . mysqli_real_escape_string($conn, $nombre) . "' 
                  OR email='" . mysqli_real_escape_string($conn, $nombre) . "') 
                  AND contrasena='" . mysqli_real_escape_string($conn, $contrasenaCodificada) . "'";
                  
        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) == 1) {
            $row = mysqli_fetch_assoc($result);

            $_SESSION['usuario_id'] = $row['id'];
            $_SESSION['usuario_nombre'] = $row['nombre'];
            $_SESSION['usuario_rol'] = $row['rol_id'];

            // Redirige según el rol
            if ($row['rol_id'] == 1) {
                header("Location: index.php");
                exit();
            } elseif ($row['rol_id'] == 2) {
                header("Location: index.php");
                exit();
            } elseif ($row['rol_id'] == 3) {
                header("Location: index.php");
                exit();
            }
        } else {
            echo "<div class='alert alert-danger text-center' role='alert'>Usuario o contraseña incorrectos</div>";
        }
    } else {
        echo "<div class='alert alert-danger text-center' role='alert'>Error de conexión a la base de datos</div>";
    }
}
?>

<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <form class="col-sm-6 border p-4 rounded shadow bg-white" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <h2 class="text-center mb-4">Iniciar Sesión</h2>

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
            </form>
        </div>
    </div>
</body>

<?php include "includes/footer.php"; ?>

