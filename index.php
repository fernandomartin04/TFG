<?php include 'includes/header.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>UrbanWear - Inicio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
            background-color: #f5f5f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .hero {
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('https://source.unsplash.com/1600x900/?fashion');
            background-size: cover;
            background-position: center;
            color: white;
            text-align: center;
            padding: 100px 20px 120px 20px;
        }
        .hero h1 {
            font-size: 3.5rem;
            font-weight: 700;
            letter-spacing: 2px;
            margin-bottom: 10px;
        }
        .hero p {
            font-size: 1.25rem;
            margin-bottom: 30px;
            font-weight: 500;
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
        }
        .btn-custom:hover {
            background-color: #e65c00;
            box-shadow: 0 6px 14px rgba(230,92,0,0.6);
            color: white;
            text-decoration: none;
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
        .container#productos {
            padding-bottom: 50px;
        }
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2.5rem;
            }
            .hero p {
                font-size: 1rem;
            }
            .card-img-top {
                height: 220px;
            }
        }
    </style>
</head>
<body>

<div class="hero">
    <h1>Descubre tu estilo en UrbanWear</h1>
    <p>Moda urbana para todas las estaciones</p>
    <a href="#productos" class="btn btn-custom btn-lg">Ver tienda</a>
</div>

<div class="container mt-5">
    <?php if (isset($_SESSION['usuario_id'])): ?>
        <div class="alert alert-success text-center">
            Has iniciado sesión como <strong><?= htmlspecialchars($_SESSION['nombre']) ?></strong>.
        </div>
    <?php else: ?>
        <div class="alert alert-warning text-center">
            No has iniciado sesión. <a href="login.php" class="btn btn-sm btn-primary ms-2">Inicia sesión</a>
        </div>
    <?php endif; ?>
</div>

<div class="container mt-5" id="productos">
    <h2 class="text-center mb-5 fw-bold" style="letter-spacing: 2px;">Productos Destacados</h2>
    <div class="row g-4">

        <div class="col-md-4">
            <div class="card">
                <img src="img/camiseta_versace.jpg" class="card-img-top" alt="Camiseta Versace" />
                <div class="card-body">
                    <h5 class="card-title">Camiseta Versace</h5>
                    <p class="card-text">Camiseta ideal para verano, cómoda y 100% algodón.</p>
                    <a href="detalle_producto.php?id=1" class="btn btn-custom">Ver detalles y añadir</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <img src="img/producto1.jpg" class="card-img-top" alt="Camiseta Tupac" />
                <div class="card-body">
                    <h5 class="card-title">Camiseta Tupac</h5>
                    <p class="card-text">De color negro, casual y oversize. ¡Tendencia del momento!</p>
                    <a href="detalle_producto.php?id=2" class="btn btn-custom">Ver detalles y añadir</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <img src="img/producto2.jpg" class="card-img-top" alt="Sudadera Tupac" />
                <div class="card-body">
                    <h5 class="card-title">Sudadera Tupac</h5>
                    <p class="card-text">Oversize, color negro y protagonizada por el famoso artista Tupac.</p>
                    <a href="detalle_producto.php?id=3" class="btn btn-custom">Ver detalles y añadir</a>
                </div>
            </div>
        </div>

    </div>
</div>

<?php include 'includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
