<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/config.php';

function stats_file(): string {
    $dir = __DIR__ . '/storage';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    return $dir . '/stats.json';
}
function stats_load(): array {
    $f = stats_file();
    if (!is_file($f)) return ['visits' => 0, 'bots' => 0, 'bot_ids' => []];
    $j = json_decode((string)file_get_contents($f), true);
    return is_array($j) ? $j + ['visits'=>0,'bots'=>0,'bot_ids'=>[]] : ['visits'=>0,'bots'=>0,'bot_ids'=>[]];
}
function stats_save(array $s): void {
    file_put_contents(stats_file(), json_encode($s, JSON_UNESCAPED_UNICODE));
}

$action = $_GET['action'] ?? $_POST['action'] ?? 'get';
$s = stats_load();

if ($action === 'visit') {
    if (empty($_COOKIE['wm_visit'])) {
        $s['visits'] = (int)$s['visits'] + 1;
        stats_save($s);
        if (!headers_sent()) setcookie('wm_visit', '1', time() + 60 * 60 * 24 * 365, '/');
    }
}

echo json_encode([
    'ok' => true,
    'visits' => (int)($s['visits'] ?? 0),
    'bots' => (int)($s['bots'] ?? count($s['bot_ids'] ?? [])),
], JSON_UNESCAPED_UNICODE);
