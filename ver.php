<?php
require_once __DIR__ . '/app.php';
require_once __DIR__ . '/db.php';
$alumno = buscar_alumno($pdo);
$cursos = cursos_alumno($pdo, (int) $alumno['id_alumno']);
inicio('Consulta de alumno');
?>
<dl class="row">
<?php foreach (['nombres' => 'Nombres', 'apellidos' => 'Apellidos', 'correo' => 'Correo', 'aficiones' => 'Aficiones'] as $campo => $label): ?>
<dt class="col-sm-3"><?= e($label) ?></dt><dd class="col-sm-9"><?= nl2br(e($alumno[$campo])) ?></dd>
<?php endforeach; ?></dl>
<h2 class="h5 mt-4">Cursos y certificaciones IT</h2>
<table class="table table-bordered"><thead><tr><th>Año</th><th>Nombre</th></tr></thead><tbody>
<?php foreach ($cursos as $curso): ?><tr><td><?= e($curso['anio']) ?></td><td><?= e($curso['nombre']) ?></td></tr><?php endforeach; ?>
<?php if (!$cursos): ?><tr><td colspan="2">Sin cursos registrados.</td></tr><?php endif; ?>
</tbody></table><a href="index.php" class="btn btn-outline-secondary">Regresar</a>
<?php fin(); ?>
