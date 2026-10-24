<?php
require_once __DIR__ . '/app.php';
requiere_login();
require_once __DIR__ . '/db.php';
$alumno = buscar_alumno($pdo);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validar_csrf();
    $stmt = $pdo->prepare('DELETE FROM alumno WHERE id_alumno = ?');
    $stmt->execute([$alumno['id_alumno']]);
    $_SESSION['success'] = 'Registro eliminado.';
    redirect('index.php');
}
inicio('Eliminar alumno');
?>
<p>¿Desea eliminar a <strong><?= e($alumno['nombres'] . ' ' . $alumno['apellidos']) ?></strong>?</p>
<p>Correo: <?= e($alumno['correo']) ?></p>
<p class="text-secondary">También se eliminarán sus asignaciones de cursos.</p>
<form method="post"><input type="hidden" name="csrf" value="<?= e(csrf()) ?>">
<button type="submit" class="btn btn-danger">Eliminar</button><a href="index.php" class="btn btn-outline-secondary ms-2">Cancelar</a></form>
<?php fin(); ?>
