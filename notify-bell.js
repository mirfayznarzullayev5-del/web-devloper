(function () {
  function el(html) {
    const t = document.createElement('div');
    t.innerHTML = html.trim();
    return t.firstChild;
  }
  function ensureStyles() {
    if (document.getElementById('wm-bell-css')) return;
    const s = document.createElement('style');
    s.id = 'wm-bell-css';
    s.textContent = `
      .wm-bell{position:relative;width:42px;height:42px;border-radius:50%;border:1.5px solid var(--line,#D2D9EE);background:var(--surface,#fff);color:var(--text,#0B1640);display:grid;place-items:center;cursor:pointer;transition:border-color .2s}
      .wm-bell:hover{border-color:var(--cobalt,#2445D6)}
      .wm-bell svg{width:20px;height:20px}
      .wm-bell .badge{position:absolute;top:-3px;right:-3px;min-width:18px;height:18px;padding:0 5px;border:2px solid var(--paper,#F4F6FC);border-radius:999px;background:#C62F45;color:#fff;font-size:11px;font-weight:700;line-height:14px;display:none;align-items:center;justify-content:center}
      .wm-bell .badge::before{display:none}
      .wm-bell .badge.on{display:inline-flex}
      .wm-bell-panel{position:absolute;right:0;top:50px;width:320px;max-width:88vw;background:var(--surface,#fff);border:1.5px solid var(--line,#D2D9EE);border-radius:6px 24px 6px 24px;box-shadow:0 24px 50px -20px rgba(11,22,64,.45);padding:12px;z-index:9999;display:none}
      .wm-bell-panel.open{display:block}
      .wm-bell-panel h4{margin:0 0 8px;font:700 13px var(--f-body,sans-serif);color:var(--link,#2445D6);letter-spacing:.08em}
      .wm-bell-item{padding:10px;border-radius:10px;border:1.5px solid var(--line,#D2D9EE);margin-bottom:6px;font-size:14px;color:var(--text,#0B1640)}
      .wm-bell-item:hover{border-color:var(--cobalt,#2445D6)}
      .wm-bell-item small{display:block;color:var(--muted,#505B85);margin-top:4px;font-size:12px}
      .wm-bell-empty{padding:16px;text-align:center;color:var(--muted,#505B85);font-size:14px}
      .wm-bell-wrap{position:relative;display:inline-flex;margin-left:8px}
    `;
    document.head.appendChild(s);
  }
  async function poll(cfg) {
    const q = new URLSearchParams({ role: cfg.role, uid: cfg.uid || '', action: 'list' });
    try {
      const d = await fetch('notify.php?' + q.toString()).then((r) => r.json());
      const count = d.count || 0;
      const badge = cfg.root.querySelector('.badge');
      if (badge) {
        badge.textContent = count > 99 ? '99+' : String(count);
        badge.classList.toggle('on', count > 0);
      }
      const panel = cfg.root.querySelector('.wm-bell-panel');
      if (panel && panel.classList.contains('open')) renderPanel(panel, d.items || [], cfg);
    } catch (e) {}
  }
  function renderPanel(panel, items, cfg) {
    const body = panel.querySelector('.wm-bell-body');
    if (!body) return;
    if (!items.length) {
      body.innerHTML = '<div class="wm-bell-empty">Yangi xabar yo‘q</div>';
      return;
    }
    body.innerHTML = items
      .map((n) => {
        const who = n.from === 'admin' ? 'Admin' : 'Mijoz';
        const link =
          cfg.role === 'admin'
            ? 'admin.php?chat=' + encodeURIComponent(n.uid || '')
            : 'cabinet.php?tab=chat';
        return `<a class="wm-bell-item" href="${link}" style="display:block;text-decoration:none">
          <b>${who}</b>: ${escapeHtml(n.text || '')}
          <small>${escapeHtml(n.uid || '')} · ${escapeHtml((n.at || '').slice(0, 16))}</small>
        </a>`;
      })
      .join('');
  }
  function escapeHtml(s) {
    return String(s)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }
  window.wmNotifyBell = function (opts) {
    ensureStyles();
    const host = document.querySelector(opts.mount || 'header.nav') || document.body;
    const wrap = el(`<div class="wm-bell-wrap">
      <button type="button" class="wm-bell" aria-label="Bildirishnomalar">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5"/><path d="M9 17a3 3 0 0 0 6 0"/></svg>
        <span class="badge">0</span>
      </button>
      <div class="wm-bell-panel"><h4>XABARLAR</h4><div class="wm-bell-body"></div></div>
    </div>`);
    host.appendChild(wrap);
    const cfg = { root: wrap, role: opts.role || 'user', uid: opts.uid || '' };
    const btn = wrap.querySelector('.wm-bell');
    const panel = wrap.querySelector('.wm-bell-panel');
    btn.addEventListener('click', async () => {
      panel.classList.toggle('open');
      if (panel.classList.contains('open')) {
        await poll(cfg);
        try {
          await fetch('notify.php?action=read&role=' + encodeURIComponent(cfg.role) + '&uid=' + encodeURIComponent(cfg.uid || ''));
        } catch (e) {}
      }
    });
    document.addEventListener('click', (e) => {
      if (!wrap.contains(e.target)) panel.classList.remove('open');
    });
    poll(cfg);
    setInterval(() => poll(cfg), 4000);
  };
})();
