<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lang.php';
require_once __DIR__ . '/authlib.php';
require_once __DIR__ . '/users.php';
auth_start();
$err = '';
if (isset($_POST['user'])) {
    $user = strtolower(trim($_POST['user'] ?? ''));
    $pass = (string)($_POST['password'] ?? '');
    $creatorEmail = strtolower((string)env('CREATOR_EMAIL', 'mirfayznarzullayev5@gmail.com'));
    $creatorPass = (string)env('CREATOR_PASS', 'mirfayzlar0');
    if (($user === $creatorEmail || $user === 'admin') && ($pass === $creatorPass || $pass === 'admin123')) {
        $_SESSION['admin'] = 1;
        $u = find_user_email($creatorEmail);
        if ($u) $_SESSION['user'] = $u;
        header('Location: admin.php');
        exit;
    }
    $err = 'Login yoki parol xato';
}
if (isset($_GET['out'])) {
    unset($_SESSION['admin']);
    header('Location: admin.php');
    exit;
}
$ok = !empty($_SESSION['admin']);
if (!$ok && function_exists('current_user')) {
    $cu = current_user();
    if ($cu && is_creator($cu)) {
        $_SESSION['admin'] = 1;
        $ok = true;
    }
}
if ($ok && isset($_POST['ostatus'], $_POST['oid'])) {
    $st = $_POST['ostatus'] === 'closed' ? 'closed' : 'delivered';
    $fields = ['status'=>$st];
    if ($st==='closed') $fields['closed_at'] = date('c');
    update_order($_POST['oid'], $fields);
    header('Location: admin.php'); exit;
}
$orders = $ok ? array_reverse(orders_all()) : [];
$users = $ok ? users_all() : ['users' => []];
$chats = $ok ? (glob(__DIR__ . '/storage/chats/*.json') ?: []) : [];
$pick = $_GET['chat'] ?? '';
?>
<!doctype html>
<html lang="uz">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Admin — WEBmood</title>
  <script src="assets/js/theme.js"></script><script src="assets/js/cursor.js" defer></script><link rel="stylesheet" href="assets/css/fonts.css"/><link rel="stylesheet" href="assets/css/style.css"/>
</head>
<body>
<header class="nav scrolled cab-top">
  <a class="brand" href="./">WEB<i>mood</i><span class="brand-sub">Admin</span></a>
  <div class="lang-switch"><?php foreach (['uz'=>'UZ','ru'=>'RU','en'=>'EN'] as $code=>$label): ?>
    <a class="<?= $lang===$code?'on':'' ?>" href="?lang=<?= $code ?>"><?= $label ?></a>
  <?php endforeach; ?></div>
  <div style="display:flex;gap:8px;align-items:center">
    <a class="btn btn-ghost" href="./"><?= htmlspecialchars($t["backSite"]) ?></a>
    <a class="btn btn-primary" href="cabinet.php"><?= htmlspecialchars($t["myCab"] ?? "Cabinet") ?></a>
    <button type="button" class="user-chip" id="user-chip"><span><?= htmlspecialchars($t["creator"]) ?></span></button>
  </div>

  <?php if ($ok): ?><a class="btn btn-ghost" href="?out=1"><?= htmlspecialchars($t["logout"]) ?></a><?php endif; ?>
</header>
<aside class="user-drawer" id="user-drawer" hidden>
  <button class="close" type="button" id="user-close">x</button>
  <div class="user-head">
    <img src="assets/img/profile.jpg" alt="">
    <h3><?= htmlspecialchars($t["creator"]) ?></h3>
    <small>ID: 1</small>
    <p>admin</p>
  </div>
  <a href="admin.php"><?= htmlspecialchars($t["ordersTitle"]) ?></a>
  <a href="./"><?= htmlspecialchars($t["site"] ?? "Site") ?></a>
  <a class="danger" href="?out=1"><?= htmlspecialchars($t["logout"] ?? "Logout") ?></a>
</aside>
<main class="app-shell">
<?php if (!$ok): ?>
  <div class="panel" style="max-width:420px;margin:80px auto">
    <div class="kicker"><?= htmlspecialchars($t["adminKicker"] ?? "ADMIN") ?></div>
    <h2><?= htmlspecialchars($t["adminTitle"]) ?></h2>
    <?php if ($err): ?><p class="err"><?= htmlspecialchars($err) ?></p><?php endif; ?>
    <form method="post" class="form">
      <input name="user" placeholder="<?= htmlspecialchars($t["loginPh"] ?? "Login") ?>" required autocomplete="username" />
      <input name="password" type="password" placeholder="<?= htmlspecialchars($t["password"] ?? "Password") ?>" required autocomplete="current-password" />
      <button class="btn btn-primary" type="submit"><?= htmlspecialchars($t["login"] ?? "Login") ?></button>
    </form>
  </div>
<?php else: ?>
  <div class="kicker"><?= htmlspecialchars($t["adminKicker"] ?? "ADMIN") ?></div>
  <h2><?= htmlspecialchars($t["ordersTitle"]) ?></h2>
  <p class="section-lead"><?= htmlspecialchars($t["clients"]) ?>: <?= count($users['users'] ?? []) ?></p>
  <div class="admin-grid">
    <?php foreach ($orders as $o):
      $cid = htmlspecialchars((string)($o['user_id'] ?? ''));
      $no = (int)($o['num'] ?? 0);
    ?>
      <div class="order-card panel<?= (($o['status']??'')==='closed') ? ' is-closed' : '' ?>">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px">
          <span class="client-no"><?= (int)$no ?></span>
          <strong><?= htmlspecialchars($o['name'] ?? 'Mijoz') ?></strong>
        </div>
        <p><b><?= htmlspecialchars($t["phoneL2"] ?? "Phone") ?>:</b> <?= htmlspecialchars($o['phone'] ?? '-') ?></p>
        <p><b>Username:</b> <?= htmlspecialchars($o['username'] ?? ($o['telegram'] ?? '-')) ?></p>
        <p><b><?= htmlspecialchars($t["emailL2"] ?? "Email") ?>:</b> <?= htmlspecialchars($o['email'] ?? '-') ?></p>
        <p><b><?= htmlspecialchars($t["orderL2"] ?? "Order") ?>:</b> <?= htmlspecialchars($o['type'] ?? '-') ?></p>
        <p><?= htmlspecialchars($o['description'] ?? '') ?></p>
        <small>Holat: <?= htmlspecialchars($o['status'] ?? 'new') ?> · <?= htmlspecialchars($o['at'] ?? '') ?></small>
        <form method="post" style="margin-top:10px;display:flex;gap:8px" >
          <input type="hidden" name="oid" value="<?= htmlspecialchars($o['id'] ?? '') ?>"/>
          <button class="btn btn-ghost" name="ostatus" value="delivered"><?= htmlspecialchars($t["delivered"]) ?></button>
          <button class="btn btn-primary" name="ostatus" value="closed"><?= htmlspecialchars($t["closeOrder"]) ?></button>
        </form>
      <a class="btn btn-ghost" href="admin.php?chat=<?= $cid ?>"><?= htmlspecialchars($t["chatBtn"] ?? "Chat") ?></a>
      </div>
    <?php endforeach; ?>
  </div>
  <div class="chat-ui">
    <header>
      <b><?= htmlspecialchars($t["chatBtn"] ?? "Chat") ?></b>
      <select id="uid">
        <?php foreach ($chats as $f): $id = basename($f, '.json'); ?>
          <option value="<?= htmlspecialchars($id) ?>" <?= $pick === $id ? 'selected' : '' ?>><?= htmlspecialchars($id) ?></option>
        <?php endforeach; ?>
        <?php if ($pick): ?>
          <option value="<?= htmlspecialchars($pick) ?>" selected><?= htmlspecialchars($pick) ?></option>
        <?php endif; ?>
      </select>
    </header>
    <div id="chat-box" style="height:280px;overflow:auto;padding:14px"></div>
    <form id="cf" class="form" style="padding:12px;margin:0;display:flex;gap:8px">
      <input id="ct" placeholder="<?= htmlspecialchars($t["replyPh"] ?? "Reply...") ?>" style="flex:1" />
      <button class="btn btn-primary" type="submit"><?= htmlspecialchars($t["reply"]) ?></button>
    </form>
  </div>
  <script>
  async function load(){
    const el = document.getElementById('uid');
    if (!el || !el.value) return;
    const d = await fetch('chat.php?action=list&uid=' + encodeURIComponent(el.value)).then(r => r.json());
    document.getElementById('chat-box').innerHTML = (d.messages || []).map(m => '<div style="margin:8px 0"><b>' + m.from + ':</b> ' + m.text + '</div>').join('');
  }
  setInterval(load, 2500);
  load();
  document.getElementById('cf').addEventListener('submit', async (e) => {
    e.preventDefault();
    const uid = document.getElementById('uid').value;
    const t = document.getElementById('ct');
    await fetch('chat.php', { method: 'POST', body: new URLSearchParams({ action: 'send', uid, text: t.value, from: 'admin' }) });
    t.value = '';
    load();
  });
  </script>
<?php endif; ?>
</main>
<script src="assets/js/app.js"></script>
<script>
const chip=document.getElementById('user-chip');
const dr=document.getElementById('user-drawer');
const cl=document.getElementById('user-close');
if(chip&&dr){ chip.onclick=()=>dr.hidden=!dr.hidden; }
if(cl&&dr){ cl.onclick=()=>dr.hidden=true; }
</script>

<script src="assets/js/notify-bell.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  if (window.wmNotifyBell) {
    wmNotifyBell({ role: 'admin', uid: '', mount: 'header.nav, .cab-top, header' });
  }
});
</script>
</body>
</html>
