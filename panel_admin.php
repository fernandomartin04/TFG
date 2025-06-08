<?php
include "includes/header.php";
require_once "includes/db.php";

if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 3) {
    header("Location: index.php");
    exit();
}

function validarContrasena($pwd) {
    return strlen($pwd) >= 12 &&
           preg_match('/[A-Z]/', $pwd) &&
           preg_match('/[a-z]/', $pwd) &&
           preg_match('/[0-9]/', $pwd) &&
           preg_match('/[\W]/', $pwd);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["boton_registrar"])) {
    $nombre     = trim($_POST["nombre"]);
    $email      = trim($_POST["email"]);
    $contrasena = $_POST["contrasena"];
    $contrasena2= $_POST["contrasena2"];
    $rol_id     = (int) $_POST["rol_id"];

    if (empty($nombre) || empty($email) || empty($contrasena) || empty($contrasena2)) {
        echo "<script>alert('¡Debes rellenar todos los campos!')</script>";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('¡Correo electrónico no válido!')</script>";
    } elseif ($contrasena !== $contrasena2) {
        echo "<script>alert('¡Las contraseñas no coinciden!')</script>";
    } elseif (!validarContrasena($contrasena)) {
        echo "<script>alert('¡Contraseña insegura! Debe tener al menos 12 caracteres, una mayúscula, una minúscula, un número y un símbolo.')</script>";
    } else {
        $stmt1 = $conn->prepare("SELECT id FROM usuarios WHERE nombre = ?");
        $stmt1->bind_param("s", $nombre);
        $stmt1->execute();
        $stmt1->store_result();

        $stmt2 = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt2->bind_param("s", $email);
        $stmt2->execute();
        $stmt2->store_result();

        if ($stmt1->num_rows > 0) {
            echo "<script>alert('¡El usuario ya existe!')</script>";
        } elseif ($stmt2->num_rows > 0) {
            echo "<script>alert('¡El correo electrónico ya está registrado!')</script>";
        } else {
            $hash = password_hash($contrasena, PASSWORD_BCRYPT, ["cost" => 12]);
            $stmt = $conn->prepare("INSERT INTO usuarios (nombre, email, contrasena, rol_id) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sssi", $nombre, $email, $hash, $rol_id);
            if ($stmt->execute()) {
                echo "<div class='alert alert-success text-center'>Usuario registrado exitosamente.</div>";
            } else {
                echo "<div class='alert alert-danger text-center'>Error al registrar usuario.</div>";
            }
        }
    }
}
?>

<style>
.admin-container {
    max-width: 1200px;
    margin: 40px auto;
    padding: 0 15px;
}

.admin-header {
    background-color: #212529; /* navbar oscuro */
    color: #f8f9fa;
    padding: 1rem;
    border-radius: 0.25rem;
    margin-bottom: 1.5rem;
    text-align: center;
    font-weight: 600;
}

.admin-content {
    display: flex;
    gap: 2rem;
    flex-wrap: nowrap;
    justify-content: space-between;
}

.admin-register {
    flex: 0 1 28%;
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 0.25rem;
    box-shadow: 0 0 12px rgb(0 0 0 / 0.1);
}

.admin-users {
    flex: 0 1 70%;
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 0.25rem;
    box-shadow: 0 0 12px rgb(0 0 0 / 0.1);
    overflow-x: hidden; /* ocultamos scroll horizontal */
}

.admin-users table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.85rem;
    table-layout: fixed; /* fuerza ancho fijo */
}

.admin-users th, .admin-users td {
    padding: 0.5rem 0.4rem;
    border: 1px solid #dee2e6;
    text-align: center;
    vertical-align: middle;
    word-wrap: break-word;
    overflow-wrap: break-word;
}

.admin-users th {
    background-color: #154c79; /* color azul sobrio de la web */
    color: #f8f9fa;
    white-space: nowrap;
}

.admin-users select.form-select {
    width: 100%;
    display: block;
    margin-bottom: 0.25rem;
    font-size: 0.85rem;
}

.admin-users button.btn-sm {
    width: 100%;
    font-size: 0.85rem;
    padding: 0.25rem 0;
}

.admin-users a.btn-danger {
    white-space: nowrap;
    font-size: 0.85rem;
    padding: 0.25rem 0.5rem;
}

/* Responsive */
@media (max-width: 768px) {
    .admin-content {
        flex-direction: column;
    }
    .admin-register, .admin-users {
        flex: 1 1 100%;
        margin-bottom: 1.5rem;
    }
}
</style>

<div class="admin-container">
    <div class="admin-header">
        <h1>Panel de administración</h1>
    </div>
    <div class="admin-content">
        <div class="admin-register">
            <h2 class="mb-4 text-primary text-center" style="color: #154c79;">Registrar nuevo usuario</h2>
            <form method="POST" novalidate>
                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre</label>
                    <input id="nombre" name="nombre" type="text" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Correo electrónico</label>
                    <input id="email" name="email" type="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="contrasena" class="form-label">Contraseña</label>
                    <input id="contrasena" name="contrasena" type="password" class="form-control" required>
                    <small class="form-text text-muted">Mínimo 12 caracteres, con mayúsculas, minúsculas, números y símbolos.</small>
                </div>
                <div class="mb-3">
                    <label for="contrasena2" class="form-label">Repetir contraseña</label>
                    <input id="contrasena2" name="contrasena2" type="password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="rol_id" class="form-label">Rol</label>
                    <select id="rol_id" name="rol_id" class="form-select" required>
                        <option value="1">Cliente</option>
                        <option value="2">Vendedor</option>
                        <option value="3">Administrador</option>
                    </select>
                </div>
                <button name="boton_registrar" type="submit" class="btn" style="background-color: #154c79; color: white; width: 100%;">Registrar</button>
            </form>
        </div>
        <div class="admin-users">
            <h2 class="mb-4 text-primary text-center" style="color: #154c79;">Usuarios existentes</h2>
            <table>
                <thead>
                    <tr>
                        <th style="width: 5%;">ID</th>
                        <th style="width: 15%;">Usuario</th>
                        <th style="width: 15%;">Rol</th>
                        <th style="width: 30%;">Correo</th>
                        <th style="width: 20%;">Cambiar Rol</th>
                        <th style="width: 15%;">Eliminar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $orden = 'id';
                    $columnasOrdenables = ['id', 'nombre', 'email'];
                    if (isset($_GET['ordenar']) && in_array($_GET['ordenar'], $columnasOrdenables)) {
                        $orden = $_GET['ordenar'];
                    }

                    $miUsuario = $_SESSION['nombre'];
                    $query = "SELECT u.id, u.nombre, u.email, u.rol_id, r.nombre_rol 
                              FROM usuarios u 
                              LEFT JOIN roles r ON u.rol_id = r.id 
                              WHERE u.nombre != ? 
                              ORDER BY $orden ASC";

                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("s", $miUsuario);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    while ($row = $result->fetch_assoc()) {
                        $id = $row['id'];
                        $nombre = htmlspecialchars($row['nombre']);
                        $email = htmlspecialchars($row['email']);
                        $rol_id = $row['rol_id'];
                        $nombre_rol = $row['nombre_rol'];

                        echo "<tr>";
                        echo "<td>{$id}</td>";
                        echo "<td>{$nombre}</td>";
                        echo "<td>{$nombre_rol}</td>";
                        echo "<td style='word-wrap: break-word; overflow-wrap: break-word;'>{$email}</td>";
                        echo "<td>
                                <form method='POST' action='actualizar_rol.php'>
                                    <input type='hidden' name='id' value='{$id}'>
                                    <select name='nuevo_rol' class='form-select form-select-sm'>";
                        $roles = [1 => 'Cliente', 2 => 'Vendedor', 3 => 'Administrador'];
                        foreach ($roles as $rid => $rnombre) {
                            $selected = $rol_id == $rid ? "selected" : "";
                            echo "<option value='{$rid}' {$selected}>{$rnombre}</option>";
                        }
                        echo "        </select>
                                    <button type='submit' class='btn btn-sm btn-primary mt-1' style='background-color: #154c79; border-color: #154c79;'>Cambiar</button>
                                </form>
                              </td>";
                        echo "<td><a onclick=\"delUser('{$id}','{$nombre}')\" class='btn btn-danger btn-sm'><i class='bi bi-trash'></i> Eliminar</a></td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="container text-center mt-5">
    <a href="index.php" class="btn btn-warning mb-5">Volver</a>
</div>

<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script>
function delUser(id, nombre) {
    if (confirm("¿Estás seguro de eliminar al usuario '" + nombre + "'?")) {
        $.post('eliminar_usuario.php', { id: id }, function(response) {
            alert("Usuario eliminado.");
            location.reload();
        }).fail(function(jqXHR, textStatus, errorThrown) {
            console.error("Ajax error:", textStatus, errorThrown);
        });
    }
}
</script>

<?php include "includes/footer.php"; ?>
