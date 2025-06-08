<?php
session_start();
require "includes/db.php";
include "includes/header.php";

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

$query_pedidos = "SELECT id, usuario_id, nombre, direccion, telefono, metodo_pago, fecha, estado, cupon_codigo, cupon_descuento, total 
                  FROM pedidos WHERE usuario_id = ? ORDER BY fecha DESC";
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
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Pedido #<?= $pedido['id'] ?></strong> - <?= $pedido['fecha'] ?> - Método: <?= ucfirst($pedido['metodo_pago']) ?>
                        <br>
                        <small>Estado: <span style="color: yellow; font-weight: bold;">
                            <?= htmlspecialchars($pedido['estado'] ?? 'Pendiente') ?>
                        </span></small>
                    </div>
                    <button onclick="descargarFactura(<?= $pedido['id'] ?>)" class="btn btn-sm btn-outline-light">
                        📄 Descargar factura
                    </button>
                </div>
                <div class="card-body">
                    <p><strong>Nombre:</strong> <?= htmlspecialchars($pedido['nombre']) ?></p>
                    <p><strong>Dirección:</strong> <?= htmlspecialchars($pedido['direccion']) ?></p>
                    <p><strong>Teléfono:</strong> <?= htmlspecialchars($pedido['telefono']) ?></p>

                    <table class="table table-bordered text-center mt-3">
                        <thead class="table-light">
                            <tr>
                                <th>Producto</th>
                                <th>Talla</th>
                                <th>Cantidad</th>
                                <th>Precio unitario</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $pedido_id = $pedido['id'];
                            $query_detalle = "SELECT pd.cantidad, pd.talla, pd.precio_unitario, pd.subtotal, p.nombre 
                                              FROM pedidos_detalle pd 
                                              JOIN productos p ON pd.producto_id = p.id 
                                              WHERE pd.pedido_id = ?";
                            $stmt_detalle = $conn->prepare($query_detalle);
                            $stmt_detalle->bind_param("i", $pedido_id);
                            $stmt_detalle->execute();
                            $result_detalle = $stmt_detalle->get_result();

                            $subtotal_general = 0;
                            while ($detalle = $result_detalle->fetch_assoc()):
                                $subtotal = $detalle['subtotal'];
                                $subtotal_general += $subtotal;
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($detalle['nombre']) ?></td>
                                    <td><?= htmlspecialchars($detalle['talla']) ?></td>
                                    <td><?= $detalle['cantidad'] ?></td>
                                    <td><?= number_format($detalle['precio_unitario'], 2) ?> €</td>
                                    <td><?= number_format($subtotal, 2) ?> €</td>
                                </tr>
                            <?php endwhile; ?>

                            <?php if ($pedido['cupon_descuento'] > 0): ?>
                                <tr>
                                    <td colspan="4" class="text-end"><strong>Cupón aplicado:</strong> <?= htmlspecialchars($pedido['cupon_codigo']) ?></td>
                                    <td>-<?= number_format($pedido['cupon_descuento'], 2) ?> €</td>
                                </tr>
                            <?php endif; ?>

                            <tr class="table-secondary">
                                <td colspan="4" class="text-end"><strong>Total final:</strong></td>
                                <td><strong><?= number_format($pedido['total'], 2) ?> €</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endwhile; ?>
    <?php endif; ?>
</div>

<script>
function descargarFactura(pedidoId) {
    const url = `descargar_factura.php?pedido_id=${pedidoId}`;
    const link = document.createElement('a');
    link.href = url;
    link.download = `Factura_Pedido_${pedidoId}.pdf`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>

<?php include "includes/footer.php"; ?>
