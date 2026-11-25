
(function () {
  const KEY = 'webmood-tetris-board';
  function loadBoard() {
    try { return JSON.parse(localStorage.getItem(KEY) || '[]'); } catch (e) { return []; }
  }
  function saveScore(score) {
    const list = loadBoard();
    list.push({ score: score, at: Date.now() });
    list.sort((a, b) => b.score - a.score);
    const top = list.slice(0, 8);
    localStorage.setItem(KEY, JSON.stringify(top));
    return top;
  }
  function renderBoard(el) {
    if (!el) return;
    const list = loadBoard();
    el.innerHTML = list.length
      ? list.map((r, i) => '<li>#' + (i + 1) + ' — <b>' + r.score + '</b></li>').join('')
      : '<li>Hali rekord yoq</li>';
  }
  function best() {
    const list = loadBoard();
    return list[0] ? list[0].score : 0;
  }

  const SHAPES = {
    I: [[1,1,1,1]], O: [[1,1],[1,1]], T: [[0,1,0],[1,1,1]],
    S: [[0,1,1],[1,1,0]], Z: [[1,1,0],[0,1,1]], J: [[1,0,0],[1,1,1]], L: [[0,0,1],[1,1,1]]
  };
  const COLORS = { I:'#5EE0D2', O:'#F4B63F', T:'#8FA6FF', S:'#3DD6B0', Z:'#FF7A7A', J:'#5B7BFF', L:'#FFB86B' };

  function mount(canvasId, scoreId, lvlId, startId, bestId) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return null;
    const ctx = canvas.getContext('2d');
    const COLS = 10, ROWS = 20, SIZE = canvas.width / COLS;
    let grid, piece, score, lvl, drop, last, playing, raf;

    function empty() { return Array.from({length: ROWS}, () => Array(COLS).fill(null)); }
    function spawn() {
      const keys = Object.keys(SHAPES);
      const type = keys[Math.floor(Math.random() * keys.length)];
      piece = { type, cells: SHAPES[type].map(r => r.slice()), x: 3, y: 0 };
      if (collide(piece.x, piece.y, piece.cells)) end();
    }
    function collide(x, y, cells) {
      for (let r = 0; r < cells.length; r++) {
        for (let c = 0; c < cells[r].length; c++) {
          if (!cells[r][c]) continue;
          const nx = x + c, ny = y + r;
          if (nx < 0 || nx >= COLS || ny >= ROWS || (ny >= 0 && grid[ny][nx])) return true;
        }
      }
      return false;
    }
    function merge() {
      piece.cells.forEach((row, r) => row.forEach((v, c) => {
        if (v && piece.y + r >= 0) grid[piece.y + r][piece.x + c] = piece.type;
      }));
    }
    function rotate(m) { return m[0].map((_, i) => m.map(row => row[i]).reverse()); }
    function sweep() {
      let lines = 0;
      for (let r = ROWS - 1; r >= 0; r--) {
        if (grid[r].every(Boolean)) { grid.splice(r, 1); grid.unshift(Array(COLS).fill(null)); lines++; r++; }
      }
      if (lines) {
        score += [0,100,300,500,800][lines] * lvl;
        if (score >= lvl * 800) lvl++;
        drop = Math.max(120, 800 - lvl * 70);
      }
    }
    function move(dx, dy) {
      if (!playing) return false;
      if (!collide(piece.x + dx, piece.y + dy, piece.cells)) { piece.x += dx; piece.y += dy; return true; }
      return false;
    }
    function hardRot() {
      if (!playing) return;
      const next = rotate(piece.cells);
      if (!collide(piece.x, piece.y, next)) piece.cells = next;
    }
    function tick() { if (!move(0, 1)) { merge(); sweep(); spawn(); } }
    function drawCell(x, y, color) {
      ctx.fillStyle = color;
      ctx.fillRect(x * SIZE + 1, y * SIZE + 1, SIZE - 2, SIZE - 2);
      ctx.fillStyle = 'rgba(255,255,255,.18)';
      ctx.fillRect(x * SIZE + 2, y * SIZE + 2, 8, 4);
    }
    function hud() {
      const s = document.getElementById(scoreId); if (s) s.textContent = score;
      const l = document.getElementById(lvlId); if (l) l.textContent = lvl;
      const b = document.getElementById(bestId); if (b) b.textContent = Math.max(best(), score);
    }
    function draw() {
      ctx.fillStyle = '#0A1239';
      ctx.fillRect(0, 0, canvas.width, canvas.height);
      grid.forEach((row, y) => row.forEach((v, x) => { if (v) drawCell(x, y, COLORS[v]); }));
      if (piece) piece.cells.forEach((row, r) => row.forEach((v, c) => {
        if (v) drawCell(piece.x + c, piece.y + r, COLORS[piece.type]);
      }));
      hud();
    }
    function end() {
      playing = false;
      saveScore(score);
      renderBoard(document.getElementById('tet-board'));
      hud();
    }
    function loop(t) {
      if (!playing) { draw(); return; }
      if (t - last > drop) { tick(); last = t; }
      draw();
      raf = requestAnimationFrame(loop);
    }
    function start() {
      grid = empty(); score = 0; lvl = 1; drop = 700; last = 0; playing = true;
      spawn();
      cancelAnimationFrame(raf);
      raf = requestAnimationFrame(loop);
      renderBoard(document.getElementById('tet-board'));
    }
    document.getElementById(startId) && document.getElementById(startId).addEventListener('click', start);
    window.addEventListener('keydown', (e) => {
      if (!playing) return;
      if (['ArrowLeft','ArrowRight','ArrowDown','ArrowUp',' '].includes(e.key)) e.preventDefault();
      if (e.key === 'ArrowLeft') move(-1, 0);
      if (e.key === 'ArrowRight') move(1, 0);
      if (e.key === 'ArrowDown') move(0, 1);
      if (e.key === 'ArrowUp' || e.key === ' ') hardRot();
      draw();
    });
    grid = empty(); score = 0; lvl = 1; playing = false; draw();
    hud();
    return { start: start };
  }

  const page = mount('tetris', 'tet-score', 'tet-lvl', 'tet-start', 'tet-best');
  const lock = mount('tetris-lock', 'tet-lock-score', 'tet-lock-lvl', 'tet-lock-start', 'tet-lock-best');
  window.webmoodTetrisStartLock = function () {
    renderBoard(document.getElementById('tet-board'));
    if (lock) lock.start();
  };
  renderBoard(document.getElementById('tet-board'));
  const b1 = document.getElementById('tet-best'); if (b1) b1.textContent = best();
  const b2 = document.getElementById('tet-lock-best'); if (b2) b2.textContent = best();

  document.querySelectorAll('.tetris-keys button').forEach((btn) => {
    btn.addEventListener('click', () => {
      const ev = { ArrowLeft: 'ArrowLeft', right: 'ArrowRight', down: 'ArrowDown', rot: 'ArrowUp' };
    });
  });
})();
