<?php
session_start();
include "includes/header.php";
require "includes/db.php";

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['id'];
$query = "SELECT c.producto_id, c.cantidad, p.nombre, p.precio
          FROM carritos c
          JOIN productos p ON c.producto_id = p.id
          WHERE c.usuario_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: carrito.php");
    exit();
}
?>

<div class="container mt-5 mb-5">
    <h2 class="mb-4">Datos de envío y pago</h2>
    <form method="POST" action="procesar_pedido.php">
        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre completo</label>
            <input type="text" class="form-control" name="nombre" required>
        </div>
        <div class="mb-3">
            <label for="direccion" class="form-label">Dirección de envío</label>
            <input type="text" class="form-control" name="direccion" required>
        </div>
        <div class="mb-3">
            <label for="telefono" class="form-label">Teléfono de contacto</label>
            <input type="text" class="form-control" name="telefono" required>
        </div>
        <div class="mb-3">
            <label for="metodo_pago" class="form-label">Método de pago</label>
            <select name="metodo_pago" class="form-select" required>
                <option value="">Selecciona una opción</option>
                <option value="tarjeta">Tarjeta</option>
                <option value="paypal">PayPal</option>
                <option value="transferencia">Transferencia bancaria</option>
            </select>
        </div>
        <button type="submit" class="btn btn-success">Finalizar pedido</button>
    </form>
</div>

<?php include "includes/footer.php"; ?>
