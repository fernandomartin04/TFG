<?php
ob_start();
session_start();
require "includes/db.php";

$usuario_id = $_SESSION['usuario_id'] ?? null;

// Obtener productos del carrito según el estado de sesión
$carrito_items = [];

if ($usuario_id) {
    $query = "SELECT c.producto_id, c.cantidad, c.talla, p.nombre, p.precio, p.imagen
              FROM carritos c
              JOIN productos p ON c.producto_id = p.id
              WHERE c.usuario_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $carrito_items = $result->fetch_all(MYSQLI_ASSOC);
} elseif (!empty($_SESSION['carrito'])) {
    foreach ($_SESSION['carrito'] as $item) {
        $producto_id = $item['producto_id'];
        $stmt = $conn->prepare("SELECT nombre, precio, imagen FROM productos WHERE id = ?");
        $stmt->bind_param("i", $producto_id);
        $stmt->execute();
        $producto = $stmt->get_result()->fetch_assoc();
        if ($producto) {
            $carrito_items[] = [
                'producto_id' => $producto_id,
                'cantidad'    => $item['cantidad'],
                'talla'       => $item['talla'],
                'nombre'      => $producto['nombre'],
                'precio'      => $producto['precio'],
                'imagen'      => $producto['imagen']
            ];
        }
    }
}

include "includes/header.php";

$total = 0;
?>

<div class="container mt-5">
    <h2>Carrito de la compra</h2>

    <?php if (!empty($_SESSION['error_carrito'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error_carrito'] ?></div>
        <?php unset($_SESSION['error_carrito']); ?>
    <?php endif; ?>

    <?php if (empty($carrito_items)): ?>
        <p>Tu carrito está vacío.</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Talla</th>
                    <th>Precio unitario</th>
                    <th>Subtotal</th>
                    <th>Actualizar talla</th>
                    <th>Eliminar</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($carrito_items as $row):
                    $subtotal = $row['cantidad'] * $row['precio'];
                    $total += $subtotal;
                ?>
                <tr>
                    <td>
                        <img src="<?= htmlspecialchars($row['imagen']) ?>" style="width: 60px; height: 60px; object-fit: cover;">
                        <?= htmlspecialchars($row['nombre']) ?>
                    </td>
                    <td><?= $row['cantidad'] ?></td>
                    <td><?= htmlspecialchars($row['talla']) ?></td>
                    <td><?= number_format($row['precio'], 2) ?> €</td>
                    <td><?= number_format($subtotal, 2) ?> €</td>
                    <td>
                        <form method="POST" action="actualizar_talla.php" class="d-flex align-items-center gap-2">
                            <input type="hidden" name="producto_id" value="<?= $row['producto_id'] ?>">
                            <select name="talla" class="form-select form-select-sm" required>
                                <?php
                                $tallas = ['XS', 'S', 'M', 'L', 'XL'];
                                foreach ($tallas as $talla_opcion) {
                                    $selected = ($row['talla'] === $talla_opcion) ? 'selected' : '';
                                    echo "<option value=\"$talla_opcion\" $selected>$talla_opcion</option>";
                                }
                                ?>
                            </select>
                            <button type="submit" class="btn btn-sm btn-primary">Actualizar</button>
                        </form>
                    </td>
                    <td>
                        <a href="eliminar_del_carrito.php?id=<?= $row['producto_id'] ?>&talla=<?= urlencode($row['talla']) ?>"
                           onclick="return confirm('¿Quieres eliminar este producto del carrito?');"
                           class="btn btn-danger btn-sm">Eliminar</a>
                    </td>
                </tr>
                <?php endforeach; ?>

                <?php
                $descuento = 0;
                if (isset($_SESSION['cupon_aplicado'])) {
                    $cupon = $_SESSION['cupon_aplicado'];
                    if ($cupon['tipo'] === 'porcentaje') {
                        $descuento = ($total * $cupon['valor']) / 100;
                    } elseif ($cupon['tipo'] === 'cantidad') {
                        $descuento = $cupon['valor'];
                    }
                    if ($descuento > $total) {
                        $descuento = $total;
                    }
                }
                $total_final = $total - $descuento;
                ?>

                <tr>
                    <td colspan="4" class="text-end"><strong>Total sin descuento:</strong></td>
                    <td colspan="3"><strong><?= number_format($total, 2) ?> €</strong></td>
                </tr>

                <?php if ($descuento > 0): ?>
                <tr>
                    <td colspan="4" class="text-end text-success"><strong>Descuento aplicado (<?= htmlspecialchars($cupon['codigo']) ?>):</strong></td>
                    <td colspan="3" class="text-success"><strong>-<?= number_format($descuento, 2) ?> €</strong></td>
                </tr>
                <tr>
                    <td colspan="4" class="text-end"><strong>Total a pagar:</strong></td>
                    <td colspan="3"><strong><?= number_format($total_final, 2) ?> €</strong></td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <form method="POST" action="aplicar_cupon.php" class="my-4">
            <div class="input-group" style="max-width: 400px;">
                <input type="text" name="codigo_cupon" class="form-control" placeholder="Introduce tu código de cupón" required>
                <button class="btn btn-primary" type="submit">Aplicar cupón</button>
            </div>
        </form>

        <?php if (isset($_SESSION['mensaje_cupon'])): ?>
            <div class="alert alert-info"><?= $_SESSION['mensaje_cupon'] ?></div>
            <?php unset($_SESSION['mensaje_cupon']); ?>
        <?php endif; ?>

        <a href="confirmar_pedido.php" class="btn btn-success">Confirmar pedido</a>
    <?php endif; ?>
</div>

<?php include "includes/footer.php"; ?>
<?php ob_end_flush(); ?>
