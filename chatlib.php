<?php
function chat_dir(): string {
    $d = __DIR__ . '/storage/chats';
    if (!is_dir($d)) mkdir($d, 0755, true);
    return $d;
}
function chat_file(string $uid): string {
    $uid = preg_replace('/[^a-zA-Z0-9_\-]/', '', $uid);
    return chat_dir() . '/' . $uid . '.json';
}
function chat_load(string $uid): array {
    $f = chat_file($uid);
    if (!is_file($f)) return [];
    $j = json_decode((string)file_get_contents($f), true);
    return is_array($j) ? $j : [];
}
function chat_save(string $uid, array $messages): void {
    file_put_contents(chat_file($uid), json_encode(array_values($messages), JSON_UNESCAPED_UNICODE));
}
function chat_append(string $uid, string $from, string $text, array $meta = []): array {
    $uid = preg_replace('/[^a-zA-Z0-9_\-]/', '', $uid);
    if ($uid === '' || trim($text) === '') return chat_load($uid);
    $messages = chat_load($uid);
    $row = array_merge([
        'from' => $from === 'admin' ? 'admin' : 'user',
        'text' => trim($text),
        'at' => date('c'),
        'src' => $meta['src'] ?? 'web',
    ], $meta);
    $messages[] = $row;
    chat_save($uid, $messages);
    notify_push($uid, $row);
    return $messages;
}
function notify_file(): string {
    $d = __DIR__ . '/storage';
    if (!is_dir($d)) mkdir($d, 0755, true);
    return $d . '/notifications.json';
}
function notify_all(): array {
    $f = notify_file();
    if (!is_file($f)) return [];
    $j = json_decode((string)file_get_contents($f), true);
    return is_array($j) ? $j : [];
}
function notify_save(array $list): void {
    // keep last 200
    if (count($list) > 200) $list = array_slice($list, -200);
    file_put_contents(notify_file(), json_encode(array_values($list), JSON_UNESCAPED_UNICODE));
}
function notify_push(string $uid, array $row): void {
    $list = notify_all();
    $list[] = [
        'id' => uniqid('n', true),
        'uid' => $uid,
        'from' => $row['from'] ?? 'user',
        'text' => mb_substr((string)($row['text'] ?? ''), 0, 120),
        'at' => $row['at'] ?? date('c'),
        'read_admin' => ($row['from'] ?? '') === 'admin',
        'read_user' => ($row['from'] ?? '') === 'user',
        'src' => $row['src'] ?? 'web',
    ];
    notify_save($list);
}
function notify_mark(string $role, ?string $uid = null): void {
    $list = notify_all();
    foreach ($list as &$n) {
        if ($role === 'admin') {
            if ($uid === null || ($n['uid'] ?? '') === $uid) $n['read_admin'] = true;
        } else {
            if ($uid !== null && ($n['uid'] ?? '') === $uid) $n['read_user'] = true;
        }
    }
    unset($n);
    notify_save($list);
}
function notify_unread(string $role, ?string $uid = null): array {
    $out = [];
    foreach (notify_all() as $n) {
        if ($role === 'admin') {
            if (empty($n['read_admin']) && ($n['from'] ?? '') === 'user') $out[] = $n;
        } else {
            if ($uid && ($n['uid'] ?? '') === $uid && empty($n['read_user']) && ($n['from'] ?? '') === 'admin') $out[] = $n;
        }
    }
    return array_reverse($out);
}
function chat_uid_by_tg(string $chatId): string {
    require_once __DIR__ . '/users.php';
    $chatId = (string)$chatId;
    $all = users_all();
    foreach ($all['users'] as $u) {
        if ((string)($u['tg_chat'] ?? '') === $chatId && !empty($u['id'])) {
            return (string)$u['id'];
        }
    }
    // orders
    if (function_exists('orders_all')) {
        foreach (orders_all() as $o) {
            if ((string)($o['tg_chat'] ?? '') === $chatId && !empty($o['user_id'])) {
                return (string)$o['user_id'];
            }
        }
    }
    return 'tg' . preg_replace('/[^0-9\-]/', '', $chatId);
}
