<?php
session_start();
require "includes/db.php";
include "includes/header.php";

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['id'];

// Obtener todos los pedidos del usuario
$query_pedidos = "SELECT * FROM pedidos WHERE usuario_id = ? ORDER BY fecha DESC";
$stmt = $conn->prepare($query_pedidos);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result_pedidos = $stmt->get_result();
?>

<div class="container mt-5 mb-5">
    <h2 class="mb-4">📦 Historial de pedidos</h2>

    <?php if ($result_pedidos->num_rows === 0): ?>
        <div class="alert alert-info text-center">No has realizado ningún pedido aún.</div>
    <?php else: ?>
        <?php while ($pedido = $result_pedidos->fetch_assoc()): ?>
            <div class="card mb-4">
                <div class="card-header bg-dark text-white">
                    <strong>Pedido #<?= $pedido['id'] ?></strong> - <?= $pedido['fecha'] ?> - Método: <?= ucfirst($pedido['metodo_pago']) ?>
                </div>
                <div class="card-body">
                    <p><strong>Nombre:</strong> <?= $pedido['nombre'] ?></p>
                    <p><strong>Dirección:</strong> <?= $pedido['direccion'] ?></p>
                    <p><strong>Teléfono:</strong> <?= $pedido['telefono'] ?></p>

                    <!-- Detalles del pedido -->
                    <table class="table table-bordered text-center mt-3">
                        <thead class="table-light">
                            <tr>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Precio unitario</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $pedido_id = $pedido['id'];
                            $query_detalle = "SELECT pd.cantidad, pd.precio_unitario, p.nombre 
                                              FROM pedidos_detalle pd 
                                              JOIN productos p ON pd.producto_id = p.id 
                                              WHERE pd.pedido_id = ?";
                            $stmt_detalle = $conn->prepare($query_detalle);
                            $stmt_detalle->bind_param("i", $pedido_id);
                            $stmt_detalle->execute();
                            $result_detalle = $stmt_detalle->get_result();

                            $total = 0;
                            while ($detalle = $result_detalle->fetch_assoc()):
                                $subtotal = $detalle['precio_unitario'] * $detalle['cantidad'];
                                $total += $subtotal;
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($detalle['nombre']) ?></td>
                                    <td><?= $detalle['cantidad'] ?></td>
                                    <td><?= number_format($detalle['precio_unitario'], 2) ?> €</td>
                                    <td><?= number_format($subtotal, 2) ?> €</td>
                                </tr>
                            <?php endwhile; ?>
                            <tr class="table-secondary">
                                <td colspan="3"><strong>Total</strong></td>
                                <td><strong><?= number_format($total, 2) ?> €</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endwhile; ?>
    <?php endif; ?>
</div>

<?php include "includes/footer.php"; ?>
