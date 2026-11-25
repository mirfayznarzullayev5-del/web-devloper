<?php
header('Content-Type: application/json; charset=utf-8');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['ok' => false, 'message' => 'Method not allowed']);
  exit;
}

$name = trim($_POST['name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$telegram = trim($_POST['telegram'] ?? '');
$type = trim($_POST['type'] ?? '');
$budget = trim($_POST['budget'] ?? '');
$description = trim($_POST['description'] ?? '');

if (empty($_COOKIE['wm_reg'])) {
  $pid = bin2hex(random_bytes(4));
  require_once __DIR__ . '/users.php';
  file_put_contents(pending_file($pid), json_encode($_POST, JSON_UNESCAPED_UNICODE));
  setcookie('wm_pending', $pid, time()+86400, '/');
  echo json_encode(['ok'=>false,'need_register'=>true,'message'=>'Avval ro\'yxatdan o\'ting']);
  exit;
}

if ($name === '' || $phone === '' || $description === '') {
  echo json_encode(['ok' => false, 'message' => 'Please complete required fields.']);
  exit;
}
if (!preg_match('/^[+\d][\d\s\-]{7,}$/', $phone)) {
  echo json_encode(['ok' => false, 'message' => 'Enter a valid phone number.']);
  exit;
}

$dir = __DIR__ . '/storage';
if (!is_dir($dir)) mkdir($dir, 0755, true);
$line = date('c') . "\t" . json_encode([
  'name' => $name, 'phone' => $phone, 'telegram' => $telegram,
  'type' => $type, 'budget' => $budget, 'description' => $description,
  'uid' => $_COOKIE['wm_uid'] ?? '',
  'email' => $_COOKIE['wm_email'] ?? '',
], JSON_UNESCAPED_UNICODE) . PHP_EOL;
file_put_contents($dir . '/requests.log', $line, FILE_APPEND);

require_once __DIR__ . '/telegram.php';
require_once __DIR__ . '/authlib.php';
add_order(['name'=>$name,'phone'=>$phone,'type'=>$type,'budget'=>$budget,'description'=>$description,'source'=>'web','user_id'=>$_COOKIE['wm_uid']??'']);
$tg = telegram_notify_admin([
  'name' => $name,
  'phone' => $phone,
  'telegram' => $telegram,
  'type' => $type,
  'budget' => $budget,
  'description' => $description,
  'uid' => $_COOKIE['wm_uid'] ?? '',
  'email' => $_COOKIE['wm_email'] ?? '',
]);
if (empty($tg['ok'])) {
  file_put_contents($dir . '/telegram.log', date('c') . "\t" . ($tg['description'] ?? 'fail') . PHP_EOL, FILE_APPEND);
}

echo json_encode(['ok' => true, 'message' => 'Your project request has been sent successfully.']);
