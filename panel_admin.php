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

$miUsuario = $_SESSION['nombre'];
$queryUsuarios = "SELECT u.id, u.nombre, u.email, u.rol_id, r.nombre_rol 
                  FROM usuarios u 
                  LEFT JOIN roles r ON u.rol_id = r.id 
                  WHERE u.nombre != ? 
                  ORDER BY u.id ASC";
$stmtUsuarios = $conn->prepare($queryUsuarios);
$stmtUsuarios->bind_param("s", $miUsuario);
$stmtUsuarios->execute();
$resultUsuarios = $stmtUsuarios->get_result();

$queryCupones = "SELECT * FROM cupones ORDER BY id DESC";
$resultCupones = $conn->query($queryCupones);
?>

<style>
.admin-container {
    max-width: 1400px;
    margin: 40px auto;
    padding: 0 15px;
    font-family: Arial, sans-serif;
}
.admin-header {
    background-color: #212529;
    color: #f8f9fa;
    padding: 1rem;
    border-radius: 0.25rem;
    margin-bottom: 1.5rem;
    text-align: center;
    font-weight: 600;
    font-size: 1.8rem;
}
.admin-content {
    display: flex;
    gap: 2rem;
    align-items: flex-start;
    flex-wrap: nowrap;
}
.admin-left-column {
    flex: 0 1 40%;
    display: flex;
    flex-direction: column;
    gap: 2rem;
}
.admin-register, .admin-cupones {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 0.25rem;
    box-shadow: 0 0 12px rgb(0 0 0 / 0.1);
    max-height: none;
    overflow-y: visible;
}
.admin-register h2, .admin-cupones h2 {
    color: #154c79;
    margin-bottom: 1.5rem;
    font-weight: 700;
}
.admin-users {
    flex: 1 1 58%;
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 0.25rem;
    box-shadow: 0 0 12px rgb(0 0 0 / 0.1);
    overflow-x: auto;
    overflow-y: visible;
}
.admin-users h2 {
    color: #154c79;
    margin-bottom: 1.5rem;
    font-weight: 700;
}
table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.9rem;
    table-layout: fixed;
    min-width: 750px;
}
th, td {
    padding: 0.5rem 0.6rem;
    border: 1px solid #dee2e6;
    text-align: center;
    vertical-align: middle;
    word-wrap: break-word;
    overflow-wrap: break-word;
}
th {
    background-color: #154c79;
    color: #f8f9fa;
    white-space: nowrap;
    font-weight: 600;
}
form select.form-select, form input.form-control {
    width: 100%;
    margin-bottom: 0.3rem;
    font-size: 0.85rem;
}
button.btn, button.btn-sm {
    background-color: #154c79;
    border-color: #154c79;
    color: #fff;
    width: 100%;
    font-size: 0.85rem;
    padding: 0.25rem 0;
    border-radius: 3px;
    cursor: pointer;
    transition: background-color 0.2s ease-in-out;
}
button.btn:hover, button.btn-sm:hover {
    background-color: #0d3053;
    border-color: #0d3053;
}
a.btn-danger {
    background-color: #dc3545;
    border-color: #dc3545;
    color: #fff !important;
    padding: 0.25rem 0.5rem;
    font-size: 0.85rem;
    border-radius: 3px;
    display: inline-block;
    cursor: pointer;
    text-decoration: none;
}
a.btn-danger:hover {
    background-color: #b02a37;
    border-color: #b02a37;
    color: #fff !important;
}
@media (max-width: 768px) {
    .admin-content {
        flex-direction: column;
    }
    .admin-left-column, .admin-users {
        flex: 1 1 100%;
        margin-bottom: 1.5rem;
    }
}
</style>

<div class="admin-container">
    <div class="admin-header">Panel de administración</div>
    <div class="admin-content">
        <div class="admin-left-column">
            <!-- Formulario Registro Usuario -->
            <div class="admin-register">
                <h2>Registrar nuevo usuario</h2>
                <form method="POST" novalidate>
                    <label for="nombre" class="form-label">Nombre</label>
                    <input id="nombre" name="nombre" type="text" class="form-control" required>

                    <label for="email" class="form-label">Correo electrónico</label>
                    <input id="email" name="email" type="email" class="form-control" required>

                    <label for="contrasena" class="form-label">Contraseña</label>
                    <input id="contrasena" name="contrasena" type="password" class="form-control" required>
                    <small class="form-text text-muted mb-3">Mínimo 12 caracteres, con mayúsculas, minúsculas, números y símbolos.</small>

                    <label for="contrasena2" class="form-label">Repetir contraseña</label>
                    <input id="contrasena2" name="contrasena2" type="password" class="form-control" required>

                    <label for="rol_id" class="form-label">Rol</label>
                    <select id="rol_id" name="rol_id" class="form-select" required>
                        <option value="1">Cliente</option>
                        <option value="2">Vendedor</option>
                        <option value="3">Administrador</option>
                    </select>

                    <button name="boton_registrar" type="submit" class="btn mt-3">Registrar</button>
                </form>
            </div>

            <!-- Gestión Cupones -->
            <div class="admin-cupones">
                <h2>Gestión de Cupones</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Código</th>
                            <th>Tipo</th>
                            <th>Valor</th>
                            <th>Fecha inicio</th>
                            <th>Fecha fin</th>
                            <th>Activo</th>
                            <th>Eliminar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($cupon = $resultCupones->fetch_assoc()): ?>
                        <tr>
                            <td><?= $cupon['id'] ?></td>
                            <td><?= htmlspecialchars($cupon['codigo']) ?></td>
                            <td><?= htmlspecialchars($cupon['tipo']) ?></td>
                            <td><?= htmlspecialchars($cupon['valor']) ?></td>
                            <td><?= htmlspecialchars($cupon['fecha_inicio']) ?></td>
                            <td><?= htmlspecialchars($cupon['fecha_fin']) ?></td>
                            <td><?= $cupon['activo'] ? 'Sí' : 'No' ?></td>
                            <td><a href="eliminar_cupon.php?id=<?= $cupon['id'] ?>" class="btn btn-danger">Eliminar</a></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

                <!-- Formulario para crear nuevo cupón -->
                <form method="POST" action="crear_cupon.php" class="mt-4">
                    <h3 class="mb-3">Crear nuevo cupón</h3>
                    <label for="codigo" class="form-label">Código</label>
                    <input type="text" id="codigo" name="codigo" class="form-control" required>

                    <label for="tipo" class="form-label">Tipo</label>
                    <select name="tipo" id="tipo" class="form-select" required>
                        <option value="porcentaje">Porcentaje</option>
                        <option value="cantidad">Cantidad fija</option>
                    </select>

                    <label for="valor" class="form-label">Valor</label>
                    <input type="number" step="0.01" id="valor" name="valor" class="form-control" required>

                    <label for="fecha_inicio" class="form-label">Fecha inicio</label>
                    <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control" required>

                    <label for="fecha_fin" class="form-label">Fecha fin</label>
                    <input type="date" id="fecha_fin" name="fecha_fin" class="form-control" required>

                    <div class="form-check mt-3 mb-3">
                        <input type="checkbox" id="activo" name="activo" class="form-check-input" checked>
                        <label for="activo" class="form-check-label">Activo</label>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Crear cupón</button>
                </form>
            </div>
        </div>

        <!-- Usuarios existentes -->
        <div class="admin-users">
            <h2>Usuarios existentes</h2>
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
                    while ($row = $resultUsuarios->fetch_assoc()) {
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
                                    <button type='submit' class='btn btn-sm mt-1'>Cambiar</button>
                                </form>
                              </td>";
                        echo "<td><a onclick=\"delUser('{$id}','{$nombre}')\" class='btn btn-danger'><i class='bi bi-trash'></i> Eliminar</a></td>";
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
