<?php
ob_start();
session_start();
require "includes/db.php";

$usuario_id = $_SESSION['usuario_id'] ?? 0;

if (!$usuario_id) {
    header("Location: login.php");
    exit();
}

// Procesar actualización de talla
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_talla'])) {
    $producto_id = isset($_POST['producto_id']) ? intval($_POST['producto_id']) : 0;
    $nueva_talla = $_POST['talla'] ?? '';

    $tallas_validas = ['XS', 'S', 'M', 'L', 'XL'];

    if ($producto_id > 0 && in_array($nueva_talla, $tallas_validas)) {
        $stmt = $conn->prepare("UPDATE carritos SET talla = ? WHERE usuario_id = ? AND producto_id = ? LIMIT 1");
        $stmt->bind_param("sii", $nueva_talla, $usuario_id, $producto_id);
        $stmt->execute();
        $stmt->close();

        header("Location: carrito.php", true, 303);
        exit();
    } else {
        echo "<div class='alert alert-danger'>Datos inválidos para actualizar talla.</div>";
    }
}

include "includes/header.php";

$query = "SELECT c.producto_id, c.cantidad, c.talla, p.nombre, p.precio, p.imagen
          FROM carritos c
          JOIN productos p ON c.producto_id = p.id
          WHERE c.usuario_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

$total = 0;
?>

<div class="container mt-5">
    <h2>Carrito de la compra</h2>

    <?php if (!$result || $result->num_rows === 0): ?>
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
                <?php while ($row = $result->fetch_assoc()):
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
                        <form method="POST" action="carrito.php" class="d-flex align-items-center gap-2">
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
                            <button type="submit" name="update_talla" class="btn btn-sm btn-primary">Actualizar</button>
                        </form>
                    </td>
                    <td>
                        <a href="eliminar_del_carrito.php?id=<?= $row['producto_id'] ?>"
                           onclick="return confirm('¿Quieres eliminar este producto del carrito?');"
                           class="btn btn-danger btn-sm">Eliminar</a>
                    </td>
                </tr>
                <?php endwhile; ?>
                <tr>
                    <td colspan="4" class="text-end"><strong>Total:</strong></td>
                    <td colspan="3"><strong><?= number_format($total, 2) ?> €</strong></td>
                </tr>
            </tbody>
        </table>

        <a href="confirmar_pedido.php" class="btn btn-success">Confirmar pedido</a>
    <?php endif; ?>
</div>

<?php include "includes/footer.php"; ?>
<?php ob_end_flush(); ?>
