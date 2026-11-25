<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/authlib.php';
require_once __DIR__ . '/telegram.php';
require_once __DIR__ . '/chatlib.php';
require_once __DIR__ . '/users.php';
if (function_exists('orders_all') === false && is_file(__DIR__.'/config.php')) {
    // orders_all may live in config/auth
}
$uid = preg_replace('/[^a-zA-Z0-9_\-]/', '', $_GET['uid'] ?? $_POST['uid'] ?? '');
if ($uid === '') { echo json_encode(['ok'=>false,'messages'=>[]]); exit; }
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';
if ($action === 'send') {
    $text = trim($_POST['text'] ?? '');
    $from = ($_POST['from'] ?? 'user') === 'admin' ? 'admin' : 'user';
    if ($text !== '') {
        chat_append($uid, $from, $text, ['src' => 'web']);
        if ($from === 'admin') {
            $tg = null;
            if (strpos($uid, 'tg') === 0) $tg = substr($uid, 2);
            $all = users_all();
            foreach ($all['users'] as $u) {
                if (($u['id'] ?? '') === $uid && !empty($u['tg_chat'])) { $tg = $u['tg_chat']; break; }
            }
            if (!$tg && function_exists('orders_all')) {
                foreach (orders_all() as $o) {
                    if (($o['user_id'] ?? '') === $uid && !empty($o['tg_chat'])) { $tg = $o['tg_chat']; break; }
                }
            }
            if ($tg) {
                telegram_api('sendMessage', [
                    'chat_id' => $tg,
                    'text' => "Admin javobi:\n" . $text,
                ]);
            }
        }
    }
}
echo json_encode(['ok'=>true,'messages'=>chat_load($uid)], JSON_UNESCAPED_UNICODE);
