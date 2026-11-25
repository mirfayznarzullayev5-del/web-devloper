<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lang.php';
require_once __DIR__ . '/authlib.php';
auth_start();
$err=''; $ok='';
if (isset($_POST['mode']) && $_POST['mode']==='email') {
  $name = trim($_POST['name'] ?? '');
  $email = strtolower(trim($_POST['email'] ?? ''));
  $pass = (string)($_POST['password'] ?? '');
  $phone = trim($_POST['phone'] ?? '');
  $username = ltrim(trim($_POST['username'] ?? ''), '@');
  if (!filter_var($email, FILTER_VALIDATE_EMAIL) || substr($email, -10) !== '@gmail.com') {
    $err = $t['onlyGmailErr'] ?? 'Only @gmail.com';
  } elseif (strlen($pass)<4 || $name==='' || $phone==='' || $username==='') {
    $err = $t['fieldsRequired'] ?? 'Required';
  } elseif (find_user_email($email)) {
    $err = $t['emailExists'] ?? 'Exists';
  } else {
    $all = users_all();
    $no = next_user_no();
    $u = ['id'=>'u'.bin2hex(random_bytes(3)),'no'=>$no,'name'=>$name,'email'=>$email,'phone'=>$phone,'username'=>$username,'pass'=>password_hash($pass,PASSWORD_DEFAULT),'via'=>'web','at'=>date('c'),'avatar'=>'','tg_chat'=>'','pending'=>1];
    $all['users'][] = $u; users_save($all);
    $_SESSION['user'] = $u;
    setcookie('wm_reg','1',time()+365*86400,'/');
    $bot = $TELEGRAM['botUsername'] ?? 'WEB_mood_bot';
    header('Location: wait-bot.php?id='.$u['id'].'&bot='.urlencode($bot));
    exit;
  }
}
$bot = $TELEGRAM['botUsername'] ?? 'WEB_mood_bot';
?><!doctype html><html lang="<?= htmlspecialchars($lang ?? 'uz') ?>"><head><meta charset="utf-8"/><meta name="viewport" content="width=device-width,initial-scale=1"/><title><?= htmlspecialchars($t["signup"] ?? "Sign up") ?></title><script src="assets/js/theme.js"></script><script src="assets/js/cursor.js" defer></script><link rel="stylesheet" href="assets/css/fonts.css"/><link rel="stylesheet" href="assets/css/style.css"/></head>
<body>
<header class="nav scrolled"><a class="brand" href="./">WEB<i>mood</i></a>
    <div class="lang-switch"><?php foreach (['uz'=>'UZ','ru'=>'RU','en'=>'EN'] as $code=>$label): ?>
      <a class="<?= ($lang??'uz')===$code?'on':'' ?>" href="?lang=<?= $code ?>"><?= $label ?></a>
    <?php endforeach; ?></div></header>
<main class="app-shell">
  <div class="panel" style="max-width:480px;margin:auto">
    <div class="kicker"><?= htmlspecialchars($t["page2"] ?? "PAGE 2") ?></div>
    <h2><?= htmlspecialchars($t["signup"]) ?></h2>
    <p class="section-lead"><?= htmlspecialchars($t['emailOrTg'] ?? '') ?></p>
    <?php if ($err): ?><p class="err"><?= htmlspecialchars($err) ?></p><?php endif; ?>
    <form method="post" class="form">
      <input type="hidden" name="mode" value="email"/>
      <input name="name" placeholder="<?= htmlspecialchars($t["name"] ?? "Name") ?>" required/>
      <input name="email" type="email" placeholder="<?= htmlspecialchars($t["gmailPh"] ?? "Gmail") ?>" required/>
      <input name="phone" placeholder="<?= htmlspecialchars($t["phoneL2"] ?? "Phone") ?>" required/>
      <input name="username" placeholder="<?= htmlspecialchars($t["tgUserPh"] ?? "Telegram username") ?>" required/>
      <input name="password" type="password" placeholder="<?= htmlspecialchars($t["passPh"] ?? $t["password"] ?? "Password") ?>" required/>
      <button class="btn btn-primary"><?= htmlspecialchars($t["emailReg"]) ?></button>
    </form>
    <p style="margin:16px 0"><?= htmlspecialchars($t["or"]) ?></p>
    <a class="btn btn-ghost" href="https://t.me/<?= htmlspecialchars($bot) ?>?start=webreg"><?= htmlspecialchars($t["tgReg"]) ?></a>
    <p style="margin-top:16px"><a href="login.php"><?= htmlspecialchars($t["hasAccount"]) ?></a></p>
  </div>
</main>
<script src="assets/js/app.js"></script>
</body></html>
