<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/users.php';

$action = $_GET['action'] ?? $_POST['action'] ?? 'me';
$data = users_all();

if ($action === 'me') {
    $ok = !empty($_COOKIE['wm_reg']);
    echo json_encode(['ok' => $ok, 'via' => $_COOKIE['wm_via'] ?? '']);
    exit;
}

if ($action === 'email') {
    $email = trim($_POST['email'] ?? '');
    $name = trim($_POST['name'] ?? 'Mijoz');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['ok'=>false,'message'=>'Email noto\'g\'ri']);
        exit;
    }
    $data['users'][] = ['via'=>'web','email'=>$email,'name'=>$name,'at'=>date('c')];
    users_save($data);
    setcookie('wm_reg', '1', time()+365*86400, '/');
    $uid = 'u'.bin2hex(random_bytes(4));
    setcookie('wm_uid', $uid, time()+365*86400, '/');
    setcookie('wm_name', $name, time()+365*86400, '/');
    setcookie('wm_email', $email, time()+365*86400, '/');
    setcookie('wm_via', 'web', time()+365*86400, '/');
    $sf = __DIR__ . '/storage/stats.json';
    $st = is_file($sf) ? json_decode((string)file_get_contents($sf), true) : [];
    if (!is_array($st)) $st = [];
    $st['visits'] = (int)($st['visits'] ?? 0) + 1;
    $st['web_regs'] = (int)($st['web_regs'] ?? 0) + 1;
    file_put_contents($sf, json_encode($st, JSON_UNESCAPED_UNICODE));
    require_once __DIR__ . '/telegram.php';
    telegram_notify_admin([
        'name' => $name,
        'phone' => '-',
        'telegram' => '-',
        'type' => 'WEB ROYXAT',
        'budget' => '-',
        'description' => "Saytdan email orqali ro'yxatdan o'tdi\nEmail: {$email}",
    ]);
    echo json_encode(['ok'=>true,'via'=>'web','message'=>'Saytdan ro\'yxatdan o\'tdingiz']);
    exit;
}

if ($action === 'telegram_init') {
    $token = bin2hex(random_bytes(6));
    $data['tokens'][$token] = ['ok'=>false,'at'=>time()];
    users_save($data);
    $bot = $TELEGRAM['botUsername'] ?? 'WEB_mood_bot';
    echo json_encode(['ok'=>true,'token'=>$token,'url'=>'https://t.me/'.$bot.'?start=web_'.$token]);
    exit;
}

if ($action === 'telegram_status') {
    $token = preg_replace('/[^a-zA-Z0-9]/','', $_GET['token'] ?? '');
    $row = $data['tokens'][$token] ?? null;
    echo json_encode(['ok'=> !empty($row['ok']), 'ready'=>isset($row)]);
    exit;
}

echo json_encode(['ok'=>false]);
