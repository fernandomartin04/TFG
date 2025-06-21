<?php
session_start();
require "includes/db.php";

if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 3) {
    header("Location: index.php");
    exit();
}

// Procesar actualización de estado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pedido_id'], $_POST['nuevo_estado'])) {
    $pedido_id = intval($_POST['pedido_id']);
    $nuevo_estado = $_POST['nuevo_estado'];
    $usuario_id = $_SESSION['usuario_id'];

    // Actualizar estado en tabla pedidos
    $stmt = $conn->prepare("UPDATE pedidos SET estado = ? WHERE id = ?");
    $stmt->bind_param("si", $nuevo_estado, $pedido_id);
    $stmt->execute();

    // Registrar cambio en historial
    $stmtHist = $conn->prepare("INSERT INTO pedidos_estados (pedido_id, estado, usuario_id) VALUES (?, ?, ?)");
    $stmtHist->bind_param("isi", $pedido_id, $nuevo_estado, $usuario_id);
    $stmtHist->execute();

    // Redirigir con mensaje para evitar resubmit
    header("Location: admin_pedidos.php?msg=estado_actualizado");
    exit();
}

// Obtener lista de pedidos con info usuario
$query = "SELECT p.id, p.usuario_id, p.nombre, p.direccion, p.telefono, p.metodo_pago, p.fecha, p.estado, u.nombre AS cliente
          FROM pedidos p
          JOIN usuarios u ON p.usuario_id = u.id
          ORDER BY p.fecha DESC";
$result = $conn->query($query);

$estados_posibles = ['Pendiente', 'Procesando', 'Enviado', 'Entregado', 'Cancelado'];

include "includes/header.php";
?>

<div class="container mt-5">
    <h2>Gestión de pedidos</h2>

    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'estado_actualizado'): ?>
        <div class="alert alert-success">Estado actualizado correctamente.</div>
    <?php endif; ?>

    <table class="table table-striped table-bordered">
        <thead class="table-dark">
            <tr>
                <th>ID Pedido</th>
                <th>Cliente</th>
                <th>Nombre destinatario</th>
                <th>Fecha</th>
                <th>Método pago</th>
                <th>Estado actual</th>
                <th>Cambiar estado</th>
                <th>Acciones</th> <!-- NUEVA COLUMNA -->
            </tr>
        </thead>
        <tbody>
            <?php while ($pedido = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $pedido['id'] ?></td>
                    <td><?= htmlspecialchars($pedido['cliente']) ?></td>
                    <td><?= htmlspecialchars($pedido['nombre']) ?></td>
                    <td><?= $pedido['fecha'] ?></td>
                    <td><?= htmlspecialchars($pedido['metodo_pago']) ?></td>
                    <td><?= htmlspecialchars($pedido['estado']) ?></td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="pedido_id" value="<?= $pedido['id'] ?>">
                            <select name="nuevo_estado" class="form-select form-select-sm" style="max-width: 180px; display:inline-block; margin-right: 5px;">
                                <?php foreach ($estados_posibles as $estado): ?>
                                    <option value="<?= $estado ?>" <?= $pedido['estado'] === $estado ? 'selected' : '' ?>>
                                        <?= $estado ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="btn btn-sm btn-primary">Actualizar</button>
                        </form>
                    </td>
                    <td>
                        <a href="ver_pedido.php?id=<?= $pedido['id'] ?>" class="btn btn-sm btn-info">Ver pedido</a> <!-- NUEVO BOTÓN -->
                    </td>
                </tr>
            <?php endwhile; ?>
            <?php if ($result->num_rows === 0): ?>
                <tr>
                    <td colspan="8" class="text-center">No hay pedidos.</td> <!-- AJUSTADO colspan -->
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include "includes/footer.php"; ?>
