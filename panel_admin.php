<?php
session_start();
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

<div class="container mt-4">
    <div class="header-container text-white p-4 rounded shadow-sm mb-4" style="background-color: #154c79">
        <h1 class="text-center">Panel de administración</h1>
    </div>
    <div id="contenido" class="d-flex justify-content-around">
        <div id="registrar">
            <div class="container">
                <div class="header-container text-white p-4 rounded shadow-sm mb-1" style="background-color: #154c79">
                    <h1 class="text-center">Registrar</h1>
                </div>
                <form method="POST" class="mt-4">
                    <div class="form-group">
                        <label for="usuario">Nombre</label>
                        <input type="text" class="form-control" name="nombre" required>
                    </div>
                    <div class="form-group mt-4">
                        <label for="contrasena">Contraseña</label>
                        <input type="password" class="form-control" name="contrasena" required>
                    </div>
                    <div class="form-group mt-4">
                        <label for="contrasena2">Repetir contraseña</label>
                        <input type="password" class="form-control" name="contrasena2" required>
                    </div>
                    <div class="form-group mt-4">
                        <label for="email">Correo</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="form-group mt-4">
                        <label for="rol_id" class="form-label">Rol</label>
                        <select name="rol_id" class="form-control">
                            <option value="1">Cliente</option>
                            <option value="2">Vendedor</option>
                            <option value="3">Administrador</option>
                        </select>
                    </div>
                    <div class="form-group mt-4">
                        <button type="submit" name="boton_registrar" class="btn btn-primary">Registrar</button>
                    </div>
                </form>
            </div>
        </div>
        <div id="usuarios_existentes">
            <div class="container">
                <div class="header-container text-white p-4 rounded shadow-sm mb-1" style="background-color: #154c79">
                    <h1 class="text-center">Usuarios</h1>
                </div>
                <div class="table-responsive rounded">
                    <table id='userTable' class="table table-bordered rounded table-hover custom-table">
                        <thead class="text-white text-center" style="background-color: #154c79">
                            <tr>
                                <th>ID</th>
                                <th>Usuario</th>
                                <th>Rol</th>
                                <th>Correo</th>
                                <th>Cambiar Rol</th>
                                <th>Eliminar</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">
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
                                echo "<td>{$email}</td>";
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
                                            <button type='submit' class='btn btn-sm btn-primary mt-1'>Cambiar</button>
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
    </div>
</div>

<div class="container text-center mt-5">
    <a href="index.php" class="btn btn-warning mb-5">Volver</a>
</div>

<?php include "includes/footer.php"; ?>