<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lang.php';
require_once __DIR__ . '/authlib.php';
require_user();
$u = current_user();
if (time() - (int)($_SESSION['last_ping'] ?? 0) > 60) {
  $_SESSION['last_ping'] = time();
  $chat = $u['tg_chat'] ?? '';
  if (!$chat) {
    foreach (users_all()['users'] as $uu) {
      if (($uu['id']??'')===($u['id']??'')) $chat = $uu['tg_chat'] ?? '';
    }
  }
  $adminId = (string)($TELEGRAM['adminChatId'] ?? env('TELEGRAM_ADMIN_CHAT_ID',''));
  if ($chat && $chat !== $adminId) {
    require_once __DIR__ . '/telegram.php';
    telegram_api('sendMessage', [
      'chat_id' => $chat,
      'text' => "Saytga kirdingiz: ".$u['name']."\nSana: ".date('Y-m-d H:i:s'),
    ]);
  }
}

$tab = $_GET['tab'] ?? 'home';
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['do']??'')==='close') {
  $oid = $_POST['oid'] ?? '';
  foreach (orders_all() as $o) {
    if (($o['id']??'')===$oid && (($o['user_id']??'')===($u['id']??'')) && ($o['status']??'')==='delivered') {
      update_order($oid, ['status'=>'closed','closed_at'=>date('c')]);
    }
  }
  header('Location: cabinet.php?tab=orders'); exit;
}
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['do']??'')==='order') {
  add_order([
    'user_id'=>$u['id'],'name'=>$u['name'],'email'=>$u['email']??'',
    'phone'=>$_POST['phone']??'','username'=>$_POST['username']??'',
    'type'=>$_POST['type']??'','budget'=>$_POST['budget']??'',
    'description'=>$_POST['description']??'','source'=>'cabinet'
  ]);
  require_once __DIR__ . '/telegram.php';
  telegram_notify_admin(['name'=>$u['name'],'phone'=>$_POST['phone']??'-','telegram'=>$u['email']??'-','type'=>$_POST['type']??'-','budget'=>$_POST['budget']??'-','description'=>$_POST['description']??'-']);
  header('Location: cabinet.php?tab=orders&ok=1'); exit;
}
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['do']??'')==='avatar' && !empty($_FILES['avatar']['tmp_name'])) {
  $dir = __DIR__.'/storage/avatars'; if(!is_dir($dir)) mkdir($dir,0755,true);
  $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION) ?: 'jpg';
  $fn = $u['id'].'.'.preg_replace('/[^a-z0-9]/i','',$ext);
  move_uploaded_file($_FILES['avatar']['tmp_name'], $dir.'/'.$fn);
  $all = users_all();
  foreach ($all['users'] as &$x) if (($x['id']??'')===$u['id']) $x['avatar']='storage/avatars/'.$fn;
  users_save($all);
  $_SESSION['user']['avatar']='storage/avatars/'.$fn;
  header('Location: cabinet.php?tab=profile'); exit;
}
$allOrders = orders_all();
$my = is_creator($u) ? $allOrders : array_values(array_filter($allOrders, fn($o)=>($o['user_id']??'')===($u['id']??'') || ($o['email']??'')===($u['email']??'')));
$allUsersN = count(users_all()['users'] ?? []);
$stf = __DIR__.'/storage/stats.json';
$st = is_file($stf) ? json_decode((string)file_get_contents($stf), true) : [];
if (!is_array($st)) $st = [];
$webN = (int)($st['web_regs'] ?? 0);
$botN = (int)($st['bots'] ?? 0);
$botWebN = (int)($st['bot_web_regs'] ?? 0);
$visitN = (int)($st['visits'] ?? 0);


$av = $u['avatar'] ?? '';
if (empty($u['no'])) {
  $all = users_all();
  foreach ($all['users'] as $i=>&$x) {
    if (empty($x['no'])) $x['no'] = $i+1;
    if (($x['id']??'')===($u['id']??'') || (($x['email']??'')===($u['email']??'') && $u['email'])) $u['no']=$x['no'];
  }
  users_save($all);
  $_SESSION['user']=$u;
}
$role = is_creator($u) ? ($t['creator'] ?? 'Sozdatel') : ($t['client'] ?? 'Mijoz');
$avatarSrc = ($av && $av !== '') ? $av : (is_creator($u) ? 'assets/img/profile.jpg' : 'assets/img/avatar-default.svg');


?><!doctype html><html lang="<?= htmlspecialchars($lang ?? 'uz') ?>"><head><meta charset="utf-8"/><meta name="viewport" content="width=device-width,initial-scale=1"/><title>Kabinet</title><script src="assets/js/theme.js"></script><script src="assets/js/cursor.js" defer></script><link rel="stylesheet" href="assets/css/fonts.css"/><link rel="stylesheet" href="assets/css/style.css"/></head>
<body>
<header class="nav scrolled cab-top">
  <a class="brand" href="./">WEB<i>mood</i></a>
  <div class="lang-switch">
    <?php foreach (['uz'=>'UZ','ru'=>'RU','en'=>'EN'] as $code=>$label): ?>
      <a class="<?= $lang===$code?'on':'' ?>" href="?tab=<?= htmlspecialchars($tab) ?>&lang=<?= $code ?>"><?= $label ?></a>
    <?php endforeach; ?>
  </div>
  <button type="button" class="user-chip" id="user-chip">
    <img src="<?= htmlspecialchars($avatarSrc) ?>" alt="">
    <span><?= htmlspecialchars($u['name']??'') ?></span>
  </button>
</header>
<aside class="user-drawer" id="user-drawer" hidden>
  <button class="close" type="button" id="user-close">x</button>
  <div class="user-head">
    <img src="<?= htmlspecialchars($avatarSrc) ?>" alt="">
    <h3><?= htmlspecialchars($u['name']??'') ?></h3>
    <small>ID: <?= (int)($u['no']??1) ?></small>
    <p><?= htmlspecialchars($role) ?></p>
    <p><?= htmlspecialchars($u['email']??'') ?></p>
  </div>
  <a href="cabinet.php?tab=profile"><?= htmlspecialchars($t["settings"]) ?></a>
  <a href="cabinet.php?tab=orders"><?= htmlspecialchars($t["orderHistory"]) ?></a>
  <a href="cabinet.php?tab=chat"><?= htmlspecialchars($t["tickets"]) ?></a>
  <a class="danger" href="logout.php"><?= htmlspecialchars($t["logout"]) ?></a>
</aside>
<main class="app-shell">
  <div class="cab-hero">
    <h1><?= htmlspecialchars($t["hello"]) ?>, <span class="name"><?= htmlspecialchars($u['name']??'Mijoz') ?></span>!</h1>
    <p class="section-lead"><?= htmlspecialchars($t["manageHint"]) ?></p>
  </div>
  <nav class="cab-nav">
    <a class="<?= $tab==='home'?'on':'' ?>" href="cabinet.php?tab=home"><?= htmlspecialchars($t["panel"]) ?></a>
    <a class="<?= $tab==='orders'?'on':'' ?>" href="cabinet.php?tab=orders"><?= htmlspecialchars($t["myOrders"]) ?></a>
    <a class="<?= $tab==='new'?'on':'' ?>" href="cabinet.php?tab=new"><?= htmlspecialchars($t["newOrder"]) ?></a>
    <a class="<?= $tab==='chat'?'on':'' ?>" href="cabinet.php?tab=chat"><?= htmlspecialchars($t["chat"]) ?></a>
    <a class="<?= $tab==='profile'?'on':'' ?>" href="cabinet.php?tab=profile"><?= htmlspecialchars($t["profile"]) ?></a>
  </nav>
  <section class="panel">
      <?php if ($tab==='home'): $vip = active_vip($u['id']??''); ?>
        <div class="dash-hero">
          <div class="kicker"><?= htmlspecialchars($t["control"]) ?></div>
          <h3><?= htmlspecialchars($t["allServices"]) ?></h3>
          <p class="section-lead"><?= htmlspecialchars($u['email']??'') ?></p>
        </div>
        <div class="stat-row">
          <div class="stat-tile"><small><?= htmlspecialchars($t["myOrders"]) ?></small><b><?= count($my) ?></b></div>
          <div class="stat-tile"><small>VIP</small><b><?= $vip ? 'ON' : 'OFF' ?></b></div>
          <div class="stat-tile"><small>Chat</small><b>24/7</b></div>
          <div class="stat-tile"><small>Hosting</small><b>UZ</b></div>
          <?php if (is_creator($u)): ?>
          <div class="stat-tile"><small>Jami akkaunt</small><b><?= (int)$allUsersN ?></b></div>
          <div class="stat-tile"><small>Web ro'yxat</small><b><?= (int)$webN ?></b></div>
          <div class="stat-tile"><small>Bot start</small><b><?= (int)$botN ?></b></div>
          <div class="stat-tile"><small>Bot+sayt</small><b><?= (int)$botWebN ?></b></div>
          <div class="stat-tile"><small>Tashrif</small><b><?= (int)$visitN ?></b></div>
          <div class="stat-tile"><small>Admin</small><b><a href="admin.php">panel</a></b></div>
          <?php endif; ?>
        </div>
        <div class="admin-grid">
          <div class="svc-banner">
            <div class="kicker">WEB / 3D / BOT</div>
            <h3><?= htmlspecialchars($t["newProject"]) ?></h3>
            <a class="btn btn-primary" href="cabinet.php?tab=new"><?= htmlspecialchars($t["placeOrder"]) ?></a>
          </div>
          <div class="svc-banner">
            <div class="kicker">SUPPORT</div>
            <h3><?= htmlspecialchars($t["techHelp"]) ?></h3>
            <a class="btn btn-ghost" href="cabinet.php?tab=chat">Chat</a>
          </div>
          <div class="svc-banner">
            <div class="kicker">ORDERS</div>
            <h3><?= htmlspecialchars($t["myWorks"]) ?></h3>
            <a class="btn btn-ghost" href="cabinet.php?tab=orders"><?= htmlspecialchars($t["open"]) ?></a>
          </div>
          <div class="svc-banner">
            <div class="kicker">HOSTING</div>
            <h3>hostin.uz</h3>
            <a class="btn btn-ghost" href="https://hostin.uz/" target="_blank" rel="noreferrer"><?= htmlspecialchars($t["go"]) ?></a>
          </div>
        </div>
      <?php elseif ($tab==='orders'): ?>
        <h2><?= htmlspecialchars($t["myOrders"]) ?></h2>
        <?php $vip = active_vip($u['id']??''); if ($vip): ?>
          <div class="order-card" style="border-color:var(--accent)"><?= htmlspecialchars(str_replace('{date}', substr($vip['vip_until'],0,10), $t['vipLine'] ?? 'VIP {date}')) ?></div>
        <?php endif; ?>
        <?php if (!$my): ?><p><?= htmlspecialchars($t["noOrders"]) ?></p><?php endif; ?>
        <?php foreach (array_reverse($my) as $o): ?>
          <a class="order-card" href="cabinet.php?tab=chat">
            <div class="kicker"><?= htmlspecialchars($o['type']??'Zakaz') ?></div>
            <p><b>Mijoz:</b> <?= htmlspecialchars($o['name']??'-') ?></p>
            <p><b>Telefon:</b> <?= htmlspecialchars($o['phone']??'-') ?></p>
            <p><b>Username:</b> <?= htmlspecialchars($o['username']??'-') ?></p>
            <p><?= htmlspecialchars($o['description']??'') ?></p>
            <small>Holat: <?= htmlspecialchars($o['status']??'new') ?> · <?= htmlspecialchars($o['at']??'') ?></small>
            <?php if (($o['status']??'')==='delivered'): ?>
              <form method="post" style="margin-top:8px"><input type="hidden" name="do" value="close"/><input type="hidden" name="oid" value="<?= htmlspecialchars($o['id']??'') ?>"/><button class="btn btn-ghost"><?= htmlspecialchars($t["closeOrder"]) ?></button></form>
            <?php endif; ?>
          </a>
        <?php endforeach; ?>
      <?php elseif ($tab==='new'): ?>
        <h2><?= htmlspecialchars($t["orderSite"]) ?></h2>
        <p><?= htmlspecialchars($t["chooseType"]) ?></p>
        <form method="post" class="form">
          <input type="hidden" name="do" value="order"/>
          <select name="type" required>
            <option value="">Sayt turi</option>
            <option>Landing</option>
            <option>Internet-magazin</option>
            <option>Portfolio</option>
            <option>Korporativ</option>
            <option>Web ilova</option>
            <option>3D sayt</option>
            <option>Telegram bot</option>
          </select>
          <input name="phone" placeholder="<?= htmlspecialchars($t["phoneL2"] ?? "Phone") ?>" required/>
          <input name="username" placeholder="<?= htmlspecialchars($t["tgUserPh"] ?? "Telegram username") ?>" required/>
          <input name="budget" placeholder="<?= htmlspecialchars($t["budget"] ?? "Budget") ?>"/>
          <textarea name="description" placeholder="<?= htmlspecialchars($t["desc"] ?? "Description") ?>" required></textarea>
          <button class="btn btn-primary">Yuborish</button>
        </form>
      <?php elseif ($tab==='chat'): ?>
        <h2><?= htmlspecialchars($t["adminChat"]) ?></h2>
        <div class="chat-box" id="chat-box"></div>
        <form id="cf" class="form" style="margin-top:10px"><input id="ct"/><button class="btn btn-primary">Yuborish</button></form>
      <?php else: ?>
        <h2><?= htmlspecialchars($t["profile"]) ?></h2>
        <form method="post" enctype="multipart/form-data" class="form">
          <input type="hidden" name="do" value="avatar"/>
          <input type="file" name="avatar" accept="image/*"/>
          <button class="btn btn-primary"><?= htmlspecialchars($t["uploadAvatar"]) ?></button>
        </form>
        <p>Email: <?= htmlspecialchars($u['email']??'') ?></p>
        <a class="btn btn-ghost" href="logout.php"><?= htmlspecialchars($t["leaveProfile"]) ?></a>
      <?php endif; ?>
    </section>
</main>
<script>
<?php if ($tab==='chat'): ?>
const uid = <?= json_encode($u['id']) ?>;
const box = document.getElementById('chat-box');
async function load(){
  const d = await fetch('chat.php?action=list&uid='+encodeURIComponent(uid)).then(r=>r.json());
  box.innerHTML = (d.messages||[]).map(m=>`<div><b>${m.from}:</b> ${m.text}</div>`).join('');
  box.scrollTop = box.scrollHeight;
}
setInterval(load,2500); load();
document.getElementById('cf').onsubmit = async (e)=>{
  e.preventDefault();
  const t=document.getElementById('ct');
  await fetch('chat.php',{method:'POST',body:new URLSearchParams({action:'send',uid,text:t.value,from:'user'})});
  t.value=''; load();
};
<?php endif; ?>
</script>
<script src="assets/js/app.js"></script>
<script>
const chip=document.getElementById('user-chip');
const dr=document.getElementById('user-drawer');
const cl=document.getElementById('user-close');
if(chip&&dr){ chip.onclick=()=>dr.hidden=!dr.hidden; }
if(cl&&dr){ cl.onclick=()=>dr.hidden=true; }
</script>

<div class="ai-dock">
  <div class="ai-box" id="ai-box">
    <header>
      <b>WEBmood AI</b>
      <button type="button" id="ai-close">×</button>
    </header>
    <div class="ai-log" id="ai-log">
      <div class="bot"><span>Salom! Sayt, hosting, zakaz yoki texnik savolingizni yozing.</span></div>
    </div>
    <form id="ai-form"><input id="ai-q" placeholder="<?= htmlspecialchars($t["askPh"] ?? "Your question...") ?>" autocomplete="off" /><button type="submit">↑</button></form>
  </div>
  <button type="button" class="ai-fab" id="ai-fab">AI</button>
</div>
<script>
const fab=document.getElementById('ai-fab'), box=document.getElementById('ai-box'), log=document.getElementById('ai-log');
fab.onclick=()=> box.classList.toggle('on');
document.getElementById('ai-close').onclick=()=> box.classList.remove('on');
document.getElementById('ai-form').onsubmit=async (e)=>{
  e.preventDefault();
  const q=document.getElementById('ai-q');
  const text=q.value.trim(); if(!text) return;
  log.innerHTML += `<div class="me"><span>${text.replace(/</g,'&lt;')}</span></div>`;
  q.value='';
  log.innerHTML += `<div class="bot" id="ai-wait"><span>...</span></div>`;
  log.scrollTop=log.scrollHeight;
  const d=await fetch('ai.php',{method:'POST',body:new URLSearchParams({q:text})}).then(r=>r.json());
  const w=document.getElementById('ai-wait'); if(w) w.remove();
  log.innerHTML += `<div class="bot"><span>${(d.a||'Javob yoq').replace(/</g,'&lt;')}</span></div>`;
  log.scrollTop=log.scrollHeight;
};
</script>

<script src="assets/js/notify-bell.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  var uid = <?= json_encode($u['id'] ?? '', JSON_UNESCAPED_UNICODE) ?>;
  if (window.wmNotifyBell) {
    wmNotifyBell({ role: 'user', uid: uid || '', mount: 'header.nav, .cab-top, header' });
  }
});
</script>
</body></html>
