(function(){
  if (document.cookie.indexOf('wm_reg=') !== -1) {
    const gate = document.getElementById('gate');
    if (gate) { gate.classList.add('done'); gate.style.display='none'; }
    const sec = document.getElementById('register');
    if (sec) sec.remove();
    const acc = document.getElementById('acc-link');
    if (acc) acc.style.display = 'inline-flex';
  }
})();

(function(){
  const gate = document.getElementById('gate');
  if (!gate) return;
  if (document.cookie.indexOf('wm_reg=') !== -1) { gate.classList.add('done'); return; }
  const start = document.getElementById('gate-start');
  const reg = document.getElementById('gate-reg');
  const msg = document.getElementById('gate-msg');
  document.getElementById('btn-start').onclick = () => { start.hidden = true; reg.hidden = false; };
  document.getElementById('btn-email').onclick = () => { document.getElementById('email-form').hidden = false; };
  document.getElementById('email-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(e.target);
    fd.append('action','email');
    const r = await fetch('register.php', { method:'POST', body: fd });
    const d = await r.json();
    msg.textContent = d.message || '';
    if (d.ok) setTimeout(() => { gate.classList.add('done'); location.reload(); }, 700);
  });
  document.getElementById('btn-tg').onclick = async () => {
    const r = await fetch('register.php?action=telegram_init');
    const d = await r.json();
    if (!d.ok) return;
    msg.innerHTML = 'Botga oting, <b>Ruxsat</b> ni bosing.';
    window.open(d.url, '_blank');
    const t = d.token;
    const iv = setInterval(async () => {
      const s = await fetch('register.php?action=telegram_status&token=' + t).then(x => x.json());
      if (s.ok) {
        clearInterval(iv);
        document.cookie = 'wm_reg=1;path=/;max-age=31536000';
        document.cookie = 'wm_via=bot;path=/;max-age=31536000';
        msg.textContent = 'Bot orqali ro\'yxatdan o\'tdingiz';
        setTimeout(() => { gate.classList.add('done'); location.reload(); }, 700);
      }
    }, 2000);
  };

  const form = document.getElementById('form');
  if (form) {
    form.addEventListener('submit', () => {
      if (document.cookie.indexOf('wm_reg=') === -1) {
        gate.classList.remove('done');
        start.hidden = true; reg.hidden = false;
      }
    }, true);
  }
})();

  const acc = document.getElementById('acc-link');
  if (acc && document.cookie.indexOf('wm_reg=') !== -1) acc.style.display = 'inline-flex';

  const se = document.getElementById('sec-email');
  const st = document.getElementById('sec-tg');
  const sf = document.getElementById('sec-email-form');
  const sm = document.getElementById('sec-reg-msg');
  if (se && sf) se.onclick = () => { sf.hidden = false; };
  if (sf) sf.addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(sf);
    fd.append('action','email');
    const d = await fetch('register.php', {method:'POST', body:fd}).then(r=>r.json());
    if (sm) sm.textContent = d.message || '';
    if (d.ok) setTimeout(()=>location.href='profile.php', 600);
  });
  if (st) st.onclick = async () => {
    const d = await fetch('register.php?action=telegram_init').then(r=>r.json());
    if (!d.ok) return;
    if (sm) sm.textContent = 'Botda Ruxsat tugmasini bosing';
    window.open(d.url, '_blank');
    const iv = setInterval(async () => {
      const s = await fetch('register.php?action=telegram_status&token='+d.token).then(r=>r.json());
      if (s.ok) {
        clearInterval(iv);
        document.cookie='wm_reg=1;path=/;max-age=31536000';
        document.cookie='wm_via=bot;path=/;max-age=31536000';
        location.href='profile.php';
      }
    }, 2000);
  };
