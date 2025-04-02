<?php
session_start();
include "includes/header.php"; // Incluye el db.php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST["nombre"]);
    $email = trim($_POST["email"]);
    $contrasena = trim($_POST["contrasena"]); // Evito espacios en blanco al principio y al final

    if ($conn) {
        // Consulta para verificar el usuario por nombre o email
        $query = "SELECT * FROM usuarios WHERE nombre='" . mysqli_real_escape_string($conn, $nombre) . "' 
                  OR email='" . mysqli_real_escape_string($conn, $email) . "'";
                  
                  // Contrasena no hace falta en el registro, ya que se registra al crear la cuenta
        
        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) == 0) {

            //echo "<div class='alert alert-success text-center' role='alert'>El usuario no existe, puedes registrarte</div>";
            $insert = "INSERT INTO usuarios (nombre, email, contrasena, rol_id) VALUES (
                '" . mysqli_real_escape_string($conn, $nombre) . "',
                '" . mysqli_real_escape_string($conn, $email) . "',
                '" . mysqli_real_escape_string($conn,base64_encode($_POST['contrasena'])) . "',
                1
            )";
            
            if (mysqli_query($conn, $insert)) {
                //echo "<div class='alert alert-success text-center' role='alert'>Registro exitoso!!</div>";
                $_SESSION['usuario_id'] = mysqli_insert_id($conn);
                $_SESSION['usuario_nombre'] = $nombre;
                $_SESSION['usuario_rol'] = 1;
                
                echo "<div class='alert alert-success text-center' role='alert'> Registro exitoso!! Redirigiendo...</div>";
                echo "<script> setTimeout(function() { window.location.href = 'index.php'; }, 2000); </script>";
                exit();
            
            } else {
                echo "<div class='alert alert-danger text-center' role='alert'>Error al registrar el usuario</div>";
            }
            
            
        } else {
            echo "<div class='alert alert-danger text-center' role='alert'>El usuario ya existe!!</div>";
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
                <h2 class="text-center mb-4">Regístrate</h2>

                <div class="input-group mb-3">
                    <span class="input-group-text">
                        <i class="bi bi-person"></i>
                    </span>
                    <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Nombre de usuario" required>
                </div>

                <div class="input-group mb-3">
                    <span class="input-group-text">
                        <i class="bi bi-envelope"></i>
                    </span>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Correo electrónico" required>
                </div>

                <div class="input-group mb-3">
                    <span class="input-group-text">
                        <i class="bi bi-key"></i>
                    </span>
                    <input type="password" class="form-control" id="contrasena" name="contrasena" placeholder="Contraseña" required>
                </div>

                <div class="d-flex justify-content-between">
                    <button type="submit" class="btn btn-success">Registrar</button>
                </div>
                <div class="text-center mt-3">
                    <p>¿Ya tienes una cuenta? <a href="login.php">Inicia sesión</a></p>
                </div>
            </form>
        </div>
    </div>
</body>


<?php include "includes/footer.php"; ?>
