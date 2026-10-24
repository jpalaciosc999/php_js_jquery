<?php
require_once __DIR__ . '/app.php';
require_once __DIR__ . '/db.php';
$alumnos = $pdo->query('SELECT * FROM alumno ORDER BY id_alumno DESC')->fetchAll();
inicio('Lista de estudiantes');
?>
<div class="d-flex justify-content-between align-items-center mb-4 gap-3">
<?php if (autenticado()): ?>
<span>Bienvenido, <?= e($_SESSION['nombre']) ?></span>
<form method="post" action="logout.php"><input type="hidden" name="csrf" value="<?= e(csrf()) ?>"><button class="btn btn-outline-secondary" type="submit">Salir</button></form>
<?php else: ?>
<a href="login.php">Ingresar (log in)</a><span class="badge text-bg-secondary">Modo lectura</span>
<?php endif; ?>
</div>
<div class="table-responsive"><table class="table table-bordered table-striped">
<thead class="table-light"><tr><th>Nombres</th><th>Apellidos</th><th>Correo</th><th>Aficiones</th><th>Acción</th></tr></thead><tbody>
<?php foreach ($alumnos as $alumno): ?>
<tr><td><?= e($alumno['nombres']) ?></td><td><?= e($alumno['apellidos']) ?></td><td><?= e($alumno['correo']) ?></td><td><?= e($alumno['aficiones']) ?></td>
<td class="text-nowrap"><a href="ver.php?id_alumno=<?= (int) $alumno['id_alumno'] ?>">Ver</a>
<?php if (autenticado()): ?>
 / <a href="editar_db.php?id_alumno=<?= (int) $alumno['id_alumno'] ?>">Editar</a>
 / <a class="text-danger" href="delete.php?id_alumno=<?= (int) $alumno['id_alumno'] ?>">Eliminar</a>
<?php endif; ?></td></tr>
<?php endforeach; ?>
<?php if (!$alumnos): ?><tr><td colspan="5" class="text-center text-secondary py-4">No hay estudiantes registrados.</td></tr><?php endif; ?>
</tbody></table></div>
<?php if (autenticado()): ?><a href="nuevo.php" class="btn btn-primary mt-3">Nuevo</a><?php endif; ?>
<?php fin(); ?>
