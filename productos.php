<?php
include 'includes/header.php';
require 'includes/db.php';

$query = "SELECT * FROM productos";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>UrbanWear - Productos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
            background-color: #f5f5f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container#productos {
            padding-top: 40px;
            padding-bottom: 60px;
        }
        h2 {
            letter-spacing: 2px;
            font-weight: 700;
            margin-bottom: 40px;
            text-align: center;
            color: #333;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
        }
        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.2);
        }
        .card-img-top {
            height: 260px;
            object-fit: cover;
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
        }
        .card-body {
            text-align: center;
            padding: 1.5rem;
        }
        .card-title {
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 0.7rem;
            color: #333;
        }
        .card-text {
            font-size: 1rem;
            color: #666;
            margin-bottom: 1.3rem;
            min-height: 3.2rem;
        }
        .btn-custom {
            background-color: #ff6600;
            color: white;
            font-weight: 600;
            padding: 12px 30px;
            border-radius: 30px;
            transition: background-color 0.3s ease;
            box-shadow: 0 4px 10px rgba(255,102,0,0.4);
            text-transform: uppercase;
            letter-spacing: 1px;
            text-decoration: none;
            display: inline-block;
        }
        .btn-custom:hover {
            background-color: #e65c00;
            box-shadow: 0 6px 14px rgba(230,92,0,0.6);
            color: white;
            text-decoration: none;
        }
        @media (max-width: 768px) {
            .card-img-top {
                height: 220px;
            }
        }
    </style>
</head>
<body>

<div class="container" id="productos">
    <h2>Productos UrbanWear</h2>
    <div class="row g-4">
        <?php while ($producto = mysqli_fetch_assoc($result)): ?>
        <div class="col-md-4">
            <div class="card">
                <img src="<?= htmlspecialchars($producto['imagen']) ?>" class="card-img-top" alt="<?= htmlspecialchars($producto['nombre']) ?>" />
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($producto['nombre']) ?></h5>
                    <p class="card-text"><?= htmlspecialchars($producto['descripcion']) ?></p>
                    <p class="card-text fw-bold"><?= number_format($producto['precio'], 2) ?> €</p>
                    <a href="detalle_producto.php?id=<?= $producto['id'] ?>" class="btn btn-custom">Ver detalles y añadir</a>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>