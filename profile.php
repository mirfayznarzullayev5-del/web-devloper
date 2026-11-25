<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lang.php';
require_once __DIR__ . '/users.php';
$uid = $_COOKIE['wm_uid'] ?? '';
if (empty($_COOKIE['wm_reg'])) { header('Location: ./'); exit; }
$name = $_COOKIE['wm_name'] ?? 'Mijoz';
$email = $_COOKIE['wm_email'] ?? '';
$via = $_COOKIE['wm_via'] ?? 'web';
$orders = [];
$log = __DIR__ . '/storage/requests.log';
if (is_file($log)) {
  foreach (file($log) as $line) {
    $p = explode("\t", $line, 2);
    $j = json_decode($p[1] ?? '', true);
    if (!is_array($j)) continue;
    if (($j['uid'] ?? '') === $uid || ($email && ($j['email'] ?? '') === $email) || ($j['name'] ?? '') === $name) $orders[] = $j;
  }
}
?><!doctype html><html lang="uz"><head><meta charset="utf-8"/><meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>Profil — WEBmood</title>
<script src="assets/js/theme.js"></script><script src="assets/js/cursor.js" defer></script><link rel="stylesheet" href="assets/css/fonts.css"/><link rel="stylesheet" href="assets/css/style.css"/>
</head>
<body>

<header class="nav scrolled"><a class="brand" href="./">WEB<i>mood</i></a><div class="nav-tools"><a class="btn btn-ghost" href="./">Sayt</a></div></header>
<main class="dash">
  <div class="kicker">ACCOUNT</div>
  <h2><?= htmlspecialchars($name) ?></h2>
  <div class="dash-card">
    <p>Email: <?= htmlspecialchars($email ?: '-') ?></p>
    <p>Kirish: <?= htmlspecialchars($via) ?></p>
    <p>ID: <?= htmlspecialchars($uid ?: '-') ?></p>
  </div>
  <h3>Mening zakazlarim</h3>
  <?php if (!$orders): ?><div class="dash-card">Hali zakaz yo'q.</div><?php endif; ?>
  <?php foreach (array_reverse($orders) as $o): ?>
    <div class="dash-card">
      <b><?= htmlspecialchars($o['type'] ?? 'Zakaz') ?></b>
      <p><?= htmlspecialchars($o['description'] ?? '') ?></p>
      <small><?= htmlspecialchars(($o['phone'] ?? '').' · '.($o['budget'] ?? '')) ?></small>
    </div>
  <?php endforeach; ?>
  <h3>Yordam chat</h3>
  <div class="dash-card">
    <div class="chat-box" id="chat-box"></div>
    <form class="form" id="chat-form" style="margin-top:10px">
      <input id="chat-text" placeholder="<?= htmlspecialchars($t["msgPh"] ?? "Message...") ?>" autocomplete="off" />
      <button class="btn btn-primary" type="submit">Yuborish</button>
    </form>
  </div>
</main>
<script>
const uid = <?= json_encode($uid) ?>;
async function load(){
  const d = await fetch('chat.php?action=list&uid='+encodeURIComponent(uid)).then(r=>r.json());
  const box = document.getElementById('chat-box');
  box.innerHTML = (d.messages||[]).map(m=>`<div class="chat-line ${m.from==='admin'?'ad':'me'}"><b>${m.from}:</b> ${m.text}</div>`).join('');
  box.scrollTop = box.scrollHeight;
}
setInterval(load, 2500); load();
document.getElementById('chat-form').onsubmit = async (e)=>{
  e.preventDefault();
  const t = document.getElementById('chat-text');
  if(!t.value.trim()) return;
  await fetch('chat.php', {method:'POST', body: new URLSearchParams({action:'send', uid, text:t.value, from:'user'})});
  t.value=''; load();
};
</script>
  <script src="assets/js/app.js"></script>

<script src="assets/js/notify-bell.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  if (window.wmNotifyBell) wmNotifyBell({ role: 'user', uid: '', mount: 'header.nav, header' });
});
</script>
</body></html>
