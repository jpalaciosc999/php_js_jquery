<?php
require_once __DIR__ . '/app.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('index.php');
validar_csrf();
$_SESSION = [];
session_regenerate_id(true);
redirect('index.php');
