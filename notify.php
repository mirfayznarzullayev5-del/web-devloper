<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/authlib.php';
require_once __DIR__ . '/chatlib.php';
auth_start();
$role = ($_GET['role'] ?? $_POST['role'] ?? '') === 'admin' ? 'admin' : 'user';
$uid = preg_replace('/[^a-zA-Z0-9_\-]/', '', $_GET['uid'] ?? $_POST['uid'] ?? '');
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

if ($role === 'admin') {
    // admin session or creator
    $ok = !empty($_SESSION['admin']);
    if (!$ok && function_exists('current_user') && function_exists('is_creator')) {
        $cu = current_user();
        $ok = $cu && is_creator($cu);
    }
    if (!$ok) { echo json_encode(['ok'=>false,'items'=>[],'count'=>0]); exit; }
} else {
    $u = function_exists('current_user') ? current_user() : null;
    if (!$u) { echo json_encode(['ok'=>false,'items'=>[],'count'=>0]); exit; }
    $uid = $uid ?: (string)($u['id'] ?? '');
}

if ($action === 'read') {
    notify_mark($role, $role === 'user' ? $uid : null);
    echo json_encode(['ok'=>true,'count'=>0,'items'=>[]]);
    exit;
}

$items = notify_unread($role, $role === 'user' ? $uid : null);
echo json_encode(['ok'=>true,'count'=>count($items),'items'=>array_slice($items, 0, 20)], JSON_UNESCAPED_UNICODE);
