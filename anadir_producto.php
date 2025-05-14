<?php
include "includes/header.php";

// Solo permitir acceso a admins o vendedores (rol 2 o 3)
if (!isset($_SESSION['rol_id']) || ($_SESSION['rol_id'] != 2 && $_SESSION['rol_id'] != 3)) {
    header("Location: login.php");
    exit();
}

if (isset($_POST['guardar'])) {
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $precio = floatval($_POST['precio']);

    $imagen_original = $_FILES['imagen']['name'];
    $imagen_temporal = $_FILES['imagen']['tmp_name'];
    $imagen_tamano = $_FILES['imagen']['size'];
    $imagen_tipo = mime_content_type($imagen_temporal);

    // Obtener extensión y generar nombre único
    $extension = strtolower(pathinfo($imagen_original, PATHINFO_EXTENSION));
    $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'webp'];
    $nombre_unico = uniqid('producto_', true) . '.' . $extension;
    $ruta_destino = "img/" . $nombre_unico;

    // Validaciones
    if (!in_array($extension, $extensiones_permitidas)) {
        echo "<div class='container mt-4 alert alert-warning'>Solo se permiten imágenes JPG, JPEG, PNG o WEBP.</div>";
        exit; // Evitar continuar con el proceso si la imagen no es válida
    } elseif ($imagen_tamano > 2 * 1024 * 1024) {
        echo "<div class='container mt-4 alert alert-warning'>La imagen no puede superar los 2 MB.</div>";
        exit; // Evitar continuar si la imagen es demasiado grande
    } elseif (strpos($imagen_tipo, 'image/') !== 0) {
        echo "<div class='container mt-4 alert alert-warning'>El archivo debe ser una imagen válida.</div>";
        exit; // Evitar continuar si el archivo no es una imagen
    } elseif (move_uploaded_file($imagen_temporal, $ruta_destino)) {
        $query = "INSERT INTO productos (nombre, descripcion, precio, imagen) 
                  VALUES ('$nombre', '$descripcion', $precio, '$ruta_destino')";
        if (mysqli_query($conn, $query)) {
            echo "<div class='container mt-4 alert alert-success'>Producto añadido correctamente.</div>";
        } else {
            echo "<div class='container mt-4 alert alert-danger'>Error al guardar en la base de datos.</div>";
        }
    } else {
        echo "<div class='container mt-4 alert alert-warning'>Error al subir la imagen.</div>";
    }
}
?>

<main class="flex-grow-1 container mt-5">
    <h2>Añadir nuevo producto</h2>
    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label class="form-label">Nombre</label>
            <input type="text" name="nombre" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Descripción</label>
            <textarea name="descripcion" class="form-control" rows="3" required></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Precio (€)</label>
            <input type="number" step="0.01" name="precio" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Imagen</label>
            <input type="file" name="imagen" class="form-control" accept=".jpg, .jpeg, .png, .webp" required>
        </div>
        <button type="submit" name="guardar" class="btn btn-primary">Guardar producto</button>
    </form>
</main>

<?php include "includes/footer.php"; ?>

