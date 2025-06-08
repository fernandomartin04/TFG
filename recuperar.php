<?php
session_start();
$mensaje = $_SESSION['mensaje_recuperacion'] ?? null;
$tipo = $_SESSION['tipo_mensaje_recuperacion'] ?? 'info';
unset($_SESSION['mensaje_recuperacion'], $_SESSION['tipo_mensaje_recuperacion']);
?>

<?php include "includes/header.php"; ?>

<div class="container mt-5">
    <h2>Recuperar contraseña</h2>

    <?php if ($mensaje): ?>
        <div class="alert alert-<?= $tipo ?> alert-dismissible fade show" role="alert">
            <?= $mensaje ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    <?php endif; ?>

    <form action="enviar_recuperacion.php" method="POST">
        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre de usuario:</label>
            <input type="text" class="form-control" name="nombre" id="nombre" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Correo electrónico:</label>
            <input type="email" class="form-control" name="email" id="email" required>
        </div>
        <button type="submit" class="btn btn-primary">Enviar enlace de recuperación</button>
    </form>
</div>

<?php include "includes/footer.php"; ?>
