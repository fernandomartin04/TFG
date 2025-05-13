<?php 
include "db.php"; 
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Proyecto</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">UrbanWear</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Productos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Carrito</a>
                    </li>
                    <?php if (isset($_SESSION['rol_id']) && $_SESSION['rol_id'] == 3): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="../panel_admin.php">Panel Admin</a>
                        <?php endif; ?>
                    </li>
                    <?php if (isset($_SESSION['nombre'])): ?>
                    <li class="nav-item">
                        <a href="../editar_perfil.php" class="nav-link">Editar Perfil</a>
                    </li>
                
                    <?php endif; ?>
                </ul>
                <?php if (isset($_SESSION['rol_id'])): ?>
                    <span class="navbar-text text-light me-3">
                        👤 <?php echo $_SESSION['nombre']; ?>
                    </span>
                    <a href="../logout.php" class="btn btn-outline-danger ms-2">Cerrar sesión</a>
                <?php else: ?>
                    <a href="../login.php" class="btn btn-outline-light">Iniciar sesión</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
