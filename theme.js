/* WEBmood — kunduzgi / kechgi rejim */
(function () {
  var KEY = 'wm-theme';
  var root = document.documentElement;
  var mq = window.matchMedia ? window.matchMedia('(prefers-color-scheme: dark)') : null;

  function stored() { try { return localStorage.getItem(KEY); } catch (e) { return null; } }
  function save(v) { try { localStorage.setItem(KEY, v); } catch (e) {} }
  function initial() {
    var s = stored();
    if (s === 'light' || s === 'dark') return s;
    return mq && mq.matches ? 'dark' : 'light';
  }

  var LABELS = { uz: 'Rejimni almashtirish', ru: 'Сменить тему', en: 'Toggle theme' };

  function apply(t) {
    root.setAttribute('data-theme', t);
    var m = document.querySelector('meta[name="theme-color"]');
    if (m) m.setAttribute('content', t === 'dark' ? '#080E2B' : '#F4F6FC');
    var b = document.querySelectorAll('[data-theme-toggle]');
    for (var i = 0; i < b.length; i++) b[i].setAttribute('aria-pressed', t === 'dark' ? 'true' : 'false');
  }

  apply(initial()); // sahifa chizilishidan oldin

  var ICON =
    '<svg class="i-moon" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 14.5A8 8 0 0 1 9.5 4 8 8 0 1 0 20 14.5Z"/></svg>' +
    '<svg class="i-sun" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2.5v2M12 19.5v2M4.2 4.2l1.4 1.4M18.4 18.4l1.4 1.4M2.5 12h2M19.5 12h2M4.2 19.8l1.4-1.4M18.4 5.6l1.4-1.4"/></svg>';

  function build() {
    if (document.querySelector('[data-theme-toggle]')) return;
    var anchor = document.querySelector('.nav .lang-switch');
    var nav = document.querySelector('.nav');
    if (!anchor && !nav) return;
    var lang = (root.getAttribute('lang') || 'uz').slice(0, 2);
    var btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'theme-toggle';
    btn.setAttribute('data-theme-toggle', '');
    btn.setAttribute('aria-label', LABELS[lang] || LABELS.uz);
    btn.setAttribute('title', LABELS[lang] || LABELS.uz);
    btn.innerHTML = ICON;
    if (anchor) anchor.parentNode.insertBefore(btn, anchor.nextSibling);
    else nav.appendChild(btn);
    apply(root.getAttribute('data-theme') || 'light');
  }

  document.addEventListener('click', function (e) {
    var t = e.target.closest ? e.target.closest('[data-theme-toggle]') : null;
    if (!t) return;
    var next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
    save(next);
    apply(next);
  });

  if (mq && mq.addEventListener) {
    mq.addEventListener('change', function (e) {
      if (!stored()) apply(e.matches ? 'dark' : 'light');
    });
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', build);
  else build();
})();
