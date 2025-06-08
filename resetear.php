<?php
require "includes/db.php";
session_start();

$token = $_GET['token'] ?? '';
$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'];
    $nueva = $_POST['nueva'] ?? '';
    $confirmar = $_POST['confirmar'] ?? '';

    if ($nueva === $confirmar && strlen($nueva) >= 6) {
        $stmt = $conn->prepare("SELECT usuario_id FROM tokens_recuperacion WHERE token = ? AND expira > NOW()");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows === 1) {
            $usuario = $res->fetch_assoc();
            $hash = password_hash($nueva, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE usuarios SET contrasena = ? WHERE id = ?");
            $stmt->bind_param("si", $hash, $usuario['usuario_id']);
            $stmt->execute();
            $conn->query("DELETE FROM tokens_recuperacion WHERE usuario_id = {$usuario['usuario_id']}");
            $mensaje = "<div class='alert alert-success'>Contraseña actualizada correctamente.</div>";
        } else {
            $mensaje = "<div class='alert alert-danger'>Token inválido o expirado.</div>";
        }
    } else {
        $mensaje = "<div class='alert alert-danger'>Las contraseñas no coinciden o son muy cortas.</div>";
    }
}
?>

<?php include "includes/header.php"; ?>
<div class="container mt-5">
    <h2>Restablecer contraseña</h2>
    <?= $mensaje ?>
    <form method="POST">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
        <div class="mb-3">
            <label class="form-label">Nueva contraseña</label>
            <input type="password" class="form-control" name="nueva" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Confirmar contraseña</label>
            <input type="password" class="form-control" name="confirmar" required>
        </div>
        <button type="submit" class="btn btn-primary">Actualizar contraseña</button>
    </form>
</div>
<?php include "includes/footer.php"; ?>