<?php
include "includes/header.php";
require "includes/db.php";

// Comprobación de rol (solo vendedores/admins)
if (!isset($_SESSION['rol_id']) || ($_SESSION['rol_id'] != 2 && $_SESSION['rol_id'] != 3)) {
    header("Location: login.php");
    exit();
}

if (isset($_POST['guardar'])) {
    $nombre      = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $precio      = floatval($_POST['precio']);

    if ($precio <= 0) {
        echo "<div class='container mt-4 alert alert-danger'>El precio debe ser mayor que cero.</div>";
        exit;
    }

    // Datos del fichero subido
    $imagen_original  = $_FILES['imagen']['name'];
    $imagen_temporal  = $_FILES['imagen']['tmp_name'];
    $imagen_error     = $_FILES['imagen']['error'];
    $extension        = strtolower(pathinfo($imagen_original, PATHINFO_EXTENSION));

    // 1) Comprobamos si hubo error en la carga
    if ($imagen_error !== UPLOAD_ERR_OK) {
        echo "<div class='container mt-4 alert alert-danger'>Error al subir la imagen.</div>";
        exit;
    }

    // 2) Validamos extensión
    $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'webp'];
    if (!in_array($extension, $extensiones_permitidas)) {
        echo "<div class='container mt-4 alert alert-danger'>Formato de imagen no permitido.</div>";
        exit;
    }

    // 3) Movemos la imagen
    $nuevo_nombre_imagen = uniqid() . '.' . $extension;
    move_uploaded_file($imagen_temporal, "imagenes/" . $nuevo_nombre_imagen);

    // 4) Insertamos en la base de datos de forma segura
    $stmt = $conn->prepare("INSERT INTO productos (nombre, descripcion, precio, imagen, fecha_creacion, id_vendedor) VALUES (?, ?, ?, ?, NOW(), ?)");
    $stmt->bind_param("ssdsi", $nombre, $descripcion, $precio, $nuevo_nombre_imagen, $_SESSION['usuario_id']);
    
    if ($stmt->execute()) {
        echo "<div class='container mt-4 alert alert-success'>Producto añadido correctamente.</div>";
    } else {
        echo "<div class='container mt-4 alert alert-danger'>Error al guardar el producto.</div>";
    }

    $stmt->close();
}
?>

<div class="container mt-5">
    <h2>Añadir nuevo producto</h2>
    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre del producto</label>
            <input type="text" class="form-control" id="nombre" name="nombre" required>
        </div>
        <div class="mb-3">
            <label for="descripcion" class="form-label">Descripción</label>
            <textarea class="form-control" id="descripcion" name="descripcion" rows="3" required></textarea>
        </div>
        <div class="mb-3">
            <label for="precio" class="form-label">Precio (€)</label>
            <input type="number" step="0.01" class="form-control" id="precio" name="precio" required>
        </div>
        <div class="mb-3">
            <label for="imagen" class="form-label">Imagen</label>
            <input class="form-control" type="file" id="imagen" name="imagen" accept="image/*" required>
        </div>
        <button type="submit" name="guardar" class="btn btn-primary">Guardar producto</button>
    </form>
</div>
