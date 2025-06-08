<?php
// includes/header.php

// Inicia la sesión solo si no ha sido iniciada ya
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UrbanWear</title>

    <!-- Bootstrap 5 CSS/JS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

    <style>
        body {
            padding-bottom: 100px;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">UrbanWear</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">

                <!-- Enlaces comunes -->
                <li class="nav-item"><a class="nav-link" href="index.php">Inicio</a></li>
                <li class="nav-item"><a class="nav-link" href="productos.php">Productos</a></li>
                <li class="nav-item"><a class="nav-link" href="carrito.php">Carrito</a></li>

                <!-- Si es administrador (rol_id = 3) -->
                <?php if (isset($_SESSION['rol_id']) && $_SESSION['rol_id'] == 3): ?>
                    <li class="nav-item"><a class="nav-link" href="panel_admin.php">Panel Admin</a></li>
                <?php endif; ?>

                <!-- Si está logueado (cualquier rol) -->
                <?php if (isset($_SESSION['usuario_id'])): ?>
                    <li class="nav-item"><a class="nav-link" href="editar_perfil.php">Editar Perfil</a></li>
                    <li class="nav-item"><a class="nav-link" href="mis_pedidos.php">Mis pedidos</a></li>
                <?php endif; ?>

                <!-- Si es vendedor (rol_id = 2) o administrador (3) -->
                <?php if (isset($_SESSION['rol_id']) && ($_SESSION['rol_id'] == 2 || $_SESSION['rol_id'] == 3)): ?>
                    <li class="nav-item"><a class="nav-link" href="anadir_producto.php">Añadir Producto</a></li>
                <?php endif; ?>
            </ul>

            <!-- Zona derecha: usuario o enlaces de login/registro -->
            <div class="d-flex align-items-center">
                <?php if (isset($_SESSION['usuario_id'])): ?>
                    <span class="navbar-text text-light me-3">
                        👤 <?= htmlspecialchars($_SESSION['nombre'] ?? '') ?>
                    </span>
                    <a href="logout.php" class="btn btn-outline-danger">Cerrar sesión</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline-light me-2">Iniciar sesión</a>
                    <a href="registro.php" class="btn btn-outline-success">Registrarse</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
