<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// CABECERAS DE SEGURIDAD
if (!headers_sent()) {
    header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");
    header("Content-Security-Policy: 
        default-src 'self'; 
        script-src 'self' https://cdn.jsdelivr.net 'unsafe-inline'; 
        style-src 'self' https://cdn.jsdelivr.net 'unsafe-inline'; 
        img-src 'self' data: https:; 
        font-src 'self' https://cdn.jsdelivr.net; 
        object-src 'none'; 
        base-uri 'self'; 
        frame-ancestors 'self';
    ");
    header("X-Frame-Options: SAMEORIGIN");
    header("X-Content-Type-Options: nosniff");
    header("Referrer-Policy: strict-origin-when-cross-origin");
    header("Permissions-Policy: geolocation=(), microphone=(), camera=()");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UrbanWear</title>
    <link rel="icon" type="image/png" href="img/favicon.png">

    <!-- Bootstrap 5 CSS/JS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

    <style>
        body {
            padding-bottom: 100px;
        }
        .navbar {
            padding-top: 1rem;
            padding-bottom: 1rem;
        }
        .navbar-brand img {
            height: 60px;
            background-color: white;
            border-radius: 8px;
            padding: 5px;
            display: block;
        }
        .navbar-dark .navbar-nav .nav-link {
            color: rgba(255, 255, 255, 0.9);
        }
        .navbar-dark .navbar-nav .nav-link:hover {
            color: #ffffff;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <img src="img/logo.png" alt="UrbanWear Logo">
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="index.php">Inicio</a></li>
                <li class="nav-item"><a class="nav-link" href="productos.php">Productos</a></li>
                <li class="nav-item"><a class="nav-link" href="carrito.php">Carrito</a></li>

                <?php if (isset($_SESSION['rol_id']) && $_SESSION['rol_id'] == 3): ?>
                    <li class="nav-item"><a class="nav-link" href="panel_admin.php">Panel Admin</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin_pedidos.php">Gestión Pedidos</a></li>
              		<li class="nav-item"><a class="nav-link" href="panel_productos.php">Gestión Productos</a></li>
                <?php endif; ?>

                <?php if (isset($_SESSION['usuario_id'])): ?>
                    <li class="nav-item"><a class="nav-link" href="editar_perfil.php">Editar Perfil</a></li>
                    <li class="nav-item"><a class="nav-link" href="mis_pedidos.php">Mis pedidos</a></li>
                <?php endif; ?>

                <?php if (isset($_SESSION['rol_id']) && ($_SESSION['rol_id'] == 2 || $_SESSION['rol_id'] == 3)): ?>
                    <li class="nav-item"><a class="nav-link" href="anadir_producto.php">Añadir Producto</a></li>
                <?php endif; ?>
            </ul>

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
