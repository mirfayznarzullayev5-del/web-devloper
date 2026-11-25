<?php
require_once __DIR__ . '/config.php';

function telegram_api(string $method, array $params = []): array
{
    global $TELEGRAM;
    $token = $TELEGRAM['botToken'] ?? '';
    if ($token === '' || $token === 'PUT_BOT_TOKEN_HERE') {
        return ['ok' => false, 'description' => 'Bot token not set'];
    }
    $url = 'https://api.telegram.org/bot' . $token . '/' . $method;
    $raw = telegram_http($url, $params, $method);
    if ($raw === false) return ['ok' => false, 'description' => 'HTTP request failed'];
    $json = json_decode($raw, true);
    return is_array($json) ? $json : ['ok' => false, 'description' => 'Bad response'];
}

function telegram_find_curl(): ?string
{
    foreach (['curl.exe', 'curl', 'C:\\Windows\\System32\\curl.exe'] as $bin) {
        $out = [];
        @exec($bin . ' --version', $out);
        if (!empty($out)) return $bin;
    }
    return null;
}

function telegram_http(string $url, array $params, string $method = 'sendMessage')
{
    $photo = $params['photo'] ?? null;
    $isFile = is_string($photo) && is_file($photo);

    if (function_exists('curl_init')) {
        $payload = $params;
        if ($isFile && class_exists('CURLFile')) {
            $payload['photo'] = new CURLFile($photo, 'image/png', 'logo.png');
        }
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $isFile ? $payload : json_encode($params, JSON_UNESCAPED_UNICODE),
            CURLOPT_HTTPHEADER => $isFile ? [] : ['Content-Type: application/json'],
            CURLOPT_TIMEOUT => 40,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $raw = curl_exec($ch);
        curl_close($ch);
        if ($raw) return $raw;
    }

    $curl = telegram_find_curl();
    if ($curl) {
        if ($isFile) {
            $cmd = $curl . ' -s -k --max-time 40 -X POST ' . escapeshellarg($url);
            $cmd .= ' -F ' . escapeshellarg('chat_id=' . ($params['chat_id'] ?? ''));
            $cmd .= ' -F ' . escapeshellarg('photo=@' . $photo);
            if (!empty($params['caption'])) {
                $cap = str_replace(["\r\n", "\n"], " / ", (string)$params['caption']);
                $cmd .= ' -F ' . escapeshellarg('caption=' . $cap);
            }
            if (!empty($params['parse_mode'])) $cmd .= ' -F ' . escapeshellarg('parse_mode=' . $params['parse_mode']);
            $out = [];
            exec($cmd, $out);
            $raw = implode("\n", $out);
            if ($raw) return $raw;
        } else {
            $tmp = tempnam(sys_get_temp_dir(), 'tgj');
            file_put_contents($tmp, json_encode($params, JSON_UNESCAPED_UNICODE));
            $cmd = $curl . ' -s -k --max-time 40 -X POST ' . escapeshellarg($url) .
                ' -H "Content-Type: application/json" --data-binary @' . escapeshellarg($tmp);
            $out = [];
            exec($cmd, $out);
            @unlink($tmp);
            $raw = implode("\n", $out);
            if ($raw) return $raw;
        }
    }

    $tmp = tempnam(sys_get_temp_dir(), 'tgj');
    file_put_contents($tmp, json_encode($params, JSON_UNESCAPED_UNICODE));
    $tmpPs = str_replace('\\', '/', $tmp);
    $ps = '$r = Invoke-WebRequest -Uri \'' . $url . '\' -Method POST -ContentType \'application/json; charset=utf-8\' -InFile \'' . $tmpPs . '\' -UseBasicParsing; $r.Content';
    $out = [];
    exec('powershell -NoProfile -Command ' . escapeshellarg($ps), $out);
    @unlink($tmp);
    $raw = implode("\n", $out);
    return $raw !== '' ? $raw : false;
}

function telegram_send_photo(string $chatId, string $photoPath, string $caption, array $extra = []): array
{
    if (is_file($photoPath)) {
        $params = array_merge(['chat_id' => $chatId, 'photo' => $photoPath, 'caption' => $caption], $extra);
        $res = telegram_api('sendPhoto', $params);
        if (!empty($res['ok'])) return $res;
    }
    $msg = array_merge(['chat_id' => $chatId, 'text' => $caption], $extra);
    return telegram_api('sendMessage', $msg);
}

function telegram_notify_admin(array $data): array
{
    global $TELEGRAM;
    $chatId = $TELEGRAM['adminChatId'] ?? '';
    if ($chatId === '' || $chatId === 'PUT_ADMIN_CHAT_ID_HERE') {
        return ['ok' => false, 'description' => 'Admin chat id not set'];
    }
    $text =
        "WEBmood — YANGI BUYURTMA\n" .
        "====================\n" .
        "Ism: " . ($data['name'] ?? '-') . "\n" .
        "Telefon: " . ($data['phone'] ?? '-') . "\n" .
        "Telegram: " . ($data['telegram'] ?: '-') . "\n" .
        "Tur: " . ($data['type'] ?? '-') . "\n" .
        "Byudjet: " . ($data['budget'] ?: '-') . "\n" .
        "Tavsif:\n" . ($data['description'] ?? '-') . "\n" .
        "--------------------\n" .
        date('Y-m-d H:i:s');
    return telegram_api('sendMessage', ['chat_id' => $chatId, 'text' => $text]);
}
