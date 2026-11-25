<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/advisor.php';
$q = trim($_POST['q'] ?? $_GET['q'] ?? '');
if ($q === '') { echo json_encode(['ok'=>false,'a'=>'Savol yozing.']); exit; }
echo json_encode(['ok'=>true,'a'=>advisor_reply($q)], JSON_UNESCAPED_UNICODE);
