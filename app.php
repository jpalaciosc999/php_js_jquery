<?php
declare(strict_types=1);
session_start([
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
    'cookie_secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'use_strict_mode' => true,
]);
function e($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}
function autenticado(): bool { return isset($_SESSION['id_usuario']); }
function requiere_login(): void {
    if (!autenticado()) {
        $_SESSION['error'] = 'Ingrese al sistema para realizar esta acción.';
        redirect('login.php');
    }
}
function csrf(): string { return $_SESSION['csrf'] ??= bin2hex(random_bytes(32)); }
function validar_csrf(): void {
    if (!is_string($_POST['csrf'] ?? null) || !hash_equals(csrf(), $_POST['csrf'])) {
        http_response_code(403);
        exit('Formulario vencido o no válido. Regrese y vuelva a intentarlo.');
    }
}
function inicio(string $titulo): void {
    $pageTitle = $titulo;
    require __DIR__ . '/head.php';
    foreach (['error' => 'danger', 'success' => 'success'] as $key => $class) {
        if (isset($_SESSION[$key])) {
            echo '<div class="alert alert-' . $class . '" role="alert">' . e($_SESSION[$key]) . '</div>';
            unset($_SESSION[$key]);
        }
    }
    echo '<h1 class="h3 mb-4">' . e($titulo) . '</h1>';
}
function fin(): void { echo '</main></body></html>'; }
function buscar_alumno(PDO $pdo): array {
    $id = filter_var($_GET['id_alumno'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $stmt = $pdo->prepare('SELECT * FROM alumno WHERE id_alumno = ?');
    $stmt->execute([$id ?: 0]);
    $alumno = $stmt->fetch();
    if (!$alumno) {
        $_SESSION['error'] = 'Alumno no encontrado.';
        redirect('index.php');
    }
    return $alumno;
}
function cursos_alumno(PDO $pdo, int $id): array {
    $stmt = $pdo->prepare('SELECT ac.anio, c.nombre FROM alumno_cursos ac JOIN cursos c ON c.id_curso = ac.id_curso WHERE ac.id_alumno = ? ORDER BY ac.anio, c.nombre');
    $stmt->execute([$id]);
    return $stmt->fetchAll();
}
