<?php
include "includes/header.php";
require "includes/db.php";

// OPCIONAL: Muestra errores MySQLi para depuración
// mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (!isset($_SESSION['rol_id']) || ($_SESSION['rol_id'] != 2 && $_SESSION['rol_id'] != 3)) {
    header("Location: login.php");
    exit();
}

// Eliminar producto
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    $stmt = $conn->prepare("SELECT imagen FROM productos WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($imagen);
    $stmt->fetch();
    $stmt->close();

    if ($imagen && file_exists("img/" . $imagen)) {
        unlink("img/" . $imagen);
    }

    // Primero eliminar el stock relacionado
    $conn->query("DELETE FROM stock_productos_tallas WHERE producto_id = $id");

    // Luego eliminar el producto
    $conn->query("DELETE FROM productos WHERE id = $id");

    echo "<div class='alert alert-success'>Producto eliminado.</div>";
}

// Guardar cambios en producto
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["editar_producto"])) {
    $id = intval($_POST["id"]);
    $nombre = trim($_POST["nombre"]);
    $descripcion = trim($_POST["descripcion"]);
    $precio = floatval($_POST["precio"]);

    $stmt = $conn->prepare("UPDATE productos SET nombre = ?, descripcion = ?, precio = ? WHERE id = ?");
    $stmt->bind_param("ssdi", $nombre, $descripcion, $precio, $id);
    $stmt->execute();
    echo "<div class='alert alert-success'>Producto actualizado correctamente.</div>";
}

// Guardar stock por tallas
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["guardar_stock"])) {
    $producto_id = intval($_POST["producto_id"]);
    $tallas = ['XS', 'S', 'M', 'L', 'XL'];

    foreach ($tallas as $talla) {
        $stock = max(0, intval($_POST["stock_$talla"]));
        $stmt = $conn->prepare("INSERT INTO stock_productos_tallas (producto_id, talla, stock) 
            VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE stock = ?");
        $stmt->bind_param("isii", $producto_id, $talla, $stock, $stock);
        $stmt->execute();
    }
    echo "<div class='alert alert-success'>Stock actualizado correctamente.</div>";
}

// Obtener productos
$productos = $conn->query("SELECT * FROM productos ORDER BY fecha_creacion DESC")->fetch_all(MYSQLI_ASSOC);
?>

<div class="container mt-4">
    <h2>Gestión de productos</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Imagen</th>
                <th>Nombre</th>
                <th>Precio</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($productos as $p): ?>
            <tr>
                <td><img src="<?= $p['imagen'] ?>" width="80"></td>
                <td><?= htmlspecialchars($p['nombre']) ?></td>
                <td><?= number_format($p['precio'], 2) ?> €</td>
                <td>
                    <a href="?editar=<?= $p['id'] ?>" class="btn btn-sm btn-primary">Editar</a>
                    <a href="?stock=<?= $p['id'] ?>" class="btn btn-sm btn-warning">Stock</a>
                    <a href="?eliminar=<?= $p['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar este producto?')">Eliminar</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php if (isset($_GET['editar'])):
        $id = intval($_GET['editar']);
        $stmt = $conn->prepare("SELECT * FROM productos WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $producto = $stmt->get_result()->fetch_assoc();
    ?>
    <div class="mt-5 p-4 border rounded bg-light">
        <h4>Editar producto</h4>
        <form method="POST">
            <input type="hidden" name="id" value="<?= $producto['id'] ?>">
            <div class="mb-2">
                <label class="form-label">Nombre</label>
                <input type="text" class="form-control" name="nombre" value="<?= htmlspecialchars($producto['nombre']) ?>" required>
            </div>
            <div class="mb-2">
                <label class="form-label">Descripción</label>
                <textarea class="form-control" name="descripcion" rows="3"><?= htmlspecialchars($producto['descripcion']) ?></textarea>
            </div>
            <div class="mb-2">
                <label class="form-label">Precio (€)</label>
                <input type="number" class="form-control" step="0.01" name="precio" value="<?= $producto['precio'] ?>" required>
            </div>
            <button class="btn btn-success" name="editar_producto" type="submit">Guardar cambios</button>
        </form>
    </div>
    <?php endif; ?>

    <?php if (isset($_GET['stock'])):
        $id = intval($_GET['stock']);
        $tallas = ['XS', 'S', 'M', 'L', 'XL'];
        $stock_actual = [];
        $res = $conn->prepare("SELECT talla, stock FROM stock_productos_tallas WHERE producto_id = ?");
        $res->bind_param("i", $id);
        $res->execute();
        $result = $res->get_result();
        while ($fila = $result->fetch_assoc()) {
            $stock_actual[$fila['talla']] = $fila['stock'];
        }
    ?>
    <div class="mt-5 p-4 border rounded bg-light">
        <h4>Editar stock por talla</h4>
        <form method="POST">
            <input type="hidden" name="producto_id" value="<?= $id ?>">
            <div class="row">
                <?php foreach ($tallas as $talla): ?>
                <div class="col-md-2 mb-2">
                    <label class="form-label"><?= $talla ?></label>
                    <input type="number" class="form-control" name="stock_<?= $talla ?>" value="<?= $stock_actual[$talla] ?? 0 ?>">
                </div>
                <?php endforeach; ?>
            </div>
            <button class="btn btn-success" name="guardar_stock" type="submit">Actualizar stock</button>
        </form>
    </div>
    <?php endif; ?>

</div>

<?php include "includes/footer.php"; ?>
