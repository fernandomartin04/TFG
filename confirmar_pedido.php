<?php
session_start();
$errores = $_SESSION['errores_formulario'] ?? [];
$datos   = $_SESSION['datos_formulario'] ?? [];

unset($_SESSION['errores_formulario'], $_SESSION['datos_formulario']);
?>

<?php include "includes/header.php"; ?>

<div class="container mt-5">
    <h2>Confirmar Pedido</h2>
    <form action="procesar_pedido.php" method="POST">
        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre completo</label>
            <input type="text" class="form-control <?= isset($errores['nombre']) ? 'is-invalid' : '' ?>" id="nombre" name="nombre"
                   value="<?= htmlspecialchars($datos['nombre'] ?? '') ?>" required>
            <?php if (isset($errores['nombre'])): ?>
                <div class="invalid-feedback"><?= $errores['nombre'] ?></div>
            <?php endif; ?>
        </div>

        <div class="mb-3">
            <label for="direccion" class="form-label">Dirección de envío</label>
            <textarea class="form-control <?= isset($errores['direccion']) ? 'is-invalid' : '' ?>" id="direccion" name="direccion" required><?= htmlspecialchars($datos['direccion'] ?? '') ?></textarea>
            <?php if (isset($errores['direccion'])): ?>
                <div class="invalid-feedback"><?= $errores['direccion'] ?></div>
            <?php endif; ?>
        </div>

        <div class="mb-3">
            <label for="telefono" class="form-label">Teléfono</label>
            <input type="text" class="form-control <?= isset($errores['telefono']) ? 'is-invalid' : '' ?>" id="telefono" name="telefono"
                   value="<?= htmlspecialchars($datos['telefono'] ?? '') ?>" required>
            <?php if (isset($errores['telefono'])): ?>
                <div class="invalid-feedback"><?= $errores['telefono'] ?></div>
            <?php endif; ?>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Correo electrónico (opcional)</label>
            <input type="email" class="form-control <?= isset($errores['email']) ? 'is-invalid' : '' ?>" id="email" name="email"
                   value="<?= htmlspecialchars($datos['email'] ?? '') ?>">
            <?php if (isset($errores['email'])): ?>
                <div class="invalid-feedback"><?= $errores['email'] ?></div>
            <?php endif; ?>
        </div>

        <div class="mb-3">
            <label for="metodo_pago" class="form-label">Método de pago</label>
            <select class="form-select <?= isset($errores['metodo_pago']) ? 'is-invalid' : '' ?>" name="metodo_pago" required>
                <option value="">Seleccionar...</option>
                <option value="tarjeta" <?= ($datos['metodo_pago'] ?? '') === 'tarjeta' ? 'selected' : '' ?>>Tarjeta</option>
                <option value="paypal" <?= ($datos['metodo_pago'] ?? '') === 'paypal' ? 'selected' : '' ?>>PayPal</option>
                <option value="transferencia" <?= ($datos['metodo_pago'] ?? '') === 'transferencia' ? 'selected' : '' ?>>Transferencia</option>
            </select>
            <?php if (isset($errores['metodo_pago'])): ?>
                <div class="invalid-feedback"><?= $errores['metodo_pago'] ?></div>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn btn-primary">Confirmar pedido</button>
    </form>
</div>
