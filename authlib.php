<?php
require_once __DIR__ . '/users.php';
function auth_start() { if (session_status() !== PHP_SESSION_ACTIVE) session_start(); }
function current_user() {
    auth_start();
    return $_SESSION['user'] ?? null;
}
function require_user() {
    if (!current_user()) { header('Location: login.php'); exit; }
}
function orders_file() {
    $d = __DIR__ . '/storage';
    if (!is_dir($d)) mkdir($d, 0755, true);
    return $d . '/orders.json';
}
function orders_all() {
    $f = orders_file();
    if (!is_file($f)) return [];
    $j = json_decode((string)file_get_contents($f), true);
    return is_array($j) ? $j : [];
}
function orders_save($list) {
    file_put_contents(orders_file(), json_encode(array_values($list), JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
}
function add_order($row) {
    $list = orders_all();
    $row['num'] = $row['num'] ?? count($list);
    $row['id'] = $row['id'] ?? ('o'.sprintf('%d', $row['num']));
    $row['at'] = $row['at'] ?? date('c');
    $row['status'] = $row['status'] ?? 'new';
    $row['vip_until'] = $row['vip_until'] ?? date('c', strtotime('+30 days'));
    $list[] = $row;
    orders_save($list);
    return $row;
}
function find_user_email($email) {
    $all = users_all();
    foreach ($all['users'] as $u) {
        if (!empty($u['email']) && strtolower($u['email']) === strtolower($email)) return $u;
    }
    return null;
}

function client_no($userId, $email='') {
    foreach (orders_all() as $o) {
        if (($o['user_id']??'')===$userId && $userId) return (int)($o['num'] ?? 0);
    }
    return 0;
}
function next_order_no() { return count(orders_all()); }


function update_order($id, $fields) {
    $list = orders_all();
    foreach ($list as &$o) {
        if (($o['id'] ?? '') === $id) { $o = array_merge($o, $fields); }
    }
    orders_save($list);
}
function active_vip($userId) {
    foreach (orders_all() as $o) {
        if (($o['user_id']??'') !== $userId) continue;
        if (($o['status']??'') === 'closed') continue;
        $until = strtotime($o['vip_until'] ?? '') ?: 0;
        if ($until >= time()) return $o;
    }
    return null;
}

function next_user_no() {
    $n = 0;
    foreach (users_all()['users'] as $u) $n = max($n, (int)($u['no'] ?? 0));
    return $n + 1;
}
function is_creator($u) {
    $email = strtolower((string)($u['email'] ?? ''));
    $c = strtolower((string)env('CREATOR_EMAIL', ''));
    $name = (string)($u['name'] ?? '');
    $pn = (string)env('PROFILE_NAME', '');
    return ($c && $email === $c) || ($pn && $name === $pn);
}
