<?php
session_start();

// Si no hay datos del formulario, redirigimos
if (!isset($_SESSION['datos_formulario'])) {
    header("Location: confirmar_pedido.php");
    exit();
}

$datos = $_SESSION['datos_formulario'];
?>

<?php include "includes/header.php"; ?>

<div class="container mt-5 mb-5">
    <h2>Validar Pago con Tarjeta</h2>
    <p class="mb-4">Introduce tus datos para procesar el pago de forma segura.</p>

    <form action="finalizar_pago.php" method="POST" class="needs-validation" novalidate>
        <div class="mb-3">
            <label for="titular" class="form-label">Titular de la tarjeta</label>
            <input type="text" class="form-control" id="titular" name="titular" required>
            <div class="invalid-feedback">Introduce el nombre del titular.</div>
        </div>

        <div class="mb-3">
            <label for="numero" class="form-label">Número de tarjeta</label>
            <input type="text" class="form-control" id="numero" name="numero" pattern="\d{16}" maxlength="16" required>
            <div class="invalid-feedback">Debe contener 16 dígitos numéricos.</div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="caducidad" class="form-label">Fecha de caducidad</label>
                <input type="text" class="form-control" id="caducidad" name="caducidad" placeholder="MM/AA" pattern="^(0[1-9]|1[0-2])\/\d{2}$" required>
                <div class="invalid-feedback">Formato válido: MM/AA</div>
            </div>
            <div class="col-md-6 mb-3">
                <label for="cvv" class="form-label">CVV</label>
                <input type="text" class="form-control" id="cvv" name="cvv" pattern="\d{3,4}" maxlength="4" required>
                <div class="invalid-feedback">Código CVV inválido.</div>
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-success w-100">Pagar ahora</button>
        </div>
    </form>
</div>

<script>
// Validación Bootstrap
(function () {
    'use strict';
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
})();
</script>

<?php include "includes/footer.php"; ?>
