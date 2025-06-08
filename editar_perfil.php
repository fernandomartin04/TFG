<?php include "includes/header.php"; ?>

<?php
require_once __DIR__ . '/includes/db.php';
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = intval($_SESSION['usuario_id']);
$mensaje = "";

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nuevo_nombre    = trim($_POST['nombre'] ?? '');
    $nuevo_email     = trim($_POST['email'] ?? '');
    $password_actual = $_POST['pwd_actual'] ?? '';
    $nueva_pwd       = $_POST['nueva_pwd'] ?? '';
    $rep_nueva_pwd   = $_POST['rep_nueva_pwd'] ?? '';

    if ($nuevo_nombre === '' || $nuevo_email === '') {
        $mensaje = "<div class='alert alert-warning'>El nombre y el email no pueden estar vacíos.</div>";
    } elseif (!filter_var($nuevo_email, FILTER_VALIDATE_EMAIL)) {
        $mensaje = "<div class='alert alert-warning'>El email no tiene un formato válido.</div>";
    } else {
        $sqlCheckEmail = "SELECT id FROM usuarios WHERE email = ? AND id <> ?";
        $stmtCheck = $conn->prepare($sqlCheckEmail);
        $stmtCheck->bind_param("si", $nuevo_email, $usuario_id);
        $stmtCheck->execute();
        $resCheck = $stmtCheck->get_result();
        if ($resCheck->num_rows > 0) {
            $mensaje = "<div class='alert alert-warning'>Ya existe otro usuario con ese email.</div>";
            $stmtCheck->close();
        } else {
            $stmtCheck->close();
            $cambia_contraseña = false;

            if ($password_actual !== '' || $nueva_pwd !== '' || $rep_nueva_pwd !== '') {
                if ($password_actual === '' || $nueva_pwd === '' || $rep_nueva_pwd === '') {
                    $mensaje = "<div class='alert alert-warning'>Para cambiar la contraseña, completa los tres campos.</div>";
                } elseif ($nueva_pwd !== $rep_nueva_pwd) {
                    $mensaje = "<div class='alert alert-warning'>La nueva contraseña y su repetición no coinciden.</div>";
                } else {
                    $sqlGetPwd = "SELECT contrasena FROM usuarios WHERE id = ?";
                    $stmtPwd = $conn->prepare($sqlGetPwd);
                    $stmtPwd->bind_param("i", $usuario_id);
                    $stmtPwd->execute();
                    $resPwd = $stmtPwd->get_result();
                    $filaPwd = $resPwd->fetch_assoc();
                    $stmtPwd->close();

                    if (!password_verify($password_actual, $filaPwd['contrasena'])) {
                        $mensaje = "<div class='alert alert-danger'>La contraseña actual no es correcta.</div>";
                    } else {
                        $cambia_contraseña = true;
                        $hash_nueva_pwd = password_hash($nueva_pwd, PASSWORD_DEFAULT);
                    }
                }
            }

            if ($mensaje === "") {
                if ($cambia_contraseña) {
                    $sqlUpdate = "UPDATE usuarios SET nombre = ?, email = ?, contrasena = ? WHERE id = ?";
                    $stmtUpd = $conn->prepare($sqlUpdate);
                    $stmtUpd->bind_param("sssi", $nuevo_nombre, $nuevo_email, $hash_nueva_pwd, $usuario_id);
                } else {
                    $sqlUpdate = "UPDATE usuarios SET nombre = ?, email = ? WHERE id = ?";
                    $stmtUpd = $conn->prepare($sqlUpdate);
                    $stmtUpd->bind_param("ssi", $nuevo_nombre, $nuevo_email, $usuario_id);
                }

                if ($stmtUpd->execute()) {
                    $_SESSION['nombre'] = $nuevo_nombre;
                    $mensaje = "<div class='alert alert-success'>Datos actualizados correctamente.</div>";
                } else {
                    $mensaje = "<div class='alert alert-danger'>Error al actualizar en la base de datos: {$stmtUpd->error}</div>";
                }
                $stmtUpd->close();
            }
        }
    }
}

$sqlUser = "SELECT nombre, email FROM usuarios WHERE id = ?";
$stmtUsr = $conn->prepare($sqlUser);
$stmtUsr->bind_param("i", $usuario_id);
$stmtUsr->execute();
$resUsr = $stmtUsr->get_result();
if ($resUsr->num_rows !== 1) {
    $stmtUsr->close();
    header("Location: logout.php");
    exit;
}
$filaUsr = $resUsr->fetch_assoc();
$stmtUsr->close();
?>

<div class="container mt-5" style="max-width: 600px;">
    <h2 class="mb-4">Editar Perfil</h2>

    <?= $mensaje ?>

    <form action="editar_perfil.php" method="post">
        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre de usuario</label>
            <input type="text" class="form-control" id="nombre" name="nombre" required value="<?= htmlspecialchars($filaUsr['nombre'], ENT_QUOTES) ?>">
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Correo electrónico</label>
            <input type="email" class="form-control" id="email" name="email" required value="<?= htmlspecialchars($filaUsr['email'], ENT_QUOTES) ?>">
        </div>

        <hr>
        <p class="text-muted">Si no quieres cambiar la contraseña, deja estos campos vacíos.</p>

        <div class="mb-3">
            <label for="pwd_actual" class="form-label">Contraseña actual</label>
            <input type="password" class="form-control" id="pwd_actual" name="pwd_actual" placeholder="Introduce tu contraseña actual">
        </div>

        <div class="mb-3">
            <label for="nueva_pwd" class="form-label">Nueva contraseña</label>
            <input type="password" class="form-control" id="nueva_pwd" name="nueva_pwd" placeholder="Deja vacío si no quieres cambiarla">
        </div>

        <div class="mb-3">
            <label for="rep_nueva_pwd" class="form-label">Repetir nueva contraseña</label>
            <input type="password" class="form-control" id="rep_nueva_pwd" name="rep_nueva_pwd" placeholder="Vuelve a escribir la nueva contraseña">
        </div>

        <button type="submit" class="btn btn-primary">Guardar cambios</button>
    </form>
</div>

<?php include "includes/footer.php"; ?>
