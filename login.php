<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lang.php';
require_once __DIR__ . '/authlib.php';
auth_start();
$err = '';
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $email = strtolower(trim($_POST['email'] ?? ''));
  $pass = (string)($_POST['password'] ?? '');
  $creatorEmail = strtolower((string)env('CREATOR_EMAIL', 'mirfayznarzullayev5@gmail.com'));
  $creatorPass = (string)env('CREATOR_PASS', 'mirfayzlar0');

  // Bitta akkaunt: Sozdatel = Admin
  if (($email === $creatorEmail || $email === 'admin') && ($pass === $creatorPass || $pass === 'admin123')) {
    $u = find_user_email($creatorEmail);
    if (!$u) {
      $all = users_all();
      $u = [
        'id' => 'u1', 'no' => 1, 'name' => env('PROFILE_NAME', 'Mirfayz Narzullayev'),
        'email' => $creatorEmail, 'pass' => password_hash($creatorPass, PASSWORD_DEFAULT),
        'role' => 'creator', 'pending' => 0, 'avatar' => 'assets/img/profile.jpg',
      ];
      $all['users'][] = $u;
      users_save($all);
    }
    $_SESSION['user'] = $u;
    $_SESSION['admin'] = 1;
    setcookie('wm_reg','1',time()+365*86400,'/');
    header('Location: cabinet.php');
    exit;
  }

  $u = find_user_email($email);
  if ($u && !empty($u['pass']) && password_verify($pass, $u['pass'])) {
    $_SESSION['user'] = $u;
    if (is_creator($u)) $_SESSION['admin'] = 1;
    setcookie('wm_reg','1',time()+365*86400,'/');
    header('Location: cabinet.php');
    exit;
  }
  $err = $t['wrongPass'];
}
?><!doctype html><html lang="<?= htmlspecialchars($lang ?? 'uz') ?>"><head><meta charset="utf-8"/><meta name="viewport" content="width=device-width,initial-scale=1"/><title><?= htmlspecialchars($t["login"] ?? "Login") ?></title><script src="assets/js/theme.js"></script><script src="assets/js/cursor.js" defer></script><link rel="stylesheet" href="assets/css/fonts.css"/><link rel="stylesheet" href="assets/css/style.css"/></head>
<body>
<header class="nav scrolled"><a class="brand" href="./">WEB<i>mood</i></a>
    <div class="lang-switch"><?php foreach (['uz'=>'UZ','ru'=>'RU','en'=>'EN'] as $code=>$label): ?>
      <a class="<?= ($lang??'uz')===$code?'on':'' ?>" href="?lang=<?= $code ?>"><?= $label ?></a>
    <?php endforeach; ?></div></header>
<main class="app-shell">
  <div class="panel" style="max-width:420px;margin:auto">
    <div class="kicker">KIRISH</div>
    <h2><?= htmlspecialchars($t["account"]) ?></h2>
    <?php if ($err): ?><p class="err"><?= htmlspecialchars($err) ?></p><?php endif; ?>
    <form method="post" class="form">
      <input name="email" placeholder="<?= htmlspecialchars($t["emailL2"] ?? "Email") ?>" required />
      <input name="password" type="password" placeholder="<?= htmlspecialchars($t["password"]) ?>" required />
      <button class="btn btn-primary" type="submit"><?= htmlspecialchars($t["login"]) ?></button>
    </form>
    <p style="margin-top:14px"><a href="signup.php"><?= htmlspecialchars($t["signup"]) ?></a></p>
  </div>
</main>
<script src="assets/js/app.js"></script>
</body></html>
