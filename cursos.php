<?php
require_once __DIR__ . '/app.php';
requiere_login();
require_once __DIR__ . '/db.php';
header('Content-Type: application/json; charset=utf-8');
$term = is_string($_GET['term'] ?? null) ? trim($_GET['term']) : '';
if ($term === '' || mb_strlen($term) > 50) { echo '[]'; exit; }
// Buscar literalmente los signos % y _ escritos por el usuario.
$prefix = strtr($term, ['!' => '!!', '%' => '!%', '_' => '!_']) . '%';
$stmt = $pdo->prepare("SELECT nombre FROM cursos WHERE nombre LIKE ? ESCAPE '!' ORDER BY nombre LIMIT 15");
$stmt->execute([$prefix]);
echo json_encode($stmt->fetchAll(PDO::FETCH_COLUMN), JSON_UNESCAPED_UNICODE);
