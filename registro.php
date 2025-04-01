<?php
session_start();
include "includes/header.php"; // Incluye el db.php
?>

<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <form class="col-sm-6 border p-4 rounded shadow bg-white" action="registro.php" method="post">
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
            </form>
        </div>
    </div>
</body>


<?php include "includes/footer.php"; ?>
