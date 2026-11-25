/* WEBmood — kursor: sakkiz qirrali yulduz (khatam) orqadan ergashadi.
   Faqat haqiqiy sichqoncha harakatida yoqiladi (sensorli ekranlarda ishlamaydi). */
(function () {
  if (window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches) return;

  var LINK = 'a,button,[role="button"],summary,label,select,.skill,.project,.filter,.hamburger,.theme-toggle,.close,.user-chip,.ai-fab';
  var TEXT = 'input:not([type=button]):not([type=submit]):not([type=checkbox]):not([type=radio]):not([type=file]),textarea,[contenteditable="true"]';

  var root = null, x = 0, y = 0, tx = 0, ty = 0, seen = false;

  function build() {
    root = document.createElement('div');
    root.className = 'wm-cursor';
    root.setAttribute('aria-hidden', 'true');
    var star = document.createElement('div');
    star.className = 'wm-cursor-star';
    root.appendChild(star);
    document.body.appendChild(root);
    document.documentElement.classList.add('wm-cursor-on');
    (function loop() {
      x += (tx - x) * 0.18;
      y += (ty - y) * 0.18;
      root.style.transform = 'translate3d(' + x.toFixed(1) + 'px,' + y.toFixed(1) + 'px,0)';
      requestAnimationFrame(loop);
    })();
  }

  document.addEventListener('pointermove', function (e) {
    if (e.pointerType && e.pointerType !== 'mouse') return;
    if (!root) { if (!document.body) return; build(); }
    tx = e.clientX; ty = e.clientY;
    if (!seen) { seen = true; x = tx; y = ty; }
    root.classList.add('is-on');
    var el = e.target;
    var isText = el.closest && el.closest(TEXT);
    var isLink = !isText && el.closest && el.closest(LINK);
    root.classList.toggle('is-text', !!isText);
    root.classList.toggle('is-link', !!isLink);
  }, { passive: true });

  document.addEventListener('pointerdown', function (e) {
    if (root && (!e.pointerType || e.pointerType === 'mouse')) root.classList.add('is-down');
  });
  document.addEventListener('pointerup', function () { if (root) root.classList.remove('is-down'); });
  document.documentElement.addEventListener('mouseleave', function () { if (root) root.classList.remove('is-on'); });
})();
