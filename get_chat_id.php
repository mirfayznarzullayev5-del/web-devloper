<?php
require_once __DIR__ . '/telegram.php';

$res = telegram_api('getUpdates', []);
if (empty($res['ok'])) {
    echo "Xato: " . ($res['description'] ?? 'unknown') . PHP_EOL;
    echo "Avval config.php ga to'g'ri botToken yozing." . PHP_EOL;
    exit(1);
}

$found = false;
foreach (($res['result'] ?? []) as $update) {
    $msg = $update['message'] ?? null;
    if (!$msg) continue;
    $chat = $msg['chat'] ?? [];
    echo "Chat ID: " . ($chat['id'] ?? '-') . PHP_EOL;
    echo "Name: " . trim(($chat['first_name'] ?? '') . ' ' . ($chat['username'] ?? '')) . PHP_EOL;
    echo "Text: " . ($msg['text'] ?? '') . PHP_EOL;
    echo "----" . PHP_EOL;
    $found = true;
}

if (!$found) {
    echo "Hali update yo'q. Botni ochib /start yuboring, keyin bu skriptni qayta ishga tushiring." . PHP_EOL;
}
