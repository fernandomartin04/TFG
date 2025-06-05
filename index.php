<?php
session_start(); 
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UrbanWear - Inicio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f5f5;
            font-family: Arial, sans-serif;
        }
        .hero {
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('https://source.unsplash.com/1600x900/?fashion');
            background-size: cover;
            background-position: center;
            color: white;
            text-align: center;
            padding: 100px 20px;
            padding-bottom: 100px;
        }
        .hero h1 {
            font-size: 3rem;
            font-weight: bold;
        }
        .btn-custom {
            background-color: #ff6600;
            color: white;
        }
        .btn-custom:hover {
            background-color: #e65c00;
        }
        .card-img-top {
            height: 250px; 
            object-fit: cover;
        }
    </style>
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="hero">
    <h1>Descubre tu estilo en UrbanWear</h1>
    <p>Moda urbana para todas las estaciones</p>
    <a href="#productos" class="btn btn-custom btn-lg">Ver tienda</a>
</div>

<div class="container mt-5" id="productos">
    <h2 class="text-center mb-4">Productos Destacados</h2>
    <div class="row">

        <!-- Producto 1: Camiseta Versace -->
        <div class="col-md-4">
            <div class="card">
                <img src="img/camiseta_versace.jpg" class="card-img-top" alt="Camiseta Versace">
                <div class="card-body text-center">
                    <h5 class="card-title">Camiseta Versace</h5>
                    <p class="card-text">Camiseta ideal para verano, cómoda y 100% algodón.</p>
                    <a href="agregar_al_carrito.php?id=1" class="btn btn-custom">Añadir al carrito</a>
                </div>
            </div>
        </div>

        <!-- Producto 2: Camiseta Tupac -->
        <div class="col-md-4">
            <div class="card">
                <img src="img/producto1.jpg" class="card-img-top" alt="Camiseta Tupac">
                <div class="card-body text-center">
                    <h5 class="card-title">Camiseta Tupac</h5>
                    <p class="card-text">De color negro, casual y oversize. Tendencia del momento!!</p>
                    <a href="agregar_al_carrito.php?id=2" class="btn btn-custom">Añadir al carrito</a>
                </div>
            </div>
        </div>

        <!-- Producto 3: Sudadera Tupac -->
        <div class="col-md-4">
            <div class="card">
                <img src="img/producto2.jpg" class="card-img-top" alt="Sudadera Tupac">
                <div class="card-body text-center">
                    <h5 class="card-title">Sudadera Tupac</h5>
                    <p class="card-text">Oversize, color negro y protagonizada por el famoso artista Tupac.</p>
                    <a href="agregar_al_carrito.php?id=3" class="btn btn-custom">Añadir al carrito</a>
                </div>
            </div>
        </div>

    </div>
</div>

<?php include 'includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
