<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/authlib.php';
auth_start();
$id = preg_replace('/[^a-zA-Z0-9_\-]/','', $_GET['id'] ?? '');
$bot = preg_replace('/[^a-zA-Z0-9_]/','', $_GET['bot'] ?? ($TELEGRAM['botUsername'] ?? 'WEB_mood_bot'));
require_once __DIR__ . '/users.php';
require_once __DIR__ . '/telegram.php';
$adminId = (string)($TELEGRAM['adminChatId'] ?? '');
# send accept to mapped username once
foreach (users_all()['users'] as $u) {
    if (($u['id']??'')===$id) {
        $to = tgmap_get($u['username']??'');
        if ($to && $to !== $adminId && empty($_SESSION['accept_sent_'.$id])) {
            $_SESSION['accept_sent_'.$id]=1;
            $kb = json_encode(['inline_keyboard'=>[[['text'=>'Saytga ruxsat','callback_data'=>'accept_'.$id]]]]);
            telegram_api('sendMessage', ['chat_id'=>$to,'text'=>"Saytdan ro'yxat. Saytga ruxsat ni bosing.",'reply_markup'=>$kb,'parse_mode'=>'HTML']);
        }
    }
}
if (isset($_GET['check'])) {
    header('Content-Type: application/json');
    $ok = false;
    foreach (users_all()['users'] as $u) {
        if (($u['id']??'')===$id && empty($u['pending']) && !empty($u['tg_chat'])) $ok = true;
    }
    echo json_encode(['ok'=>$ok]);
    exit;
}
?>
<!doctype html><html lang="uz"><head><meta charset="utf-8"/><meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>Bot</title><script src="assets/js/theme.js"></script><script src="assets/js/cursor.js" defer></script><link rel="stylesheet" href="assets/css/fonts.css"/><link rel="stylesheet" href="assets/css/style.css"/></head>
<body>
<main class="app-shell">
  <div class="panel" style="max-width:420px;margin:80px auto;text-align:center">
    <h2><?= htmlspecialchars($t["waitTitle"] ?? "Telegram bot") ?></h2>
    <p><?= htmlspecialchars($t["waitText"] ?? "Open bot") ?> va <b>Saytga ruxsat</b> ni bosing. Keyin sayt ochiladi.</p>
    <a class="btn btn-primary" href="https://t.me/<?= htmlspecialchars($bot) ?>?start=reg_<?= htmlspecialchars($id) ?>" target="_blank">Botni ochish</a>
    <p id="st" style="margin-top:12px;color:var(--muted)">Kutilmoqda...</p>
  </div>
</main>
<script>
const id = <?= json_encode($id) ?>;
setInterval(async ()=>{
  const d = await fetch('wait-bot.php?check=1&id='+encodeURIComponent(id)).then(r=>r.json());
  if (d.ok) location.href = 'cabinet.php';
}, 2000);
</script>

<script src="assets/js/notify-bell.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  if (window.wmNotifyBell) wmNotifyBell({ role: 'user', uid: '', mount: 'header.nav, header' });
});
</script>
</body></html>
