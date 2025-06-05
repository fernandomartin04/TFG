<?php
session_start();
require "includes/db.php";
include "includes/header.php";

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['id'];

$query = "SELECT c.id AS carrito_id, c.producto_id, c.cantidad, c.talla, p.nombre, p.precio
          FROM carritos c
          JOIN productos p ON c.producto_id = p.id
          WHERE c.usuario_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="container mt-5">
    <h2 class="text-center mb-4">🛒 Tu carrito de compras</h2>

    <?php if ($result->num_rows === 0): ?>
        <div class="alert alert-info text-center">Tu carrito está vacío.</div>
    <?php else: ?>
        <table class="table table-bordered text-center">
            <thead class="table-dark">
                <tr>
                    <th>Producto</th>
                    <th>Talla</th>
                    <th>Precio</th>
                    <th>Cantidad</th>
                    <th>Subtotal</th>
                    <th>Eliminar</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $total = 0;
                while ($row = $result->fetch_assoc()) {
                    $subtotal = $row['precio'] * $row['cantidad'];
                    $total += $subtotal;
                    echo "<tr>";

                    echo "<td>" . htmlspecialchars($row['nombre']) . "</td>";

                    echo "<td>
                        <form method='POST' action='actualizar_talla.php' class='d-flex justify-content-center'>
                            <input type='hidden' name='carrito_id' value='{$row['carrito_id']}'>
                            <select name='talla' class='form-select form-select-sm me-1' style='width:auto'>
                                <option value=''>--</option>
                                <option value='XS' " . ($row['talla'] === 'XS' ? 'selected' : '') . ">XS</option>
                                <option value='S' "  . ($row['talla'] === 'S' ? 'selected' : '') . ">S</option>
                                <option value='M' "  . ($row['talla'] === 'M' ? 'selected' : '') . ">M</option>
                                <option value='L' "  . ($row['talla'] === 'L' ? 'selected' : '') . ">L</option>
                                <option value='XL' " . ($row['talla'] === 'XL' ? 'selected' : '') . ">XL</option>
                            </select>
                            <button class='btn btn-sm btn-outline-secondary' type='submit'>💾</button>
                        </form>
                    </td>";

                    echo "<td>" . number_format($row['precio'], 2) . " €</td>";
                    echo "<td>{$row['cantidad']}</td>";
                    echo "<td>" . number_format($subtotal, 2) . " €</td>";

                    $url = "eliminar_del_carrito.php?id={$row['producto_id']}";
                    if (!empty($row['talla'])) {
                        $url .= "&talla=" . urlencode($row['talla']);
                    }

                    echo "<td><a href='$url' class='btn btn-danger btn-sm'>Eliminar</a></td>";
                    echo "</tr>";
                }
                ?>
                <tr class="table-secondary">
                    <td colspan="4"><strong>Total</strong></td>
                    <td colspan="2"><strong><?= number_format($total, 2) ?> €</strong></td>
                </tr>
            </tbody>
        </table>
        <div class="text-center">
            <a href="confirmar_pedido.php" class="btn btn-success">Confirmar pedido</a>
        </div>
    <?php endif; ?>
</div>

<?php include "includes/footer.php"; ?>
