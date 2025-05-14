<?php 
include "includes/header.php";

// Consultar todos los productos
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';
if (!empty($busqueda)) {
    $busqueda = mysqli_real_escape_string($conn, $busqueda);
    $query = "SELECT * FROM productos WHERE nombre LIKE '%$busqueda%'";
} else {
    $query = "SELECT * FROM productos";
}

$resultado = mysqli_query($conn, $query);

if (!$resultado) {
    echo "<div class='container mt-4 alert alert-danger'>Error al obtener los productos: " . mysqli_error($conn) . "</div>";
    exit();
}
?>

<style>
    .producto-card {
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .producto-card img {
        height: 250px;
        object-fit: cover;
    }

    .producto-card .card-body {
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
</style>

<div class="container mt-5 mb-5">
    <h2 class="mb-4">Productos</h2>
    <form method="GET" class="mb-4">
        <div class="input-group">
            <input type="text" name="busqueda" class="form-control" placeholder="Buscar productos..." value="<?php echo isset($_GET['busqueda']) ? htmlspecialchars($_GET['busqueda']) : ''; ?>">
            <button class="btn btn-outline-primary" type="submit">Buscar</button>
        </div>
    </form>
    <div class="row">
        <?php while ($producto = mysqli_fetch_assoc($resultado)) { ?>
            <div class="col-sm-6 col-md-4 mb-4 d-flex">
                <div class="card producto-card w-100">
                    <a href="detalle_producto.php?id=<?php echo $producto['id']; ?>">
                        <img src="<?php echo $producto['imagen']; ?>" class="card-img-top" alt="<?php echo $producto['nombre']; ?>">
                    </a>
                    <div class="card-body text-center">
                        <h5 class="card-title"><?php echo $producto['nombre']; ?></h5>
                        <p class="card-text"><?php echo $producto['descripcion']; ?></p>
                        <p class="card-text fw-bold">€<?php echo number_format($producto['precio'], 2); ?></p>
                        <a href="detalle_producto.php?id=<?php echo $producto['id']; ?>" class="btn btn-primary">Ver detalles</a>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</div>

<?php include "includes/footer.php"; ?>
