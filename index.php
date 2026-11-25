<?php
require __DIR__ . '/config.php';
require_once __DIR__ . '/authlib.php';
if (function_exists('auth_start')) auth_start();
require __DIR__ . '/lang.php';
require __DIR__ . '/tech.php';
function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function brand_html($name) {
  if (preg_match('/^(web)(mood)$/i', (string)$name, $m)) return h($m[1]) . '<i>' . h($m[2]) . '</i>';
  return h($name);
}
$SVC_ICONS = [
  '<rect x="3" y="4" width="18" height="16" rx="2"/><path d="M3 9h18M7 6.5h.01M10 6.5h.01"/>',
  '<path d="m8 8-4 4 4 4M16 8l4 4-4 4M13.5 5l-3 14"/>',
  '<rect x="3" y="4" width="18" height="6" rx="1.5"/><rect x="3" y="14" width="18" height="6" rx="1.5"/><path d="M7 7h.01M7 17h.01M12 7h5M12 17h5"/>',
  '<path d="M5 8h14l-1 12H6L5 8Z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/>',
  '<path d="M12 3 4 7.5v9L12 21l8-4.5v-9L12 3Z"/><path d="M4 7.5 12 12l8-4.5M12 12v9"/>',
  '<path d="M4 20l4-1 11-11a2.1 2.1 0 0 0-3-3L5 16l-1 4Z"/><path d="m14 7 3 3"/>',
];
$filters = [
  'ALL' => $t['f_all'] ?? 'ALL',
  'WEB' => $t['f_web'] ?? 'WEB',
  'APP' => $t['f_app'] ?? 'APP',
  '3D' => $t['f_3d'] ?? '3D',
  'E-COMMERCE' => $t['f_ecom'] ?? 'E-COMMERCE',
];
$statLabels = [$t['stat_projects'], $t['stat_clients'], $t['stat_years'], $t['stat_passion']];
?>
<!doctype html>
<html lang="<?= h($t['html']) ?>">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
  <title><?= h($SITE['title']) ?></title>
  <meta name="description" content="<?= h($SITE['description']) ?>" />
  <meta property="og:title" content="<?= h($SITE['title']) ?>" />
  <meta property="og:description" content="<?= h($SITE['subTagline']) ?>" />
  <meta property="og:type" content="website" />
  <meta name="theme-color" content="#F4F6FC" />
  <link rel="icon" type="image/svg+xml" href="assets/img/favicon.svg" />
  <link rel="preload" href="assets/fonts/geologica-latin-wght-normal.woff2" as="font" type="font/woff2" crossorigin />
  <link rel="preload" href="assets/fonts/onest-latin-wght-normal.woff2" as="font" type="font/woff2" crossorigin />
  <script src="assets/js/theme.js"></script><script src="assets/js/cursor.js" defer></script><link rel="stylesheet" href="assets/css/fonts.css" />
  <link rel="stylesheet" href="assets/css/style.css" />
</head>
<body data-view="<?= h($t['view']) ?>" data-open="<?= h($t['open']) ?>">
  <svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
    <defs><clipPath id="wm-arch" clipPathUnits="objectBoundingBox"><path d="M0,1 V0.46 C0,0.30 0.22,0.12 0.5,0 C0.78,0.12 1,0.30 1,0.46 V1 Z"/></clipPath></defs>
  </svg>
  <div class="sec-lock" id="sec-lock" hidden>
    <div class="sec-lock-card sec-error">
      <button type="button" class="close" id="tet-close">x</button>
      <div class="sec-err-icon">⚠</div>
      <b><?= h($t['secError'] ?? 'SECURITY ERROR') ?></b>
      <p class="sec-err-code"><?= h($t['secCode'] ?? 'ERROR 403') ?></p>
      <p><?= h($t['secMsg'] ?? '') ?></p>
      <p class="sec-err-sub"><?= h($t['secSub'] ?? 'WEBmood Security System') ?></p>
    </div>
  </div>
  
  <div class="gate done" id="gate" hidden>
    <div class="gate-card">
      <div class="gate-step" id="gate-start">
        <div class="loader-orb"></div>
        <h2>WEBmood</h2>
        <p><?= h($t["gateContinue"] ?? "") ?></p>
        <button type="button" class="btn btn-primary" id="btn-start"><?= h($t["startBtn"] ?? "START") ?></button>
      </div>
      <div class="gate-step" id="gate-reg" hidden>
        <h2><?= h($t["regBtn"] ?? $t["signup"] ?? "Sign up") ?></h2>
        <p><?= h($t["gateChoose"] ?? "") ?></p>
        <div class="cta-row">
          <button type="button" class="btn btn-primary" id="btn-email"><?= h($t["emailBtn"] ?? "Email") ?></button>
          <button type="button" class="btn btn-ghost" id="btn-tg"><?= h($t['tgBotBtn'] ?? 'Telegram bot') ?></button>
        </div>
        <form id="email-form" class="form" hidden>
          <input name="name" placeholder="<?= h($t["name"] ?? "Name") ?>" required />
          <input name="email" type="email" placeholder="<?= h($t["emailL2"] ?? "Email") ?>" required />
          <button class="btn btn-primary" type="submit"><?= h($t["regBtn"] ?? $t["signup"] ?? "Sign up") ?></button>
        </form>
        <p id="gate-msg"></p>
      </div>
    </div>
  </div>

  <div class="loader">
    <div class="loader-bg"></div>
    <div class="loader-inner">
      <div class="loader-orb"></div>
      <div class="loader-logo"><?= h($SITE['name']) ?></div>
      <div class="loader-sub"><?= h($t['loader']) ?></div>
      <div class="loader-bar"><span></span></div>
      <div class="loader-pct" id="loader-pct">00%</div>
    </div>
  </div>

  <header class="nav">
    <a href="#home" class="brand" aria-label="<?= h($SITE['name']) ?>"><?= brand_html($SITE['name']) ?></a>
    <nav class="nav-links">
      <?php foreach ($NAV as $n): ?>
        <a href="#<?= h($n['id']) ?>"><?= h($t['nav'][$n['id']]) ?></a>
      <?php endforeach; ?>
    </nav>
    <div class="nav-tools">
    <div class="hud" aria-label="live stats">
      <div class="hud-clock" id="hud-clock" title="Toshkent"><em>00</em><span>:</span><em>00</em><span>:</span><em>00</em></div>
    </div>
<?php if (empty($_SESSION['user']) && empty($_COOKIE['wm_reg'])): ?>
    <a class="btn btn-primary" href="login.php"><?= h($t['login']) ?></a>
    <?php else: ?>
    <a class="btn btn-ghost" href="cabinet.php"><?= h($t['cabinet']) ?></a>
    <?php endif; ?>
    <div class="lang-switch">
      <?php foreach (['uz'=>'UZ','ru'=>'RU','en'=>'EN'] as $code => $label): ?>
        <a class="<?= $lang === $code ? 'on' : '' ?>" href="<?= h(t_url($code)) ?>"><?= $label ?></a>
      <?php endforeach; ?>
    </div>
    </div>
    <button class="hamburger" aria-label="<?= h($t["menu"] ?? "Menu") ?>"><span></span><span></span><span></span></button>
  </header>
  <div class="mobile-menu">
    <?php foreach ($NAV as $n): ?>
      <a href="#<?= h($n['id']) ?>"><?= h($t['nav'][$n['id']]) ?></a>
    <?php endforeach; ?>
    <div class="lang-switch">
      <?php foreach (['uz'=>'UZ','ru'=>'RU','en'=>'EN'] as $code => $label): ?>
        <a class="<?= $lang === $code ? 'on' : '' ?>" href="<?= h(t_url($code)) ?>"><?= $label ?></a>
      <?php endforeach; ?>
    </div>
  </div>

  <section class="hero" id="home">
    <div class="hero-bg" aria-hidden="true"></div>
    <div class="hero-copy">
      <div class="badge"><?= h($t['badge']) ?></div>
      <h1 class="hero-title"><span class="l1"><?= h($t['hero1']) ?></span><span class="l2"><?= h($t['hero2']) ?></span></h1>
      <p class="hero-lead"><?= h($t['heroLead']) ?></p>
      <div class="cta-row">
        <a class="btn btn-primary" href="#projects"><?= h($t['viewWork']) ?></a>
        <a class="btn btn-ghost" href="#contact"><?= h($t['workTogether']) ?></a>
      </div>
    </div>
    <div class="hero-stage">
      <div class="cube-scene" id="hero-cube">
        <div class="cube">
          <div class="face front">&lt;/&gt;</div>
          <div class="face back">{ }</div>
          <div class="face right">JS</div>
          <div class="face left">CSS</div>
          <div class="face top">HTML</div>
          <div class="face bottom">PHP</div>
        </div>
      </div>
    </div>
  </section>

  <?php $vipShow = (function_exists('current_user') && current_user() && function_exists('active_vip')) ? active_vip(current_user()['id']??'') : null; if ($vipShow): ?>
  <div class="vip-strip"><?= h(str_replace('{date}', substr($vipShow['vip_until'],0,10), $t['vipLine'] ?? 'VIP {date}')) ?></div>
  <?php endif; ?>
  <section id="about">
    <div class="wrap about-grid">
      <div>
        <div class="kicker"><?= h($t['intro']) ?></div>
        <h2><?= h($t['who']) ?></h2>
        <p class="section-lead"><?= h($t['aboutLead']) ?></p>
        <div class="stats">
          <?php foreach ($STATS as $i => $s): ?>
            <div class="stat">
              <b data-value="<?= (int)$s['value'] ?>" data-suffix="<?= h($s['suffix']) ?>">0<?= h($s['suffix']) ?></b>
              <span><?= h($statLabels[$i]) ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="portrait portrait-3d">
        <model-viewer
          class="about-model"
          src="https://cdn.jsdelivr.net/gh/mrdoob/three.js@r152/examples/models/gltf/Soldier.glb"
          alt="3D Developer model"
          camera-controls
          auto-rotate
          auto-rotate-delay="0"
          rotation-per-second="20deg"
          shadow-intensity="1"
          exposure="1.1"
          camera-orbit="0deg 75deg 2.4m"
          field-of-view="28deg"
          interaction-prompt="none"
          ar-modes="webxr scene-viewer"
          style="width:100%;height:100%;background:transparent;"
        ></model-viewer>
        <span class="portrait-label"><?= h($t["codeDesign"] ?? "CODE + DESIGN + 3D") ?></span>
      </div>
    </div>
  </section>


  <section id="profile">
    <div class="wrap">
      <div class="kicker"><?= h($t['devProfile'] ?? 'PROFILE') ?></div>
      <h2><?= h($SITE['profileName'] ?? $SITE['name']) ?></h2>
      <article class="dev-card">
        <div class="dev-photo">
          <img src="assets/img/profile.jpg?v=2" alt="" onerror="this.onerror=null;this.src='assets/img/avatar-default.svg'" />
          <span class="online-dot"><?= h($t['online'] ?? 'ONLINE') ?></span>
        </div>
        <h3><?= h($SITE['profileName'] ?? $SITE['name']) ?></h3>
        <p class="dev-role"><?= h($SITE['profileRole'] ?? 'Web Developer') ?></p>
        <a class="btn btn-primary dev-tg" href="https://t.me/<?= h($SITE['profileTg'] ?? $SITE['telegramUsername']) ?>" target="_blank" rel="noreferrer">
          <?= h($t['writeTg'] ?? 'Telegram') ?> · @<?= h($SITE['profileTg'] ?? $SITE['telegramUsername']) ?>
        </a>
      </article>
    </div>
  </section>

  <section id="skills">
    <div class="wrap">
      <div class="kicker"><?= h($t['cap']) ?></div>
      <h2><?= h($t['stack']) ?></h2>
      <div class="skill-groups">
        <?php foreach ($SKILLS as $group => $items): ?>
          <div class="skill-group">
            <h3><?= h(strtoupper($group)) ?></h3>
            <div class="skill-row">
              <?php foreach ($items as $item): ?>
                <button type="button" class="skill" data-tech="<?= h($item) ?>" data-info="<?= h($TECH[$item][$lang] ?? $TECH[$item]['en'] ?? $item) ?>">
                  <span class="mono" aria-hidden="true"><?= h(substr($item,0,2)) ?></span>
                  <strong><?= h($item) ?></strong>
                </button>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="code-section">
    <div class="wrap">
      <div class="kicker"><?= h($t['craft']) ?></div>
      <h2><?= h($t['codeTool']) ?></h2>
      <div class="editor">
        <div class="editor-bar"><i class="dot r"></i><i class="dot y"></i><i class="dot g"></i></div>
        <pre></pre>
      </div>
    </div>
  </section>

  <section id="services">
    <div class="wrap">
      <div class="kicker"><?= h($t['offer']) ?></div>
      <h2><?= h($t['whatIDo']) ?></h2>
      <div class="services">
        <?php foreach ($SERVICES as $i => $s): ?>
          <article class="service">
            <div class="svc-ico" aria-hidden="true"><svg viewBox="0 0 24 24"><?= $SVC_ICONS[$i] ?? '' ?></svg></div>
            <h3><?= h($t['svc'][$i][0]) ?></h3>
            <p><?= h($t['svc'][$i][1]) ?></p>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section id="projects">
    <div class="wrap">
      <div class="kicker"><?= h($t['portfolio']) ?></div>
      <h2><?= h($t['selected']) ?></h2>
      <div class="filters">
        <?php $fi=0; foreach ($filters as $fkey => $flabel): ?>
          <button class="filter<?= $fi === 0 ? ' on' : '' ?>" data-filter="<?= h($fkey) ?>"><?= h($flabel) ?></button>
        <?php $fi++; endforeach; ?>
      </div>
      <div class="projects">
        <?php foreach ($PROJECTS as $p):
          $pid = $p['id'] ?? '';
          $map = [
            'wamsey' => ['type'=>'proj_wamsey_type','ch'=>'proj_wamsey_ch','sol'=>'proj_wamsey_sol'],
            'olimov-meat' => ['type'=>'proj_meat_type','ch'=>'proj_meat_ch','sol'=>'proj_meat_sol'],
            'ai-project' => ['type'=>'proj_ai_type','ch'=>'proj_ai_ch','sol'=>'proj_ai_sol'],
            'webmood' => ['type'=>'proj_wm_type','ch'=>'proj_wm_ch','sol'=>'proj_wm_sol'],
            'qarz-pro' => ['type'=>'proj_qarz_type','ch'=>'proj_qarz_ch','sol'=>'proj_qarz_sol'],
            'tg-bot' => ['type'=>'proj_bot_type','ch'=>'proj_bot_ch','sol'=>'proj_bot_sol'],
          ];
          if (isset($map[$pid])) {
            $p['type'] = $t[$map[$pid]['type']] ?? $p['type'];
            $p['challenge'] = $t[$map[$pid]['ch']] ?? ($p['challenge'] ?? '');
            $p['solution'] = $t[$map[$pid]['sol']] ?? ($p['solution'] ?? '');
          }
        ?>
          <article class="project" data-category="<?= h($p['category']) ?>" data-project='<?= h(json_encode($p, JSON_UNESCAPED_UNICODE)) ?>' style="--p: <?= h($p['accent']) ?>">
            <div class="project-visual"></div>
            <div class="project-meta">
              <small><?= h($p['num']) ?> — <?= h($p['category']) ?></small>
              <h3><?= h($p['title']) ?></h3>
              <p><?= h($p['type']) ?></p>
              <div class="tech-list">
                <?php foreach ($p['tech'] as $tech): ?><span><?= h($tech) ?></span><?php endforeach; ?>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <div class="modal skill-modal" id="skill-modal" hidden aria-hidden="true">
    <div class="modal-card">
      <button class="close" type="button" aria-label="Close">x</button>
      <small class="kicker"><?= h($t['stack']) ?></small>
      <h3 id="skill-title"></h3>
      <p id="skill-info" class="section-lead"></p>
    </div>
  </div>
  <div class="modal" id="project-modal" hidden aria-hidden="true">
    <div class="modal-card">
      <button class="close" type="button" aria-label="Close">x</button>
      <div class="modal-card-body"></div>
    </div>
  </div>

  <section id="experience">
    <div class="wrap">
      <div class="kicker"><?= h($t['timeline']) ?></div>
      <h2><?= h($t['journey']) ?></h2>
      <div class="timeline">
        <?php foreach ($JOURNEY as $i => $j): ?>
          <div class="tl-item">
            <strong><?= h($j['year']) ?></strong>
            <h3><?= h($t['journeyItems'][$i][0]) ?></h3>
            <p><?= h($t['journeyItems'][$i][1]) ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section>
    <div class="wrap lab">
      <div>
        <div class="kicker"><?= h($t['experiment']) ?></div>
        <h2><?= h($t['lab']) ?></h2>
        <p class="section-lead"><?= h($t['labLead']) ?></p>
      </div>
      <div class="lab-canvas tetris-wrap">
        <div class="tetris-hud">
          <span>SCORE <b id="tet-score">0</b></span>
          <span>BEST <b id="tet-best">0</b></span>
          <span>LVL <b id="tet-lvl">1</b></span>
          <button type="button" id="tet-start" class="btn btn-primary">PLAY</button>
        </div>
        <canvas id="tetris" width="240" height="480"></canvas>
        <div class="tetris-keys">
          <button type="button" data-k="left">←</button>
          <button type="button" data-k="rot">↻</button>
          <button type="button" data-k="right">→</button>
          <button type="button" data-k="down">↓</button>
        </div>
      </div>
    </div>
  </section>

  <section>
    <div class="wrap">
      <div class="kicker"><?= h($t['activity']) ?></div>
      <h2><?= h($t['building']) ?></h2>
      <div class="gh-grid">
        <div class="contrib" aria-hidden>
          <?php for ($w = 0; $w < 53; $w++): for ($d = 0; $d < 7; $d++): $lvl = random_int(0, 4); ?>
            <i class="cell<?= $lvl ? ' l'.$lvl : '' ?>"></i>
          <?php endfor; endfor; ?>
        </div>
        <div class="gh-cards">
          <div class="gh-card"><b><?= (int)$GITHUB['repos'] ?></b><?= h($t['repos']) ?></div>
          <div class="gh-card"><b><?= (int)$GITHUB['commits'] ?></b><?= h($t['commits']) ?></div>
          <div class="gh-card"><b><?= (int)$GITHUB['projects'] ?></b><?= h($t['proj']) ?></div>
          <div class="gh-card"><b><?= (int)$GITHUB['technologies'] ?></b><?= h($t['techs']) ?></div>
        </div>
      </div>
    </div>
  </section>


  <?php if (false): ?>
  <section id="register">
    <div class="wrap">
      <div class="kicker">ACCOUNT</div>
      <h2><?= h($t["regBtn"] ?? $t["signup"] ?? "Sign up") ?></h2>
      <p class="section-lead"><?= h($t['emailOrTg'] ?? '') ?></p>
      <div class="cta-row">
        <button type="button" class="btn btn-primary" id="sec-email"><?= h($t["emailBtn"] ?? "Email") ?></button>
        <button type="button" class="btn btn-ghost" id="sec-tg"><?= h($t['tgBotBtn'] ?? 'Telegram bot') ?></button>
      </div>
      <form id="sec-email-form" class="form" hidden style="max-width:420px;margin-top:16px">
        <input name="name" placeholder="<?= h($t["name"] ?? "Name") ?>" required />
        <input name="email" type="email" placeholder="<?= h($t["emailL2"] ?? "Email") ?>" required />
        <button class="btn btn-primary" type="submit"><?= h($t["regBtn"] ?? $t["signup"] ?? "Sign up") ?></button>
      </form>
      <p id="sec-reg-msg"></p>
    </div>
  </section>

<?php endif; ?>
  <section id="contact">
    <div class="wrap">
      <div class="kicker"><?= h($t['haveIdea']) ?></div>
      <div class="contact-hero"><h2><?= h($t['letsBuild']) ?></h2></div>
      <p class="section-lead"><?= h($t['contactLead']) ?></p>
      <div class="cta-row">
        <a class="btn btn-primary" href="<?= h($SITE['telegramHref']) ?>" target="_blank" rel="noreferrer"><?= h($t['connectBot']) ?></a>
        <a class="btn btn-ghost" href="https://hostin.uz/" target="_blank" rel="noreferrer"><?= h($t['hosting']) ?></a>
        <a class="btn btn-ghost" href="login.php"><?= h($t['cabinet']) ?></a>
      </div>
      <div class="contact-grid">
        <div class="contact-card"><small>TELEGRAM BOT</small><a href="<?= h($SITE['telegramHref']) ?>" target="_blank" rel="noreferrer"><?= h($SITE['telegramHandle']) ?></a></div>
        <div class="contact-card"><small>HOSTING</small><a href="https://hostin.uz/" target="_blank" rel="noreferrer">hostin.uz</a></div>

      </div>
    </div>
  </section>

  <footer>
    <div class="foot-top">
      <div>
        <div class="brand"><?= brand_html($SITE['name']) ?></div>
        <p><?= h($SITE['tagline']) ?></p>
      </div>
      <div class="foot-links">
        <?php foreach ($NAV as $n): if ($n['id'] === 'experience') continue; ?>
          <a href="#<?= h($n['id']) ?>"><?= h($t['nav'][$n['id']]) ?></a>
        <?php endforeach; ?>
      </div>
      <div class="socials">
        <a href="<?= h($SITE['social']['telegram']) ?>"><span class="mono" aria-hidden="true">TG</span>Telegram</a>
        <a href="<?= h($SITE['social']['instagram']) ?>"><span class="mono" aria-hidden="true">IG</span>Instagram</a>
        <a href="<?= h($SITE['social']['github']) ?>"><span class="mono" aria-hidden="true">GH</span>GitHub</a>
        <a href="<?= h($SITE['social']['linkedin']) ?>"><span class="mono" aria-hidden="true">IN</span>LinkedIn</a>
      </div>
    </div>
    <div class="foot-bottom">
      <span><?= h($SITE['phone']) ?> · <?= h($SITE['telegramHandle']) ?></span>
      <span>&copy; <?= (int)$SITE['year'] ?> <?= h($SITE['name']) ?>. <?= h($t['rights']) ?></span>
    </div>
  </footer>

  <script type="module" src="https://ajax.googleapis.com/ajax/libs/model-viewer/3.5.0/model-viewer.min.js"></script>
  <script src="assets/js/app.js"></script>
  <script src="assets/js/gate.js"></script>
  <script src="assets/js/tetris.js"></script>
</body>
</html>
