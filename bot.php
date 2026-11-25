<?php
require_once __DIR__ . '/bot_handler.php';

$input = file_get_contents('php://input');
$update = json_decode($input, true);
if (is_array($update)) {
    process_update($update);
    http_response_code(200);
    echo 'OK';
    exit;
}

header('Content-Type: text/plain; charset=utf-8');
echo "WEBmood bot\n";
echo "Lokal ishlatish: php poll.php\n";
echo "Hostingda webhook: bot.php ni Telegram webhook qiling.\n";
