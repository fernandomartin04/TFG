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

    // Subida de imagen
    $imagen_nombre = $_FILES['imagen']['name'];
    $imagen_temporal = $_FILES['imagen']['tmp_name'];
    $ruta_destino = "img/" . $imagen_nombre;

    if (move_uploaded_file($imagen_temporal, $ruta_destino)) {
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
            <input type="file" name="imagen" class="form-control" accept="img/" required>
        </div>
        <button type="submit" name="guardar" class="btn btn-primary">Guardar producto</button>
    </form>
</main>

<?php include "includes/footer.php"; ?>
