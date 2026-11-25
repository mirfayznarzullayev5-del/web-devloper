(function () {

  /* ---- Toshkent vaqti va tashrif statistikasi ---- */
  (function hud() {
    const clock = document.getElementById('hud-clock');
    const dateEl = document.getElementById('hud-date');
    function tick() {
      const parts = new Intl.DateTimeFormat('en-GB', {
        timeZone: 'Asia/Tashkent', hour: '2-digit', minute: '2-digit', second: '2-digit',
        hour12: false, day: '2-digit', month: 'short'
      }).formatToParts(new Date());
      const g = (t) => (parts.find(p => p.type === t) || {}).value || '00';
      if (clock) {
        const ems = clock.querySelectorAll('em');
        if (ems[0]) ems[0].textContent = g('hour');
        if (ems[1]) ems[1].textContent = g('minute');
        if (ems[2]) ems[2].textContent = g('second');
      }
      if (dateEl) dateEl.textContent = (g('day') + ' ' + g('month')).toUpperCase();
    }
    tick();
    setInterval(tick, 1000);
    async function loadStats() {
      try {
        const r = await fetch('stats.php?action=visit');
        const d = await r.json();
        const b = document.getElementById('hud-bots');
        const v = document.getElementById('hud-visits');
        if (b) b.textContent = d.bots ?? 0;
        if (v) v.textContent = d.visits ?? 0;
      } catch (e) {}
    }
    loadStats();
    setInterval(loadStats, 15000);
  })();

  /* ---- xavfsizlik oynasi (o'ng tugma / F12) ---- */
  (function guard() {
    const lock = document.getElementById('sec-lock');
    const show = () => {
      if (!lock) return;
      lock.hidden = false;
      lock.classList.add('show');
    };
    document.addEventListener('contextmenu', (e) => {
      e.preventDefault();
      show();
    });
    document.addEventListener('keydown', (e) => {
      const key = e.key;
      const blocked =
        key === 'F12' ||
        (e.ctrlKey && e.shiftKey && ['I', 'J', 'C', 'i', 'j', 'c'].includes(key)) ||
        (e.ctrlKey && (key === 'u' || key === 'U')) ||
        (e.metaKey && e.altKey && ['I', 'J', 'C', 'i', 'j', 'c'].includes(key));
      if (blocked) {
        e.preventDefault();
        e.stopPropagation();
        show();
      }
    }, true);
    const closer = document.getElementById('tet-close');
    closer && closer.addEventListener('click', (e) => {
      e.stopPropagation();
      lock.classList.remove('show');
      lock.hidden = true;
    });
  })();

  /* ---- yuklanish ekrani ---- */
  const loader = document.querySelector('.loader');
  const bar = document.querySelector('.loader-bar span');
  const pct = document.getElementById('loader-pct');
  let p = 0;
  const loadTick = setInterval(() => {
    p = Math.min(100, p + 3);
    if (bar) bar.style.width = p + '%';
    if (pct) pct.textContent = String(p).padStart(2, '0') + '%';
    if (p >= 100) {
      clearInterval(loadTick);
      setTimeout(() => loader && loader.classList.add('done'), 200);
    }
  }, 40);

  /* ---- navigatsiya ---- */
  const nav = document.querySelector('.nav');
  const onScroll = () => nav && nav.classList.toggle('scrolled', window.scrollY > 20);
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();

  const hamburger = document.querySelector('.hamburger');
  const mobile = document.querySelector('.mobile-menu');
  hamburger && mobile && hamburger.addEventListener('click', () => mobile.classList.toggle('open'));
  mobile && mobile.querySelectorAll('a').forEach((a) => a.addEventListener('click', () => mobile.classList.remove('open')));

  const sections = [...document.querySelectorAll('section[id]')];
  const links = [...document.querySelectorAll('.nav-links a')];
  if (sections.length && links.length) {
    const io = new IntersectionObserver((entries) => {
      entries.forEach((e) => {
        if (!e.isIntersecting) return;
        links.forEach((l) => l.classList.toggle('active', l.getAttribute('href') === '#' + e.target.id));
      });
    }, { rootMargin: '-40% 0px -50% 0px' });
    sections.forEach((s) => io.observe(s));
  }

  /* ---- hero kubi: sichqoncha va scroll bilan aylanadi ---- */
  (function cubeRig() {
    const cube = document.querySelector('.cube');
    const stage = document.querySelector('.hero-stage');
    if (!cube) return;
    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    let ry = 28, tx = 0, ty = 0, sy = 0, visible = true;
    window.addEventListener('mousemove', (e) => {
      tx = (e.clientX / window.innerWidth - 0.5) * 28;
      ty = (e.clientY / window.innerHeight - 0.5) * -18;
    });
    window.addEventListener('scroll', () => { sy = window.scrollY * 0.12; }, { passive: true });
    if (stage && 'IntersectionObserver' in window) {
      new IntersectionObserver((en) => { visible = en[0].isIntersecting; }).observe(stage);
    }
    (function loop() {
      if (visible) {
        if (!reduce) ry += 0.35;
        cube.style.transform = `rotateX(${-18 + ty}deg) rotateY(${ry + tx + sy}deg)`;
      }
      requestAnimationFrame(loop);
    })();
  })();

  /* ---- kod yozilish animatsiyasi ---- */
  const codeLines = [
    '<span class="tok-c">// webmood.php — digital craft</span>',
    '<span class="tok-k">const</span> developer = {',
    "  name: <span class=\"tok-s\">'WEBmood'</span>,",
    "  stack: [<span class=\"tok-s\">'PHP'</span>, <span class=\"tok-s\">'JavaScript'</span>, <span class=\"tok-s\">'Three.js'</span>],",
    "  focus: <span class=\"tok-s\">'interactive experiences'</span>,",
    '  ship: (idea) => build(idea),',
    '}',
    '',
    '<span class="tok-k">function</span> <span class="tok-n">build</span>(idea) {',
    '  <span class="tok-k">return</span> compose(design, code, motion)',
    '}',
  ];
  const pre = document.querySelector('.editor pre');
  let n = 0;
  const codeTimer = setInterval(() => {
    if (!pre || n >= codeLines.length) return clearInterval(codeTimer);
    const div = document.createElement('div');
    div.innerHTML = codeLines[n] || '&nbsp;';
    pre.appendChild(div);
    n++;
  }, 180);

  /* ---- raqamlar sanagichi ---- */
  const stats = document.querySelectorAll('.stat b');
  const statsBox = document.querySelector('.stats');
  if (statsBox) {
    const so = new IntersectionObserver((entries) => {
      if (!entries[0].isIntersecting) return;
      stats.forEach((el) => {
        const target = Number(el.dataset.value || 0);
        const suffix = el.dataset.suffix || '';
        const start = performance.now();
        const run = (now) => {
          const t = Math.min(1, (now - start) / 1200);
          el.textContent = Math.round(target * t) + suffix;
          if (t < 1) requestAnimationFrame(run);
        };
        requestAnimationFrame(run);
      });
      so.disconnect();
    });
    so.observe(statsBox);
  }

  /* ---- loyihalar: filtr va tafsilot oynasi ---- */
  const filters = document.querySelectorAll('.filter');
  const cards = document.querySelectorAll('.project');
  filters.forEach((btn) => {
    btn.addEventListener('click', () => {
      filters.forEach((b) => b.classList.remove('on'));
      btn.classList.add('on');
      const f = btn.dataset.filter;
      cards.forEach((c) => {
        c.style.display = f === 'ALL' || c.dataset.category === f ? '' : 'none';
      });
    });
  });

  const modal = document.getElementById('project-modal');
  const modalBody = document.querySelector('#project-modal .modal-card-body');
  cards.forEach((card) => {
    card.addEventListener('click', () => {
      const data = JSON.parse(card.dataset.project);
      if (data.live && String(data.live).indexOf('http') === 0) {
        window.open(data.live, '_blank', 'noopener');
        return;
      }
      if (!modal || !modalBody) return;
      modalBody.innerHTML = `
        <small>${data.num} / ${data.category}</small>
        <h3>${data.title}</h3>
        <p class="m-type">${data.type}</p>
        <p class="m-row"><b>Overview.</b> ${data.overview}</p>
        <p class="m-row"><b>Challenge.</b> ${data.challenge}</p>
        <p class="m-row"><b>Solution.</b> ${data.solution}</p>
        <p class="m-row"><b>Technologies.</b> ${data.tech.join(' / ')}</p>
        <ul class="m-list">${data.features.map((f) => `<li>${f}</li>`).join('')}</ul>
        <div class="cta-row">
          <a class="btn btn-primary" href="${data.live}">Live Demo</a>
          <a class="btn btn-ghost" href="${data.github}">GitHub</a>
        </div>`;
      modal.classList.add('open');
      modal.hidden = false;
      modal.setAttribute('aria-hidden', 'false');
    });
  });
  function closeModal() {
    if (!modal) return;
    modal.classList.remove('open');
    modal.hidden = true;
    modal.setAttribute('aria-hidden', 'true');
  }
  modal && modal.addEventListener('click', (e) => {
    if (e.target === modal || e.target.classList.contains('close')) closeModal();
  });

  /* ---- texnologiya oynasi ---- */
  const skillModal = document.getElementById('skill-modal');
  const skillTitle = document.getElementById('skill-title');
  const skillInfo = document.getElementById('skill-info');
  function closeSkill() {
    if (!skillModal) return;
    skillModal.classList.remove('open');
    skillModal.hidden = true;
    skillModal.setAttribute('aria-hidden', 'true');
  }
  document.querySelectorAll('.skill[data-tech]').forEach((el) => {
    el.addEventListener('click', () => {
      if (!skillModal) return;
      if (skillTitle) skillTitle.textContent = el.dataset.tech || '';
      if (skillInfo) skillInfo.textContent = el.dataset.info || '';
      skillModal.hidden = false;
      skillModal.classList.add('open');
      skillModal.setAttribute('aria-hidden', 'false');
    });
  });
  skillModal && skillModal.addEventListener('click', (e) => {
    if (e.target === skillModal || e.target.classList.contains('close')) closeSkill();
  });
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') { closeModal(); closeSkill(); }
  });

  /* ---- aloqa formasi (agar sahifada #form bo'lsa) ---- */
  const form = document.querySelector('#form');
  const err = document.querySelector('.err');
  const ok = document.querySelector('.success');
  form && err && ok && form.addEventListener('submit', async (e) => {
    e.preventDefault();
    err.textContent = '';
    ok.hidden = true;
    const fd = new FormData(form);
    const name = String(fd.get('name') || '').trim();
    const phone = String(fd.get('phone') || '').trim();
    const desc = String(fd.get('description') || '').trim();
    if (!name || !phone || !desc) {
      err.textContent = 'Please complete required fields.';
      return;
    }
    if (!/^[+\d][\d\s-]{7,}$/.test(phone)) {
      err.textContent = 'Enter a valid phone number.';
      return;
    }
    try {
      const res = await fetch('send.php', { method: 'POST', body: fd });
      const json = await res.json();
      if (json.ok) {
        ok.hidden = false;
        form.reset();
      } else {
        err.textContent = json.message || 'Error';
      }
    } catch {
      err.textContent = 'Network error';
    }
  });
})();
