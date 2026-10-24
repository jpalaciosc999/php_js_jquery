<?php
require_once __DIR__ . '/app.php';
if (autenticado()) redirect('index.php');
require_once __DIR__ . '/db.php';
$correo = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validar_csrf();
    $correo = is_string($_POST['correo'] ?? null) ? trim($_POST['correo']) : '';
    $pass = is_string($_POST['pass'] ?? null) ? $_POST['pass'] : '';
    $stmt = $pdo->prepare('SELECT * FROM usuario WHERE correo = ?');
    $stmt->execute([$correo]);
    $usuario = $stmt->fetch();
    if ($usuario && password_verify($pass, $usuario['password'])) {
        session_regenerate_id(true);
        unset($_SESSION['csrf']);
        $_SESSION['id_usuario'] = $usuario['id_usuario'];
        $_SESSION['nombre'] = $usuario['nombre'];
        $_SESSION['success'] = 'Ingreso correcto.';
        redirect('index.php');
    }
    $_SESSION['error'] = 'Correo o contraseña incorrectos.';
}
inicio('Ingreso al sistema');
?>
<form method="post" class="login-form">
<input type="hidden" name="csrf" value="<?= e(csrf()) ?>">
<div class="mb-3"><label for="correo" class="form-label">Usuario (correo)</label><input class="form-control" type="email" name="correo" id="correo" maxlength="30" autocomplete="username" required value="<?= e($correo) ?>"></div>
<div class="mb-4"><label for="pass" class="form-label">Contraseña</label><input class="form-control" type="password" name="pass" id="pass" autocomplete="current-password" required></div>
<button class="btn btn-primary" type="submit">Aceptar</button><a class="btn btn-outline-secondary ms-2" href="index.php">Cancelar</a>
</form>
<?php fin(); ?>
