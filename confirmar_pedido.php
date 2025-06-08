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
            <div class="invalid-feedback"><?= $errores['nombre'] ?? '' ?></div>
        </div>

        <div class="mb-3">
            <label for="direccion" class="form-label">Dirección</label>
            <textarea class="form-control <?= isset($errores['direccion']) ? 'is-invalid' : '' ?>" id="direccion" name="direccion" required><?= htmlspecialchars($datos['direccion'] ?? '') ?></textarea>
            <div class="invalid-feedback"><?= $errores['direccion'] ?? '' ?></div>
        </div>

        <div class="mb-3">
            <label for="telefono" class="form-label">Teléfono</label>
            <input type="text" class="form-control <?= isset($errores['telefono']) ? 'is-invalid' : '' ?>" id="telefono" name="telefono"
                   value="<?= htmlspecialchars($datos['telefono'] ?? '') ?>" required>
            <div class="invalid-feedback"><?= $errores['telefono'] ?? '' ?></div>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Correo electrónico (opcional)</label>
            <input type="email" class="form-control <?= isset($errores['email']) ? 'is-invalid' : '' ?>" id="email" name="email"
                   value="<?= htmlspecialchars($datos['email'] ?? '') ?>">
            <div class="invalid-feedback"><?= $errores['email'] ?? '' ?></div>
        </div>

        <div class="mb-3">
            <label for="metodo_pago" class="form-label">Método de pago</label>
            <select class="form-select <?= isset($errores['metodo_pago']) ? 'is-invalid' : '' ?>" name="metodo_pago" required>
                <option value="">Seleccionar...</option>
                <option value="tarjeta" <?= ($datos['metodo_pago'] ?? '') === 'tarjeta' ? 'selected' : '' ?>>Tarjeta</option>
                <option value="paypal" <?= ($datos['metodo_pago'] ?? '') === 'paypal' ? 'selected' : '' ?>>PayPal</option>
                <option value="transferencia" <?= ($datos['metodo_pago'] ?? '') === 'transferencia' ? 'selected' : '' ?>>Transferencia</option>
            </select>
            <div class="invalid-feedback"><?= $errores['metodo_pago'] ?? '' ?></div>
        </div>

        <button type="submit" class="btn btn-success">Confirmar pedido</button>
    </form>
</div>

<?php include "includes/footer.php"; ?>
