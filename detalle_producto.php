<?php 
include "includes/header.php"; 
require "includes/db.php";

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
                    <select name="talla" id="talla" class="form-select" required>
                        <option value="">-- Elige una talla --</option>
                        <option value="XS">XS</option>
                        <option value="S">S</option>
                        <option value="M">M</option>
                        <option value="L">L</option>
                        <option value="XL">XL</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Añadir al carrito</button>
            </form>
        </div>
    </div>
</div>

<?php include "includes/footer.php"; ?>
