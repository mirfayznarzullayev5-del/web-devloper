<?php
require_once __DIR__ . '/bot_handler.php';

echo "WEBmood bot polling... Ctrl+C bilan to'xtating.\n";
$offset = 0;
while (true) {
    $res = telegram_api('getUpdates', [
        'offset' => $offset,
        'timeout' => 25,
    ]);
    if (empty($res['ok'])) {
        echo ($res['description'] ?? 'error') . PHP_EOL;
        sleep(3);
        continue;
    }
    foreach (($res['result'] ?? []) as $update) {
        $offset = ((int)($update['update_id'] ?? 0)) + 1;
        process_update($update);
        echo "update " . ($update['update_id'] ?? '') . PHP_EOL;
    }
}
