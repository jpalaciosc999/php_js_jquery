<?php
// Formulario compartido por nuevo.php y editar_db.php.
if (!isset($pdo, $editar)) {
    http_response_code(404);
    exit;
}
$alumno = $editar ? buscar_alumno($pdo) : ['nombres' => '', 'apellidos' => '', 'correo' => '', 'aficiones' => ''];
$cursos = $editar ? cursos_alumno($pdo, (int) $alumno['id_alumno']) : [];
$errores = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validar_csrf();
    foreach (['nombres' => 50, 'apellidos' => 50, 'correo' => 30, 'aficiones' => 100] as $campo => $max) {
        $alumno[$campo] = is_string($_POST[$campo] ?? null) ? trim($_POST[$campo]) : '';
        if ($alumno[$campo] === '' || mb_strlen($alumno[$campo]) > $max) {
            $errores[] = ucfirst($campo) . ': ingrese entre 1 y ' . $max . ' caracteres.';
        }
    }
    if (!filter_var($alumno['correo'], FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'Ingrese un correo electrónico válido.';
    }
    $recibidos = $_POST['cursos'] ?? [];
    $cursos = [];
    $vistos = [];
    if (!is_array($recibidos) || count($recibidos) > 100) {
        $errores[] = 'La lista de cursos no es válida (máximo 100).';
        $recibidos = [];
    }
    foreach ($recibidos as $curso) {
        if (!is_array($curso)) {
            $errores[] = 'Curso no válido.';
            continue;
        }
        $nombre = is_string($curso['nombre'] ?? null) ? trim($curso['nombre']) : '';
        $anio = is_string($curso['anio'] ?? null) ? trim($curso['anio']) : '';
        if ($nombre === '' && $anio === '') continue;
        $cursos[] = ['nombre' => $nombre, 'anio' => $anio];
        if ($nombre === '' || mb_strlen($nombre) > 50 || !preg_match('/^(19|20)\d{2}$|^2100$/D', $anio)) {
            $errores[] = 'Cada curso necesita un nombre de hasta 50 caracteres y un año entre 1900 y 2100.';
        }
        $clave = mb_strtolower($nombre) . ':' . $anio;
        if (isset($vistos[$clave])) $errores[] = 'No repita el mismo curso en el mismo año.';
        $vistos[$clave] = true;
    }
    if (!$errores) {
        try {
            $pdo->beginTransaction();
            if ($editar) {
                $id = (int) $alumno['id_alumno'];
                $stmt = $pdo->prepare('UPDATE alumno SET nombres = ?, apellidos = ?, correo = ?, aficiones = ? WHERE id_alumno = ?');
                $stmt->execute([$alumno['nombres'], $alumno['apellidos'], $alumno['correo'], $alumno['aficiones'], $id]);
                $stmt = $pdo->prepare('DELETE FROM alumno_cursos WHERE id_alumno = ?');
                $stmt->execute([$id]);
            } else {
                $stmt = $pdo->prepare('INSERT INTO alumno (nombres, apellidos, correo, aficiones, id_usuario) VALUES (?, ?, ?, ?, ?)');
                $stmt->execute([$alumno['nombres'], $alumno['apellidos'], $alumno['correo'], $alumno['aficiones'], $_SESSION['id_usuario']]);
                $id = (int) $pdo->lastInsertId();
            }
            foreach ($cursos as $curso) {
                // Reutilizar el catálogo existente; LAST_INSERT_ID también devuelve el id de un curso ya creado.
                $stmt = $pdo->prepare('INSERT INTO cursos (nombre) VALUES (?) ON DUPLICATE KEY UPDATE id_curso = LAST_INSERT_ID(id_curso)');
                $stmt->execute([$curso['nombre']]);
                $idCurso = (int) $pdo->lastInsertId();
                $stmt = $pdo->prepare('INSERT INTO alumno_cursos (id_alumno, id_curso, anio) VALUES (?, ?, ?)');
                $stmt->execute([$id, $idCurso, $curso['anio']]);
            }
            $pdo->commit();
            $_SESSION['success'] = $editar ? 'Registro actualizado.' : 'Registro agregado.';
            redirect('index.php');
        } catch (PDOException $error) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log($error->getMessage());
            $errores[] = 'No se pudo guardar. Revise que no haya cursos repetidos en el mismo año e intente de nuevo.';
        }
    }
}
inicio($editar ? 'Editar estudiante' : 'Ingreso de nuevo estudiante');
?>
<?php if ($errores): ?><div class="alert alert-danger" role="alert"><ul class="mb-0"><?php foreach (array_unique($errores) as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<form method="post" class="student-form">
    <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">
    <?php foreach (['nombres' => 'Nombres', 'apellidos' => 'Apellidos', 'correo' => 'Correo'] as $campo => $label): ?>
    <div class="row mb-3 align-items-center">
        <label for="<?= e($campo) ?>" class="col-sm-3 col-form-label"><?= e($label) ?></label>
        <div class="col-sm-9"><input class="form-control" id="<?= e($campo) ?>" name="<?= e($campo) ?>" type="<?= $campo === 'correo' ? 'email' : 'text' ?>" maxlength="<?= $campo === 'correo' ? 30 : 50 ?>" value="<?= e($alumno[$campo]) ?>" required></div>
    </div>
    <?php endforeach; ?>
    <div class="row mb-4">
        <label for="aficiones" class="col-sm-3 col-form-label">Aficiones</label>
        <div class="col-sm-9"><textarea class="form-control" id="aficiones" name="aficiones" rows="3" maxlength="100" required><?= e($alumno['aficiones']) ?></textarea></div>
    </div>
    <div class="d-flex gap-3 align-items-center mb-2">
        <h2 class="h5 mb-0">Cursos y certificaciones IT</h2>
        <button type="button" class="btn btn-outline-primary" id="addCurso" aria-label="Agregar curso">+</button>
    </div>
    <p class="small text-secondary">Escriba el nombre para ver cursos ya registrados. Puede guardar un curso nuevo. Año entre 1900 y 2100.</p>
    <div id="curso_fields">
    <?php foreach ($cursos ?: [['nombre' => '', 'anio' => '']] as $i => $curso): ?>
        <div class="course-row border rounded p-3 mb-3">
            <div class="d-flex gap-2 align-items-center mb-2">
                <label for="anio-<?= $i ?>">Año</label>
                <input type="number" min="1900" max="2100" step="1" class="form-control w-auto" id="anio-<?= $i ?>" name="cursos[<?= $i ?>][anio]" value="<?= e($curso['anio']) ?>">
                <button type="button" class="btn btn-outline-danger remove-course" aria-label="Quitar curso">−</button>
            </div>
            <label for="curso-<?= $i ?>" class="form-label">Nombre del curso</label>
            <input type="text" class="form-control cursos" maxlength="50" id="curso-<?= $i ?>" name="cursos[<?= $i ?>][nombre]" value="<?= e($curso['nombre']) ?>" autocomplete="off">
        </div>
    <?php endforeach; ?>
    </div>
    <p id="course-status" class="small text-danger" role="status"></p>
    <div class="mt-4"><button type="submit" class="btn btn-primary">Grabar</button><a href="index.php" class="btn btn-outline-secondary ms-2">Cancelar</a></div>
</form>
<script src="assets/jquery.min.js"></script>
<script src="assets/jquery-ui.min.js"></script>
<script src="assets/cursos.js"></script>
<?php fin(); ?>
