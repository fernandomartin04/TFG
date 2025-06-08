<?php
include "includes/header.php";
require "includes/db.php";

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Validación del parámetro ID
if (!isset($_GET['id'])) {
    echo "<div class='container mt-4 alert alert-danger'>Producto no especificado.</div>";
    include "includes/footer.php";
    exit();
}

$id = (int) $_GET['id'];
$query = "SELECT * FROM productos WHERE id = $id";
$resultado = mysqli_query($conn, $query);

if ($resultado && mysqli_num_rows($resultado) > 0) {
    $producto = mysqli_fetch_assoc($resultado);
} else {
    echo "<div class='container mt-4 alert alert-danger'>Producto no encontrado.</div>";
    include "includes/footer.php";
    exit();
}

// Obtener stock por tallas
$sqlStock = $conn->prepare("SELECT talla, stock FROM stock_productos_tallas WHERE producto_id = ?");
$sqlStock->bind_param("i", $id);
$sqlStock->execute();
$sqlStock->store_result();
$sqlStock->bind_result($talla, $stock);

$stocks = [];
while ($sqlStock->fetch()) {
    $stocks[$talla] = $stock;
}
$sqlStock->close();
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-6">
            <img src="<?php echo htmlspecialchars($producto['imagen']); ?>" alt="Imagen del producto" class="img-fluid">
        </div>
        <div class="col-md-6">
            <h2><?php echo htmlspecialchars($producto['nombre']); ?></h2>
            <p><?php echo htmlspecialchars($producto['descripcion']); ?></p>
            <h4><?php echo number_format($producto['precio'], 2); ?> €</h4>

            <form method="POST" action="agregar_al_carrito.php?id=<?= $producto['id'] ?>">
                <div class="mb-3">
                    <label for="talla" class="form-label">Selecciona tu talla:</label>
                    <select name="talla" id="talla" class="form-select" required onchange="actualizarCantidadMaxima()">
                        <option value="">-- Elige una talla --</option>
                        <?php foreach ($stocks as $t => $s): ?>
                            <option value="<?= $t ?>"><?= $t ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="cantidad" class="form-label">Cantidad:</label>
                    <input type="number" name="cantidad" id="cantidad" class="form-control" value="1" min="1" oninput="validarCantidad()" required>
                </div>

                <!-- Banner de alerta si excede stock -->
                <div id="banner_stock" class="alert alert-danger mt-2" role="alert" style="display: none;">
                    Has seleccionado una cantidad superior al stock disponible para esta talla.
                </div>

                <button type="submit" class="btn btn-primary">Añadir al carrito</button>
            </form>

            <?php if (isset($_SESSION['rol_id']) && ($_SESSION['rol_id'] == 2 || $_SESSION['rol_id'] == 3)) : ?>
                <div class="mt-3">
                    <h5>Stock disponible (solo admins/vendedores):</h5>
                    <ul>
                        <?php foreach ($stocks as $t => $s): ?>
                            <li><?= $t ?>: <?= $s ?> unidades</li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    const stocks = <?= json_encode($stocks) ?>;

    function actualizarCantidadMaxima() {
        const talla = document.getElementById("talla").value;
        const cantidad = document.getElementById("cantidad");
        const banner = document.getElementById("banner_stock");

        if (stocks[talla]) {
            cantidad.max = stocks[talla];
            cantidad.value = 1;
        } else {
            cantidad.max = 1;
            cantidad.value = 1;
        }

        banner.style.display = "none";
    }

    function validarCantidad() {
        const talla = document.getElementById("talla").value;
        const cantidad = document.getElementById("cantidad");
        const banner = document.getElementById("banner_stock");

        if (!talla || !stocks[talla]) {
            banner.style.display = "none";
            return;
        }

        const maxStock = stocks[talla];

        if (parseInt(cantidad.value) > maxStock) {
            banner.style.display = "block";
        } else {
            banner.style.display = "none";
        }
    }
</script>

<?php include "includes/footer.php"; ?>
