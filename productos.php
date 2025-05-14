<?php 
include "includes/header.php"; // Asegúrate de incluir el header con la conexión a DB

// Consultar todos los productos
$query = "SELECT * FROM productos";
$resultado = mysqli_query($conn, $query);

if (!$resultado) {
    echo "<div class='container mt-4 alert alert-danger'>Error al obtener los productos: " . mysqli_error($conn) . "</div>";
    exit();
}
?>

<div class="container mt-5">
    <h2>Productos</h2>
    <div class="row">
        <?php while ($producto = mysqli_fetch_assoc($resultado)) { ?>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <img src="<?php echo $producto['imagen']; ?>" class="card-img-top" alt="<?php echo $producto['nombre']; ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $producto['nombre']; ?></h5>
                        <p class="card-text"><?php echo $producto['descripcion']; ?></p>
                        <p class="card-text"><strong>€<?php echo number_format($producto['precio'], 2); ?></strong></p>
                        <a href="#" class="btn btn-primary">Comprar</a>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</div>

<?php include "includes/footer.php"; ?>
