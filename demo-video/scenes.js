// ======================================================================
// PREDICTIVE INVENTORY SYSTEM FOR STEVEN HYDROTECH EXPONENT
// Official UI Simulation — Perfectly Matched to System Screenshots
// Includes Dark Mode (#10141D), 50/50 Split Login, Real Products & Analytics
// ======================================================================

const FONT = '"Inter", "Segoe UI", system-ui, sans-serif';

// App window: 1100 x 600 viewport drawn at 1.5x in 1920 x 1080 frame
const K = 1.5, OX = 135, OY = 90, VW = 1100, VH = 600, SB = 230, TB = 50;
const CX0 = SB + 20, CY0 = TB + 20;

// Theme Palettes matching Laravel Blade & Tailwind v4 (#10141D dark background)
const THEMES = {
  dark: {
    bg: '#10141D', card: '#161B26', border: '#1e2536', border2: '#2a3449',
    ink: '#ffffff', g800: '#f1f5f9', g700: '#e2e8f0', g600: '#cbd5e1',
    g500: '#94a3b8', g400: '#64748b', g300: '#475569', g200: '#334155', g100: '#1e2536', g50: '#131822',
    navActive: '#161B26', navActiveBorder: '#1e2536', navText: '#ffffff', navIdle: '#94a3b8',
    red: '#f43f5e', redBg: '#4c0519', redTx: '#fda4af', redBorder: '#881337',
    amber: '#f59e0b', amberBg: '#451a03', amberTx: '#fcd34d', amberBorder: '#78350f',
    green: '#10b981', greenBg: '#022c22', greenTx: '#6ee7b7', greenBorder: '#064e3b',
    blue: '#0284c7', blueBg: '#082f49', blueTx: '#7dd3fc', blueBorder: '#0369a1',
    purple: '#8b5cf6', purpleBg: '#2e1065', purpleTx: '#c4b5fd', purpleBorder: '#5b21b6',
    hi: '#f59e0b', topbar: '#10141D',
  },
  light: {
    bg: '#f8fafc', card: '#ffffff', border: '#e2e8f0', border2: '#cbd5e1',
    ink: '#0f172a', g800: '#1e293b', g700: '#334155', g600: '#475569',
    g500: '#64748b', g400: '#94a3b8', g300: '#cbd5e1', g200: '#e2e8f0', g100: '#f1f5f9', g50: '#f8fafc',
    navActive: '#f1f5f9', navActiveBorder: '#cbd5e1', navText: '#0f172a', navIdle: '#475569',
    red: '#dc2626', redBg: '#fee2e2', redTx: '#991b1b', redBorder: '#fca5a5',
    amber: '#d97706', amberBg: '#fef3c7', amberTx: '#92400e', amberBorder: '#fde68a',
    green: '#059669', greenBg: '#d1fae5', greenTx: '#065f46', greenBorder: '#a7f3d0',
    blue: '#0284c7', blueBg: '#e0f2fe', blueTx: '#0369a1', blueBorder: '#bae6fd',
    purple: '#7c3aed', purpleBg: '#ede9fe', purpleTx: '#5b21b6', purpleBorder: '#ddd6fe',
    hi: '#f59e0b', topbar: '#ffffff',
  }
};

const sm = (p) => p * p * (3 - 2 * p);
const eo = (p) => 1 - Math.pow(1 - p, 3);
const cl = (v, a = 0, b = 1) => Math.min(b, Math.max(a, v));
const lerp = (a, b, p) => a + (b - a) * p;

function mkP(s) {
  const T = (m) => s.time(m);
  return {
    t: s.t, T,
    on: (m, d = 0.5, delay = 0) => eo(cl((s.t - T(m) - delay) / d)),
    off: (m, d = 0.4) => 1 - eo(cl((s.t - T(m)) / d)),
    at: (m) => s.t >= T(m),
    bump: (m, w = 0.25) => Math.max(0, 1 - Math.abs(s.t - T(m) - 0.05) / w),
    track(keys, d = 0.8) {
      let cur = keys[0][1];
      for (let i = 1; i < keys.length; i++) {
        const t0 = T(keys[i][0]);
        if (s.t < t0) break;
        cur = lerp(keys[i - 1][1], keys[i][1], sm(cl((s.t - t0) / d)));
      }
      return cur;
    },
    typed(str, m, cps = 20) {
      if (!s.time(m)) return '';
      const n = Math.floor(cl((s.t - T(m)) * cps, 0, str.length));
      return str.slice(0, n);
    }
  };
}

function rr(c, x, y, w, h, r) {
  const rad = Math.min(r, w / 2, h / 2);
  c.beginPath();
  c.moveTo(x + rad, y);
  c.arcTo(x + w, y, x + w, y + h, rad);
  c.arcTo(x + w, y + h, x, y + h, rad);
  c.arcTo(x, y + h, x, y, rad);
  c.arcTo(x, y, x + w, y, rad);
  c.closePath();
}

function box(c, x, y, w, h, o = {}) {
  const { r = 8, fill, stroke, lw = 1, sh = 0 } = o;
  c.save();
  if (sh) { c.shadowColor = 'rgba(0,0,0,0.4)'; c.shadowBlur = sh; c.shadowOffsetY = sh * 0.3; }
  rr(c, x, y, w, h, r);
  if (fill) { c.fillStyle = fill; c.fill(); }
  if (stroke) { c.strokeStyle = stroke; c.lineWidth = lw; c.stroke(); }
  c.restore();
}

function tx(c, t, x, y, o = {}) {
  const { s = 14, w = 400, col = '#ffffff', a = 'left', mw } = o;
  c.save();
  c.font = `${w} ${s}px ${FONT}`;
  c.fillStyle = col;
  c.textAlign = a;
  c.textBaseline = 'middle';
  if (mw) {
    let str = String(t);
    while (c.measureText(str).width > mw && str.length > 3) str = str.slice(0, -2);
    if (str !== t) str += '…';
    c.fillText(str, x, y);
  } else {
    c.fillText(String(t), x, y);
  }
  c.restore();
}

function al(c, a, fn) { if (a <= 0.003) return; c.save(); c.globalAlpha *= cl(a); fn(); c.restore(); }
function show(c, a, fn, dy = 10) { al(c, a, () => { c.save(); c.translate(0, (1 - cl(a)) * dy); fn(); c.restore(); }); }

function wrap(c, str, x, y, maxW, lh, o = {}) {
  const words = String(str).split(' '); let line = '', yy = y, n = 0;
  for (const wd of words) {
    const test = line ? line + ' ' + wd : wd;
    if (c.measureText(test).width > maxW && line) {
      tx(c, line, x, yy, o);
      line = wd; yy += lh; n++;
    } else line = test;
  }
  if (line) { tx(c, line, x, yy, o); n++; }
  return n * lh;
}

// Glowing cyan molecular water drop cluster logo
function drawHydrotechLogo(c, x, y, size = 40) {
  c.save();
  c.translate(x, y);
  const s = size / 60;
  c.scale(s, s);

  // Glow
  c.shadowColor = '#38bdf8';
  c.shadowBlur = 15;

  // Nodes (Water bubbles)
  const bubbles = [
    { x: 0, y: -22, r: 9, col: '#7dd3fc' },
    { x: 12, y: -10, r: 7, col: '#38bdf8' },
    { x: -8, y: -2, r: 12, col: '#0284c7' },
    { x: 8, y: 12, r: 10, col: '#38bdf8' },
    { x: -4, y: 24, r: 7, col: '#7dd3fc' },
    { x: -16, y: 14, r: 5, col: '#bae6fd' },
    { x: 16, y: -22, r: 4, col: '#bae6fd' },
  ];

  // Connecting bridges
  c.strokeStyle = '#0284c7';
  c.lineWidth = 4;
  c.beginPath();
  c.moveTo(0, -22); c.lineTo(12, -10); c.lineTo(-8, -2); c.lineTo(8, 12); c.lineTo(-4, 24);
  c.stroke();

  // Nodes
  bubbles.forEach((b) => {
    c.fillStyle = b.col;
    c.beginPath();
    c.arc(b.x, b.y, b.r, 0, Math.PI * 2);
    c.fill();
    // Inner white glint
    c.fillStyle = 'rgba(255,255,255,0.7)';
    c.beginPath();
    c.arc(b.x - b.r * 0.3, b.y - b.r * 0.3, b.r * 0.3, 0, Math.PI * 2);
    c.fill();
  });

  c.restore();
}

function pill(c, label, x, y, tone = 'gray', colMode = THEMES.dark, o = {}) {
  const s = o.s || 10.5;
  const T = {
    green: { bg: colMode.greenBg, tx: colMode.greenTx, br: colMode.greenBorder },
    amber: { bg: colMode.amberBg, tx: colMode.amberTx, br: colMode.amberBorder },
    red: { bg: colMode.redBg, tx: colMode.redTx, br: colMode.redBorder },
    rose: { bg: colMode.redBg, tx: colMode.redTx, br: colMode.redBorder },
    blue: { bg: colMode.blueBg, tx: colMode.blueTx, br: colMode.blueBorder },
    purple: { bg: colMode.purpleBg, tx: colMode.purpleTx, br: colMode.purpleBorder },
    gray: { bg: colMode.g100, tx: colMode.g600, br: colMode.border },
  }[tone] || { bg: colMode.g100, tx: colMode.g600, br: colMode.border };

  c.save();
  c.font = `700 ${s}px ${FONT}`;
  const w = c.measureText(label).width + 16, h = s + 9;
  box(c, x, y - h / 2, w, h, { r: h / 2, fill: T.bg, stroke: T.br, lw: 1 });
  tx(c, label, x + w / 2, y, { s, w: 700, col: T.tx, a: 'center' });
  c.restore();
  return w;
}

function btn(c, label, x, y, w, h, colMode = THEMES.dark, o = {}) {
  const { kind = 'primary', s = 12, press = 0 } = o;
  const K2 = {
    primary: { bg: colMode.blue, tx: '#ffffff', br: colMode.blue },
    subtle: { bg: colMode.g100, tx: colMode.ink, br: colMode.border },
    ghost: { bg: 'transparent', tx: colMode.g600, br: colMode.border },
    danger: { bg: colMode.red, tx: '#ffffff', br: colMode.red },
    amber: { bg: colMode.amber, tx: '#ffffff', br: colMode.amber },
  }[kind] || { bg: colMode.blue, tx: '#ffffff', br: colMode.blue };

  const sc = 1 - press * 0.05;
  c.save();
  c.translate(x + w / 2, y + h / 2);
  c.scale(sc, sc);
  box(c, -w / 2, -h / 2, w, h, { r: 10, fill: K2.bg, stroke: K2.br, lw: 1 });
  tx(c, label, 0, 0, { s, w: 600, col: K2.tx, a: 'center' });
  c.restore();
}

function field(c, x, y, w, h, val, colMode = THEMES.dark, o = {}) {
  const f = o.focus;
  box(c, x, y, w, h, { r: 10, fill: colMode.card, stroke: f ? colMode.blue : colMode.border, lw: f ? 2 : 1.5 });
  if (o.icon === 'search') {
    c.strokeStyle = colMode.g400; c.lineWidth = 1.8;
    c.beginPath(); c.arc(x + 16, y + h / 2 - 1, 5, 0, 6.3); c.stroke();
    c.beginPath(); c.moveTo(x + 20, y + h / 2 + 3); c.lineTo(x + 24, y + h / 2 + 7); c.stroke();
  }
  const txOffset = o.icon === 'search' ? 32 : 12;
  if (!val && o.ph) {
    tx(c, o.ph, x + txOffset, y + h / 2 + 0.5, { s: o.s || 12, col: colMode.g400, mw: w - txOffset - 12 });
  } else if (val) {
    tx(c, val, x + txOffset, y + h / 2 + 0.5, { s: o.s || 12, col: colMode.ink, mw: w - txOffset - 12 });
  }
}

function select(c, x, y, w, h, val, colMode = THEMES.dark, o = {}) {
  box(c, x, y, w, h, { r: 10, fill: colMode.card, stroke: colMode.border, lw: 1.5 });
  tx(c, val, x + 12, y + h / 2 + 0.5, { s: o.s || 11.5, w: 500, col: colMode.ink, mw: w - 30 });
  c.strokeStyle = colMode.g400; c.lineWidth = 1.6;
  c.beginPath();
  c.moveTo(x + w - 16, y + h / 2 - 2);
  c.lineTo(x + w - 12, y + h / 2 + 2);
  c.lineTo(x + w - 8, y + h / 2 - 2);
  c.stroke();
}

function card(c, x, y, w, h, colMode = THEMES.dark, o = {}) {
  box(c, x, y, w, h, { r: 16, fill: colMode.card, stroke: colMode.border, lw: 1.5, sh: o.sh || 0 });
}

function table(c, x, y, w, cols, rows, colMode = THEMES.dark, o = {}) {
  const { rh = 36, hh = 32, fs = 11.5 } = o;
  const tot = cols.reduce((a, k) => a + k.w, 0);
  const xs = []; let acc = x;
  cols.forEach((k) => { xs.push(acc); acc += (w * k.w) / tot; });

  // Header
  box(c, x, y, w, hh, { r: 8, fill: colMode.g100, stroke: colMode.border });
  cols.forEach((k, i) => {
    const cw = (w * k.w) / tot;
    const right = k.a === 'right';
    tx(c, k.h.toUpperCase(), right ? xs[i] + cw - 12 : xs[i] + 12, y + hh / 2 + 0.5, {
      s: 9.5, w: 700, col: colMode.g500, a: right ? 'right' : 'left'
    });
  });

  // Rows
  rows.forEach((r, ri) => {
    const ry = y + hh + ri * rh;
    c.fillStyle = colMode.border;
    c.fillRect(x, ry + rh - 1, w, 1);

    r.forEach((cell, ci) => {
      const cw = (w * cols[ci].w) / tot, cx = xs[ci] + 12, cy = ry + rh / 2 + 0.5, right = cols[ci].a === 'right';
      if (cell && typeof cell === 'object') {
        if (cell.pill) {
          pill(c, cell.pill, cx, cy, cell.tone || 'gray', colMode);
        } else if (cell.btns) {
          let bx = right ? xs[ci] + cw - 8 : cx;
          cell.btns.forEach((b) => {
            btn(c, b.l, bx - 80, cy - 12, 80, 24, colMode, { kind: b.kind || 'subtle', s: 10 });
            bx -= 86;
          });
        } else if (cell.t) {
          tx(c, cell.t, right ? xs[ci] + cw - 12 : cx, cy, { s: fs, w: cell.w || 500, col: cell.col || colMode.ink, a: right ? 'right' : 'left' });
        }
      } else {
        tx(c, String(cell ?? '—'), right ? xs[ci] + cw - 12 : cx, cy, {
          s: fs, w: 400, col: colMode.ink, a: right ? 'right' : 'left'
        });
      }
    });
  });
}

function cursor(c, P, keys, o = {}) {
  const d = o.dur || 0.7, t0 = P.T(keys[0].at) - d;
  if (P.t < t0) return;
  const a0 = sm(cl((P.t - t0) / d));
  let cur = { x: keys[0].x, y: keys[0].y, click: false };
  for (let i = 0; i < keys.length; i++) {
    const k = keys[i];
    const st = P.T(k.at) - d;
    if (P.t >= st) {
      const prev = keys[i - 1] || k;
      const p = sm(cl((P.t - st) / d));
      cur.x = lerp(prev.x, k.x, p);
      cur.y = lerp(prev.y, k.y, p);
      if (k.click && P.at(k.at)) {
        const q = (P.t - P.T(k.at)) / 0.5;
        if (q >= 0 && q < 1) cur.click = true;
      }
    }
  }

  c.save();
  c.translate(cur.x, cur.y);
  c.globalAlpha = a0;
  if (cur.click) {
    c.strokeStyle = '#0284c7'; c.lineWidth = 2.5;
    c.beginPath(); c.arc(0, 0, 16, 0, 6.3); c.stroke();
  }
  c.fillStyle = '#ffffff'; c.strokeStyle = '#0f172a'; c.lineWidth = 1.5;
  c.beginPath(); c.moveTo(0, 0); c.lineTo(0, 17); c.lineTo(4.5, 13); c.lineTo(8, 20); c.lineTo(10.5, 19); c.lineTo(7, 12); c.lineTo(12.5, 12); c.closePath();
  c.fill(); c.stroke(); c.restore();
}

function clicks(s, keys, vol = 0.5) {
  keys.forEach((k) => { if (k.click) s.sfx('click', s.time(k.at), { gain: vol }); });
}

function cam(s, steps, d = 0.9) {
  if (!steps || !steps.length) { s.camera([]); return; }
  const list = [{ at: 0, x: 960, y: 540, zoom: 1, dur: 0.01 }];
  steps.forEach(([at, wx, wy, z]) => {
    const x = cl(OX + wx * K, 960 / z, 1920 - 960 / z), y = cl(OY + wy * K, 540 / z, 1080 - 540 / z);
    list.push({ at: s.time(at), x, y, zoom: z, dur: d });
  });
  s.camera(list);
}

// Full Authentic Sidenav Navigation Items (From Screenshot 1, 2, 3)
const NAV_GROUPS = [
  { name: 'Dashboard', icon: 'grid', route: 'home' },
  { name: 'Point of Sale', icon: 'cart', route: 'pos' },
  { name: 'Transaction History', icon: 'clock', route: 'history' },
  { name: 'Sales Records', icon: 'chart', route: 'records' },
  { name: 'Catalog Management', icon: 'folder', children: ['Products', 'Categories'] },
  { name: 'Inventory', icon: 'box', badge: 2, badgeColor: 'blue', children: ['Management', 'Restock Requests', 'Restock Approvals', 'Stock Logs'] },
  { name: 'Forecasting & EOQ', icon: 'trend', children: ['EOQ Calibration', 'Forecast Settings'] },
  { name: 'Suppliers & POs', icon: 'truck', badge: 3, badgeColor: 'rose', children: ['Purchase Requests', 'Purchase Orders', 'Suppliers Directory'] },
  { name: 'Audit Logs', icon: 'shield', route: 'audit' },
  { name: 'User Management', icon: 'users', admin: true },
  { name: 'System Backup', icon: 'database', admin: true },
  { name: 'User Guide', icon: 'book', route: 'guide' },
];

function sidebar(c, o, colMode) {
  const { active = 'Dashboard', user = 'Admin Ako', roleSubtitle = 'System Administrator' } = o;

  c.fillStyle = colMode.topbar; c.fillRect(0, 0, SB, VH);
  c.fillStyle = colMode.border; c.fillRect(SB - 1, 0, 1, VH);

  // Logo & Title
  drawHydrotechLogo(c, 24, 25, 26);
  tx(c, 'Hydrotech Predictive', 46, 20, { s: 12.5, w: 700, col: colMode.ink });
  tx(c, 'INVENTORY SYSTEM', 46, 32, { s: 8.5, w: 700, col: colMode.g500 });

  // Collapse button <
  box(c, SB - 26, 17, 18, 18, { r: 5, fill: colMode.g100, stroke: colMode.border });
  c.strokeStyle = colMode.g500; c.lineWidth = 1.5;
  c.beginPath(); c.moveTo(SB - 15, 23); c.lineTo(SB - 19, 26); c.lineTo(SB - 15, 29); c.stroke();

  c.fillStyle = colMode.border; c.fillRect(0, 48, SB, 1);

  // Nav Items
  let yy = 54;
  NAV_GROUPS.forEach((n) => {
    const isAct = n.name === active || (n.children && n.children.includes(active));
    if (isAct) {
      box(c, 8, yy, SB - 16, 26, { r: 8, fill: colMode.navActive, stroke: colMode.navActiveBorder, lw: 1 });
    }
    tx(c, n.name, 22, yy + 13, { s: 11, w: isAct ? 700 : 500, col: isAct ? colMode.navText : colMode.navIdle, mw: 145 });

    if (n.badge) {
      const bgCol = n.badgeColor === 'rose' ? '#f43f5e' : '#0284c7';
      box(c, SB - 38, yy + 5, 18, 15, { r: 7, fill: bgCol });
      tx(c, n.badge, SB - 29, yy + 12.5, { s: 9, w: 700, col: '#fff', a: 'center' });
    } else if (n.children) {
      c.strokeStyle = colMode.g400; c.lineWidth = 1.2; c.beginPath();
      c.moveTo(SB - 26, yy + 11); c.lineTo(SB - 22, yy + 14); c.lineTo(SB - 18, yy + 11); c.stroke();
    }
    yy += 27.5;
  });

  // Footer User Profile & Theme Toggle (Matching Screenshot 1 & 2)
  c.fillStyle = colMode.border; c.fillRect(0, VH - 72, SB, 1);
  tx(c, 'Hello, ' + user, 16, VH - 58, { s: 10.5, w: 600, col: colMode.ink });
  tx(c, roleSubtitle, 16, VH - 46, { s: 9, w: 600, col: colMode.blue });

  // Dark/Light toggle
  c.strokeStyle = colMode.g500; c.lineWidth = 1.2;
  tx(c, colMode === THEMES.dark ? '🌙 Dark Mode' : '☀️ Light Mode', 16, VH - 26, { s: 9.5, w: 500, col: colMode.g600 });
  // Logout
  tx(c, '🚪 Logout', SB - 50, VH - 26, { s: 9.5, w: 500, col: '#f43f5e' });
}

function topbar(c, o, colMode) {
  const { title = 'Dashboard' } = o;
  c.fillStyle = colMode.topbar; c.fillRect(SB, 0, VW - SB, TB);
  c.fillStyle = colMode.border; c.fillRect(SB, TB - 1, VW - SB, 1);

  // Breadcrumb / Title
  tx(c, title, SB + 20, TB / 2 + 0.5, { s: 15, w: 700, col: colMode.ink });

  const rightEdge = VW - 16;
  // User badge: "ADMIN System Administrator [AD]"
  box(c, rightEdge - 170, 10, 160, 30, { r: 15, fill: colMode.g100, stroke: colMode.border });
  tx(c, 'ADMIN', rightEdge - 130, 20, { s: 9.5, w: 700, col: colMode.ink });
  tx(c, 'System Administrator', rightEdge - 130, 31, { s: 7.5, w: 400, col: colMode.g500 });
  // AD Avatar
  c.fillStyle = '#0284c7'; c.beginPath(); c.arc(rightEdge - 22, 25, 11, 0, 6.3); c.fill();
  tx(c, 'AD', rightEdge - 22, 25, { s: 8.5, w: 700, col: '#ffffff', a: 'center' });

  // Notification Bell
  const bellX = rightEdge - 195;
  c.strokeStyle = colMode.g500; c.lineWidth = 1.5;
  c.beginPath(); c.arc(bellX, 24, 6, 0, 6.3); c.stroke();
  box(c, bellX + 2, 16, 6, 6, { r: 3, fill: '#0284c7' });

  // Refresh
  const refX = rightEdge - 225;
  c.strokeStyle = colMode.g500; c.lineWidth = 1.5;
  c.beginPath(); c.arc(refX, 24, 6, 0.5, 5.8); c.stroke();
}

function shell(c, o, content) {
  c.save();
  const themeMode = o.theme === 'light' ? THEMES.light : THEMES.dark;
  c.translate(OX, OY); c.scale(K, K);
  c.fillStyle = themeMode.bg; c.fillRect(0, 0, VW, VH);

  sidebar(c, o.side || {}, themeMode);
  topbar(c, o, themeMode);

  c.save();
  c.translate(CX0, CY0);
  if (o.scroll) c.translate(0, -o.scroll);
  content(c, themeMode);
  c.restore();

  if (o.overlay) {
    c.save();
    c.translate(CX0, CY0);
    o.overlay(c, themeMode);
    c.restore();
  }

  c.restore();
}

function shelfTile(c, x, y, name, stock, i, shake = 0) {
  c.save(); c.translate(shake, 0);
  box(c, x - 130, y - 200, 260, 220, { r: 18, fill: '#161B26', stroke: '#1e2536', lw: 2, sh: 16 });
  drawHydrotechLogo(c, x, y - 100, 48);
  tx(c, name, x, y - 35, { s: 20, w: 700, col: '#fff', a: 'center' });
  const col = stock === 0 ? '#f43f5e' : stock < 10 ? '#f59e0b' : '#10b981';
  box(c, x - 65, y + 2, 130, 30, { r: 15, fill: stock === 0 ? '#4c0519' : '#022c22', stroke: col, lw: 1.5 });
  tx(c, stock === 0 ? 'OUT OF STOCK' : `Stock: ${stock}`, x, y + 17, { s: 13, w: 700, col, a: 'center' });
  c.restore();
}

function stamp(c, x, y, a, p) {
  al(c, p, () => {
    c.save(); c.translate(x, y); c.rotate(-0.14); const sc = 1 + (1 - p) * 0.6; c.scale(sc, sc);
    box(c, -190, -44, 380, 88, { r: 14, stroke: '#f43f5e', lw: 7 });
    tx(c, 'OUT OF STOCK', 0, 2, { s: 48, w: 800, col: '#f43f5e', a: 'center' });
    c.restore();
  });
}

function ccStroke(c, col, pts) {
  c.strokeStyle = col; c.lineWidth = 4; c.beginPath();
  c.moveTo(pts[0][0], pts[0][1]);
  for (let i = 1; i < pts.length; i++) c.lineTo(pts[i][0], pts[i][1]);
  c.stroke();
}

// ======================================================================
// SCENE HANDLERS (24 Comprehensive Scenes)
// ======================================================================
export default {

  intro(s) {
    s.camera([]);
    const P = mkP(s);
    s.sfx('thud', s.time('#zero'), { gain: 0.8 });
    s.sfx('whoosh', s.time('#title'), { gain: 0.5 });
    s.draw({ at: 0 }, (c) => {
      const tIn = P.on('#title', 0.9);
      al(c, 1 - tIn, () => {
        const zero = P.on('#zero', 0.25);
        const shake = Math.sin(P.t * 60) * 8 * Math.max(0, 1 - (P.t - P.T('#zero')) * 3) * (P.at('#zero') ? 1 : 0);
        show(c, P.on(0.2, 0.5), () => tx(c, 'Monday morning.', 960, 150, { s: 64, w: 800, col: '#fff', a: 'center' }));
        show(c, P.on('#shelf', 0.5), () => tx(c, '“Do you have a water filter?”', 960, 235, { s: 34, w: 500, col: '#94a3b8', a: 'center' }));
        show(c, P.on('#shelf', 0.6), () => {
          box(c, 220, 790, 1480, 18, { r: 9, fill: '#334155' });
          shelfTile(c, 560, 540, 'Faucet Seal', P.at('#zero') ? 0 : 4, 0, shake);
          shelfTile(c, 960, 540, 'Micron #1', 5, 1, 0);
          shelfTile(c, 1360, 540, 'Carbon Black', 22, 2, 0);
        });
        stamp(c, 560 + shake, 540, 0, zero);
        al(c, P.on('#lost', 0.4) * (1 - P.on('#but', 0.3)), () => tx(c, 'A lost sale. An unhappy customer.', 960, 900, { s: 44, w: 800, col: '#f43f5e', a: 'center' }));
        show(c, P.on('#but', 0.6), () => tx(c, 'What if your system warned you three weeks ago?', 960, 900, { s: 44, w: 800, col: '#38bdf8', a: 'center' }));
      });
      al(c, tIn, () => {
        drawHydrotechLogo(c, 960, 320, 80);
        tx(c, 'Predictive Inventory System Using the Economic Order Quantity (EOQ)', 960, 460, { s: 48, w: 800, col: '#fff', a: 'center', mw: 1600 });
        tx(c, 'Steven Hydrotech Exponent Water Treatment and Supply Services', 960, 560, { s: 28, w: 600, col: '#0284c7', a: 'center' });
      });
    });
  },

  problem(s) {
    s.camera([]);
    const P = mkP(s);
    s.sfx('pop', s.time('#left'), { gain: 0.3 });
    s.sfx('pop', s.time('#right'), { gain: 0.3 });
    s.sfx('ding', s.time('#balance'), { gain: 0.4 });
    s.draw({ at: 0 }, (c) => {
      tx(c, 'The Inventory Balancing Act', 960, 140, { s: 52, w: 800, col: '#fff', a: 'center' });
      show(c, P.on('#left', 0.5), () => {
        box(c, 240, 260, 640, 360, { r: 24, fill: '#161B26', stroke: '#f43f5e', lw: 2.5 });
        tx(c, 'Ordering Too Little', 560, 330, { s: 32, w: 700, col: '#f43f5e', a: 'center' });
        wrap(c, 'Frequent stockouts, missed customer sales, delayed water treatment projects, and emergency high-cost rush orders.', 290, 410, 540, 38, { s: 22, col: '#cbd5e1' });
      });
      show(c, P.on('#right', 0.5), () => {
        box(c, 1040, 260, 640, 360, { r: 24, fill: '#161B26', stroke: '#f59e0b', lw: 2.5 });
        tx(c, 'Ordering Too Much', 1360, 330, { s: 32, w: 700, col: '#f59e0b', a: 'center' });
        wrap(c, 'Trapped capital, bloated storage footprint in tanks and media, shelf expiration, and soaring holding costs.', 1090, 410, 540, 38, { s: 22, col: '#cbd5e1' });
      });
      show(c, P.on('#balance', 0.6), () => {
        box(c, 560, 720, 800, 120, { r: 30, fill: '#0284c7', sh: 30 });
        tx(c, 'Target: The Economic Order Quantity (EOQ)', 960, 780, { s: 34, w: 700, col: '#fff', a: 'center' });
      });
    });
  },

  tour(s) {
    cam(s, [['#dark', 550, 300, 1.0], ['#theme', 220, 540, 1.5], ['#darkback', 550, 300, 1.0]]);
    const P = mkP(s);
    s.sfx('click', s.time('#theme'), { gain: 0.5 });
    s.sfx('click', s.time('#darkback'), { gain: 0.5 });
    s.draw({ at: 0 }, (c) => {
      const isLight = P.at('#theme') && !P.at('#darkback');
      shell(c, {
        title: 'Dashboard', theme: isLight ? 'light' : 'dark',
        side: { active: 'Dashboard', user: 'Admin Ako', roleSubtitle: 'System Administrator' },
      }, (cc, colMode) => {
        tx(cc, 'Welcome, Admin Ako', 0, 18, { s: 20, w: 700, col: colMode.ink });
        // Real KPI metrics from Screenshot 2
        const kpis = [
          ["Today's Sales", '₱0.00', 'blue', 'Daily revenue total'],
          ['Overall Sales', '₱621,475.00', 'green', 'All-time accumulated revenue'],
          ['POS Orders', '0', 'purple', 'Completed today'],
          ['Products', '5', 'blue', 'Active catalog items'],
          ['Restock Needed', '5', 'red', 'Items require attention']
        ];
        kpis.forEach((k, i) => {
          const x = i * 172; card(cc, x, 44, 162, 70, colMode);
          tx(cc, k[0], x + 12, 60, { s: 10, col: colMode.g500 });
          tx(cc, k[1], x + 12, 82, { s: 16, w: 700, col: k[2] === 'red' ? colMode.red : colMode.ink });
          tx(cc, k[3], x + 12, 98, { s: 8.5, col: colMode.g400 });
        });

        // Official Documentation Card (Screenshot 1: User Guide Preview)
        card(cc, 0, 130, 850, 230, colMode);
        tx(cc, 'Hydrotech Predictive Inventory & POS System — Operational Manual', 16, 154, { s: 14, w: 700, col: colMode.ink });
        pill(cc, 'ASSIGNED ROLE: ADMIN', 720, 154, 'blue', colMode);
        tx(cc, 'Comprehensive 4Ws framework detailing menu access, permissions, and module governance.', 16, 175, { s: 11, col: colMode.g500 });

        const cards4W = [
          ['Dashboard', 'Analytics, velocity alerts, stock tracking', 'Admin, Manager, Staff'],
          ['Point of Sale', 'Cart, projects, receipts, discounts', 'Admin, Manager, POS Staff'],
          ['Forecasting & EOQ', 'SMA windows, target batch calibration', 'Admin, Manager'],
        ];
        cards4W.forEach(([t1, w1, who], ci) => {
          const cx = ci * 280;
          card(cc, cx, 195, 270, 140, colMode);
          tx(cc, t1, cx + 12, 215, { s: 12.5, w: 700, col: colMode.ink });
          tx(cc, 'What: ' + w1, cx + 12, 240, { s: 10, col: colMode.g600, mw: 245 });
          tx(cc, 'Who: ' + who, cx + 12, 280, { s: 9.5, col: colMode.blue, mw: 245 });
        });
      });
    });
  },

  // Exact 50/50 Split Screen Login matching Screenshot 6 (media_1795101925831_f905551e.png)
  login(s) {
    s.camera([]);
    const P = mkP(s);
    const keys = [{ at: '#type', x: 1440, y: 485 }, { at: '#click', x: 1440, y: 685, click: true }];
    clicks(s, keys);
    s.draw({ at: 0 }, (c) => {
      // 50/50 Split Background
      c.fillStyle = '#10141D'; c.fillRect(0, 0, 960, 1080); // Left Dark
      c.fillStyle = '#ffffff'; c.fillRect(960, 0, 960, 1080); // Right Crisp White

      // LEFT SIDE: STEVEN HYDROTECH EXPONENT BRANDING
      drawHydrotechLogo(c, 480, 440, 120);
      tx(c, 'STEVEN HYDROTECH', 480, 240, { s: 52, w: 900, col: '#ffffff', a: 'center' });
      c.strokeStyle = '#0284c7'; c.lineWidth = 2;
      c.beginPath(); c.moveTo(340, 290); c.lineTo(400, 290); c.stroke();
      tx(c, 'E X P O N E N T', 480, 290, { s: 22, w: 700, col: '#38bdf8', a: 'center' });
      c.beginPath(); c.moveTo(560, 290); c.lineTo(620, 290); c.stroke();

      tx(c, 'WATER TREATMENT AND SUPPLY SERVICES', 480, 620, { s: 17, w: 600, col: '#7dd3fc', a: 'center' });

      // 4 Feature Cards at bottom of left screen
      const feats = [
        ['Predict Demand', 'Forecast requirements using analytics.'],
        ['Optimize Orders', 'Determine ideal restock quantities.'],
        ['Reduce Costs', 'Lower holding costs & avoid stockouts.'],
        ['Smart Decisions', 'Real-time insights for management.']
      ];
      feats.forEach(([f1, f2], fi) => {
        const fx = 60 + fi * 215;
        box(c, fx, 870, 205, 95, { r: 12, fill: '#161B26', stroke: '#1e2536' });
        tx(c, f1, fx + 14, 900, { s: 13, w: 700, col: '#38bdf8' });
        wrap(c, f2, fx + 14, 925, 175, 18, { s: 10.5, col: '#94a3b8' });
      });

      // RIGHT SIDE: SIGN IN FORM
      tx(c, 'Welcome!', 1440, 330, { s: 44, w: 800, col: '#0f172a', a: 'center' });
      tx(c, 'Sign in to your account to continue', 1440, 380, { s: 18, col: '#64748b', a: 'center' });

      // Email field
      tx(c, 'Email address or Username', 1200, 450, { s: 14, w: 600, col: '#334155' });
      const u = P.typed('admin@stevenhydrotech.com', '#type', 18);
      box(c, 1200, 470, 480, 52, { r: 12, fill: '#ffffff', stroke: P.at('#type') && !P.at('#click') ? '#0284c7' : '#e2e8f0', lw: 1.5 });
      tx(c, u || 'admin@stevenhydrotech.com', 1220, 496, { s: 16, col: '#0f172a' });

      // Password field
      tx(c, 'Password', 1200, 550, { s: 14, w: 600, col: '#334155' });
      const n = Math.floor(cl((P.t - P.T('#type') - 1.0) * 12, 0, 12));
      box(c, 1200, 570, 480, 52, { r: 12, fill: '#ffffff', stroke: '#e2e8f0', lw: 1.5 });
      tx(c, '•'.repeat(n || 12), 1220, 598, { s: 22, col: '#0f172a' });

      // Remember me
      box(c, 1200, 638, 16, 16, { r: 4, fill: '#0284c7' });
      tx(c, 'Remember Me', 1230, 646, { s: 14, col: '#64748b' });

      // Sign In Button
      const bPress = P.bump('#click');
      box(c, 1200, 680, 480, 54, { r: 12, fill: '#ffffff', stroke: '#0284c7', lw: 2 });
      tx(c, '→ Sign In to System', 1440, 707, { s: 17, w: 700, col: '#0284c7', a: 'center' });

      // Operational Roles Popup / Overlay
      const roles = [
        ['Admin', 'Full control: Users, Backups, Settings, Audit logs', '#admin', '#f43f5e'],
        ['Manager', 'Products catalog, EOQ calibration, Restock approvals, POs', '#manager', '#8b5cf6'],
        ['Staff', 'Counter POS retail & project sales, Store restock requests', '#staff', '#0284c7'],
        ['Warehouse Staff', 'Warehouse inventory, Inter-facility transfers, PRs', '#wh', '#f59e0b'],
      ];
      roles.forEach(([n1, d1, m, col], i) => {
        show(c, P.on(m, 0.45), () => {
          const ry = 750 + i * 75;
          box(c, 100, ry, 760, 65, { r: 14, fill: '#161B26', stroke: '#1e2536', sh: 15 });
          box(c, 100, ry, 8, 65, { r: 4, fill: col });
          tx(c, n1, 130, ry + 24, { s: 18, w: 700, col: '#ffffff' });
          tx(c, d1, 130, ry + 46, { s: 13, col: '#94a3b8' });
        });
      });

      cursor(c, P, keys);
    });
  },

  // Exact Dashboard matching Screenshots 2, 3, 5
  dashboard(s) {
    cam(s, [['#cards', 650, 190, 1.3], ['#chart', 650, 320, 1.25], ['#alerts', 650, 480, 1.35]]);
    const P = mkP(s);
    s.sfx('pop', s.time('#cards'), { gain: 0.3 });
    s.sfx('ding', s.time('#chart'), { gain: 0.3 });
    s.draw({ at: 0 }, (c) => {
      const scroll = P.track([[0, 0], ['#chart', 120], ['#alerts', 240]], 0.9);
      shell(c, { active: 'Dashboard', title: 'Dashboard', scroll }, (cc, colMode) => {
        tx(cc, 'Welcome, Admin Ako', 0, 18, { s: 20, w: 700, col: colMode.ink });

        // 5 Real KPI Cards (Screenshot 2)
        const kpis = [
          ["Today's Sales", '₱0.00', 'blue', 'Daily revenue total'],
          ['Overall Sales', '₱621,475.00', 'green', 'All-time accumulated revenue'],
          ['POS Orders', '0', 'purple', 'Completed today'],
          ['Products', '5', 'blue', 'Active catalog items'],
          ['Restock Needed', '5', 'red', 'Items require attention']
        ];
        kpis.forEach((k, i) => {
          const x = i * 172; card(cc, x, 44, 162, 70, colMode);
          tx(cc, k[0].toUpperCase(), x + 12, 58, { s: 9, w: 700, col: colMode.g500 });
          tx(cc, k[1], x + 12, 80, { s: 16, w: 800, col: k[2] === 'red' ? colMode.red : colMode.ink });
          tx(cc, k[3], x + 12, 98, { s: 8.5, col: colMode.g400 });
        });

        // Sales Predictions Card matching Screenshot 2 & 3
        card(cc, 0, 130, 850, 230, colMode);
        tx(cc, 'Sales Predictions', 16, 150, { s: 15, w: 700, col: colMode.ink });
        tx(cc, 'Showing All Products (Aggregate)', 16, 168, { s: 10, col: colMode.g400 });

        select(cc, 500, 142, 110, 26, 'All Categories', colMode, { s: 10 });
        select(cc, 620, 142, 130, 26, 'Next 3 Months', colMode, { s: 10 });
        btn(cc, 'Settings', 760, 142, 75, 26, colMode, { kind: 'subtle', s: 10 });

        // Legend
        tx(cc, '— Past Sales (Actual)', 16, 192, { s: 9.5, w: 600, col: '#38bdf8' });
        tx(cc, '- - Prediction (SMA)', 160, 192, { s: 9.5, w: 600, col: '#a855f7' });
        tx(cc, '■ Expected Range (Best/Worst)', 300, 192, { s: 9.5, w: 600, col: colMode.g500 });

        // Shaded Uncertainty Cone from Oct -> Jan
        cc.fillStyle = 'rgba(139,92,246,0.18)';
        cc.beginPath();
        cc.moveTo(340, 250); // Oct peak
        cc.lineTo(780, 205); // Jan high
        cc.lineTo(780, 310); // Jan low
        cc.closePath();
        cc.fill();

        // Historical Blue Curve (Aug -> Sep -> Oct)
        cc.strokeStyle = '#0284c7'; cc.lineWidth = 3;
        cc.beginPath();
        cc.moveTo(60, 320); // Aug 500
        cc.bezierCurveTo(150, 310, 180, 220, 220, 210); // Sep 2700
        cc.bezierCurveTo(260, 205, 300, 240, 340, 250); // Oct 2170
        cc.stroke();

        // Dots on past sales
        [[60, 320], [220, 210], [340, 250]].forEach(([dx, dy]) => {
          cc.fillStyle = '#0284c7'; cc.beginPath(); cc.arc(dx, dy, 4.5, 0, 6.3); cc.fill();
        });

        // Vertical Divider line at Oct
        cc.strokeStyle = '#475569'; cc.lineWidth = 1; cc.setLineDash([4, 4]);
        cc.beginPath(); cc.moveTo(340, 195); cc.lineTo(340, 330); cc.stroke();
        cc.setLineDash([]);

        // Prediction Dashed Purple Curve (Oct -> Nov -> Dec -> Jan)
        cc.strokeStyle = '#a855f7'; cc.lineWidth = 2.5; cc.setLineDash([5, 5]);
        cc.beginPath();
        cc.moveTo(340, 250);
        cc.lineTo(480, 260); // Nov
        cc.lineTo(620, 260); // Dec
        cc.lineTo(780, 260); // Jan
        cc.stroke();
        cc.setLineDash([]);

        // X-Axis Months
        ['Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'Jan'].forEach((m, mi) => {
          tx(cc, m, [60, 220, 340, 480, 620, 780][mi], 335, { s: 9.5, col: colMode.g400, a: 'center' });
        });

        // Active Tooltip on Oct (Screenshot 3)
        if (P.at('#chart')) {
          box(cc, 300, 210, 190, 52, { r: 8, fill: '#0f172a', stroke: '#1e2536', sh: 12 });
          tx(cc, 'Oct Actual: 2170 units/mo', 312, 226, { s: 9.5, w: 700, col: '#38bdf8' });
          tx(cc, 'Oct Prediction: 2170 units/mo', 312, 246, { s: 9.5, w: 600, col: '#c084fc' });
        }

        // Inventory Alerts Table matching Screenshot 5
        card(cc, 0, 375, 850, 195, colMode);
        tx(cc, 'Inventory Alerts', 16, 395, { s: 14, w: 700, col: colMode.ink });
        tx(cc, 'Combined store, warehouse, and total stock tracking', 16, 410, { s: 9.5, col: colMode.g400 });
        pill(cc, '5 Products', 760, 395, 'gray', colMode);

        table(cc, 0, 420, 850, [
          { h: 'Product Name', w: 0.25 }, { h: 'Category', w: 0.18 }, { h: 'Store Status', w: 0.18 }, { h: 'Warehouse Status', w: 0.18 }, { h: 'Current Stock / ROP', w: 0.21, a: 'right' }
        ], [
          ['Micron #5 (MIC-640E24)', 'Uncategorized', { pill: 'LOW STOCK', tone: 'amber' }, { pill: 'LOW STOCK', tone: 'amber' }, 'Store 10 • WH 10 (ROP: 10)'],
          ['Micron #1 (MIC-EA2E3D)', 'Uncategorized', { pill: 'LOW STOCK', tone: 'amber' }, { pill: 'LOW STOCK', tone: 'amber' }, 'Store 5 • WH 3 (ROP: 10)'],
          ['Faucet Seal (FAU-297C37)', 'Uncategorized', { pill: 'OUT OF STOCK', tone: 'red' }, { pill: 'OUT OF STOCK', tone: 'red' }, 'Store 0 • WH 0 (ROP: 10)'],
          ['Carbon Black (PRD-566E2Q)', 'Uncategorized', { pill: 'IN STOCK', tone: 'green' }, { pill: 'OUT OF STOCK', tone: 'red' }, 'Store 22 • WH 0 (ROP: 10)'],
        ], colMode, { rh: 34 });
      });
    });
  },

  // Exact Fast & Slow Moving Products matching Screenshot 4
  velocity(s) {
    cam(s, [['#fast', 500, 260, 1.3], ['#slow', 800, 260, 1.3]]);
    const P = mkP(s);
    s.draw({ at: 0 }, (c) => {
      shell(c, { active: 'Dashboard', title: 'Fast and Slow Moving Products' }, (cc, colMode) => {
        tx(cc, '⚡ Fast and Slow Moving Products', 0, 18, { s: 18, w: 700, col: colMode.ink });
        tx(cc, 'Fast items sorted by sales velocity • Slow items sorted by stock quantity', 0, 34, { s: 10.5, col: colMode.g400 });

        select(cc, 500, 12, 140, 26, 'Uncategorized (default)', colMode, { s: 9.5 });
        select(cc, 650, 12, 90, 26, '3 Months', colMode, { s: 9.5 });
        btn(cc, 'Show Both', 750, 12, 90, 26, colMode, { kind: 'primary', s: 10 });

        // Left Panel: FAST MOVING PRODUCTS
        card(cc, 0, 48, 415, 305, colMode);
        pill(cc, '● FAST MOVING PRODUCTS', 14, 70, 'green', colMode);
        tx(cc, 'High Sales', 380, 70, { s: 10, w: 700, col: colMode.green, a: 'right' });

        const fastItems = [
          ['#1 Faucet Seal (FAU-297C37)', '3220 sold • 0.00 in stock', 1.0, 'Fast Velocity'],
          ['#2 Small Cap Seal (SMA-FAE299)', '1415 sold • 0.00 in stock', 0.44, 'Fast Velocity'],
          ['#3 Micron #5 (MIC-640E24)', '480 sold • 20.00 in stock', 0.16, 'Fast Velocity'],
          ['#4 Micron #1 (MIC-EA2E3D)', '400 sold • 8.00 in stock', 0.12, 'Active Movement'],
        ];
        fastItems.forEach(([it, sub, pct, tag], fi) => {
          const iy = 94 + fi * 52;
          tx(cc, it, 14, iy, { s: 11, w: 700, col: colMode.ink });
          tx(cc, sub, 395, iy, { s: 9.5, col: colMode.g400, a: 'right' });
          box(cc, 14, iy + 10, 385, 6, { r: 3, fill: colMode.g100 });
          box(cc, 14, iy + 10, 385 * pct, 6, { r: 3, fill: colMode.green });
          tx(cc, tag, 14, iy + 24, { s: 8.5, col: colMode.green });
        });

        // Right Panel: SLOW MOVING PRODUCTS
        card(cc, 435, 48, 415, 305, colMode);
        pill(cc, '● SLOW MOVING PRODUCTS', 450, 70, 'amber', colMode);
        tx(cc, 'High Stock', 815, 70, { s: 10, w: 700, col: colMode.amber, a: 'right' });

        const slowItems = [
          ['#1 Carbon Black (PRD-566E2Q)', '22.00 in stock • 0 sold', 1.0, '⚠️ 180+ days supply'],
          ['#2 Micron #1 (MIC-EA2E3D)', '8.00 in stock • 400 sold', 0.36, '⚠️ Active movement'],
          ['#3 Micron #5 (MIC-640E24)', '20.00 in stock • 480 sold', 0.45, '⚠️ Active movement'],
          ['#4 Small Cap Seal (SMA-FAE299)', '0.00 in stock • 1415 sold', 0.0, '⚠️ Low stock'],
        ];
        slowItems.forEach(([it, sub, pct, tag], fi) => {
          const iy = 94 + fi * 52;
          tx(cc, it, 450, iy, { s: 11, w: 700, col: colMode.ink });
          tx(cc, sub, 830, iy, { s: 9.5, col: colMode.g400, a: 'right' });
          box(cc, 450, iy + 10, 385, 6, { r: 3, fill: colMode.g100 });
          if (pct > 0) box(cc, 450, iy + 10, 385 * pct, 6, { r: 3, fill: colMode.amber });
          tx(cc, tag, 450, iy + 24, { s: 8.5, col: colMode.amber });
        });
      });
    });
  },

  // Exact Counter POS matching iPad & Desktop POS Screenshots
  pos(s) {
    cam(s, [['#dual', 550, 120, 1.3], ['#click1', 450, 300, 1.3], ['#cart', 780, 320, 1.3]]);
    const P = mkP(s);
    const keys = [{ at: '#click1', x: CX0 + 80, y: CY0 + 170, click: true }];
    clicks(s, keys);
    s.draw({ at: 0 }, (c) => {
      shell(c, { active: 'Point of Sale', title: 'Point of Sale', overlay: (cc) => cursor(cc, P, keys) }, (cc, colMode) => {
        // Mode switch capsule & status badges
        box(cc, 0, 6, 210, 28, { r: 14, fill: colMode.g100, stroke: colMode.border });
        btn(cc, 'Retail Sale', 2, 8, 100, 24, colMode, { kind: P.at('#project') ? 'ghost' : 'primary', s: 10.5 });
        btn(cc, 'Project / Service', 104, 8, 104, 24, colMode, { kind: P.at('#project') ? 'primary' : 'ghost', s: 10.5 });

        pill(cc, '● Counter POS', 225, 20, 'green', colMode);
        pill(cc, '⏱ Parked Orders (0)', 340, 20, 'amber', colMode);

        // Search & Filter Bar
        field(cc, 0, 44, 260, 30, '', colMode, { icon: 'search', ph: 'Search items or categories...' });
        select(cc, 270, 44, 150, 30, 'All Categories', colMode, { s: 11 });

        // Product Cards (Exact 5 Products from system!)
        const prods = [
          ['Carbon Black', 'UNCATEGORIZED', '22', '₱450.00', 'green', 'IN STOCK'],
          ['Faucet Seal', 'SEALER > UNCATEGORIZED', '0', '₱125.00', 'red', 'OUT OF STOCK'],
          ['Micron #1', 'UNCATEGORIZED', '5', '₱80.00', 'amber', 'LOW STOCK'],
          ['Micron #5', 'UNCATEGORIZED', '10', '₱80.00', 'green', 'IN STOCK'],
        ];
        prods.forEach((p, i) => {
          const x = (i % 2) * 210, y = 86 + Math.floor(i / 2) * 125;
          card(cc, x, y, 200, 115, colMode);
          // Image placeholder box
          box(cc, x + 8, y + 8, 50, 50, { r: 10, fill: colMode.g100 });
          drawHydrotechLogo(cc, x + 33, y + 33, 24);

          // Stock Badge & Big Number
          pill(cc, p[5], x + 105, y + 16, p[4], colMode, { s: 8.5 });
          tx(cc, p[2], x + 185, y + 36, { s: 20, w: 800, col: p[4] === 'red' ? colMode.red : colMode.ink, a: 'right' });
          tx(cc, 'UNIT : PIECE', x + 185, y + 50, { s: 7.5, w: 700, col: colMode.green, a: 'right' });

          // Product Name & Category
          tx(cc, p[0], x + 8, y + 70, { s: 12, w: 700, col: colMode.ink });
          tx(cc, p[1], x + 8, y + 82, { s: 8.5, col: colMode.g400, mw: 180 });

          // Price & Capsule Buttons
          tx(cc, 'PRICE: ' + p[3], x + 8, y + 102, { s: 11, w: 800, col: colMode.ink });
          if (p[4] === 'red') {
            btn(cc, 'Unavailable', x + 120, y + 92, 72, 20, colMode, { kind: 'subtle', s: 8.5 });
          } else {
            box(cc, x + 120, y + 92, 72, 20, { r: 10, fill: colMode.g100, stroke: colMode.border });
            tx(cc, 'DROP | ADD', x + 156, y + 102, { s: 8.5, w: 700, col: colMode.green, a: 'center' });
          }
        });

        // Order Summary Sidebar (Right)
        card(cc, 440, 44, 410, 360, colMode);
        tx(cc, 'Order Summary', 456, 68, { s: 15, w: 800, col: colMode.ink });
        pill(cc, '1 item', 820, 68, 'blue', colMode);

        const hasItem = P.at('#click1');
        if (hasItem) {
          card(cc, 456, 92, 378, 60, colMode);
          tx(cc, 'Carbon Black (PRD-566E2Q)', 470, 112, { s: 12, w: 700, col: colMode.ink });
          tx(cc, '1 × ₱450.00', 470, 130, { s: 10.5, col: colMode.g400 });
          tx(cc, '₱450.00', 818, 120, { s: 14, w: 700, col: colMode.ink, a: 'right' });

          // Calculation totals
          tx(cc, 'Subtotal', 470, 200, { s: 11, col: colMode.g500 });
          tx(cc, '₱450.00', 818, 200, { s: 11, col: colMode.ink, a: 'right' });
          tx(cc, 'Discounts (Configure)', 470, 222, { s: 11, col: colMode.green });
          tx(cc, '- ₱0.00', 818, 222, { s: 11, col: colMode.green, a: 'right' });
          tx(cc, 'Tax (12% VAT)', 470, 244, { s: 11, col: colMode.g500 });
          tx(cc, '₱48.21', 818, 244, { s: 11, col: colMode.ink, a: 'right' });

          cc.fillStyle = colMode.border; cc.fillRect(470, 265, 350, 1);
          tx(cc, 'Total Amount', 470, 285, { s: 14, w: 800, col: colMode.ink });
          tx(cc, '₱450.00', 818, 285, { s: 20, w: 800, col: colMode.ink, a: 'right' });

          btn(cc, 'Proceed to Checkout →', 470, 315, 350, 38, colMode, { kind: 'primary', s: 13 });
        } else {
          tx(cc, 'Your cart is empty. Use Drop/Add buttons on product cards.', 645, 180, { s: 12, col: colMode.g400, a: 'center', mw: 340 });
        }
      });
    });
  },

  parked(s) {
    cam(s, [['#park', 700, 200, 1.3], ['#restore', 700, 200, 1.3]]);
    const P = mkP(s);
    s.sfx('click', s.time('#park'), { gain: 0.4 });
    s.sfx('pop', s.time('#restore'), { gain: 0.4 });
    s.draw({ at: 0 }, (c) => {
      shell(c, { active: 'Point of Sale', title: 'Parked Orders & Discounts' }, (cc, colMode) => {
        card(cc, 150, 40, 550, 280, colMode);
        tx(cc, 'Parked Orders Manager', 180, 75, { s: 17, w: 700, col: colMode.ink });
        pill(cc, '2 Orders Currently Parked', 500, 75, 'amber', colMode);

        table(cc, 180, 110, 490, [
          { h: 'Order #', w: 0.25 }, { h: 'Items', w: 0.45 }, { h: 'Action', w: 0.3 }
        ], [
          ['Order #104', '1x Carbon Black, 2x Micron #5', { btns: [{ l: 'Restore', kind: 'primary' }] }],
          ['Order #105', '3x Micron #1', { btns: [{ l: 'Restore', kind: 'ghost' }] }],
        ], colMode, { rh: 44 });
      });
    });
  },

  checkout(s) {
    cam(s, [['#tender', 650, 240, 1.3], ['#receipt', 650, 260, 1.3], ['#magic', 650, 260, 1.25]]);
    const P = mkP(s);
    s.sfx('ding', s.time('#receipt'), { gain: 0.5 });
    s.draw({ at: 0 }, (c) => {
      shell(c, { active: 'Point of Sale', title: 'Checkout & Official Receipt' }, (cc, colMode) => {
        const mx = 425;
        if (!P.at('#magic')) {
          card(cc, mx - 170, 20, 340, 330, colMode);
          drawHydrotechLogo(cc, mx, 45, 24);
          tx(cc, 'STEVEN HYDROTECH EXPONENT', mx, 68, { s: 11, w: 800, col: colMode.ink, a: 'center' });
          tx(cc, 'OFFICIAL RECEIPT', mx, 84, { s: 10, col: colMode.blue, a: 'center' });
          tx(cc, 'TXN-2026-0042', mx, 102, { s: 10.5, w: 700, col: colMode.g600, a: 'center' });
          cc.fillStyle = colMode.border; cc.fillRect(mx - 150, 115, 300, 1);

          tx(cc, '1x Carbon Black (PRD-566E2Q)', mx - 150, 135, { s: 11, col: colMode.ink });
          tx(cc, '₱450.00', mx + 150, 135, { s: 11, w: 600, col: colMode.ink, a: 'right' });

          cc.fillStyle = colMode.border; cc.fillRect(mx - 150, 160, 300, 1);
          tx(cc, 'Total Due:', mx - 150, 185, { s: 12, col: colMode.g500 });
          tx(cc, '₱450.00', mx + 150, 185, { s: 13, w: 700, col: colMode.ink, a: 'right' });
          tx(cc, 'Cash Tendered:', mx - 150, 210, { s: 12, col: colMode.g500 });
          tx(cc, '₱500.00', mx + 150, 210, { s: 13, w: 700, col: colMode.ink, a: 'right' });
          tx(cc, 'Change:', mx - 150, 235, { s: 12, w: 700, col: colMode.green });
          tx(cc, '₱50.00', mx + 150, 235, { s: 15, w: 800, col: colMode.green, a: 'right' });

          btn(cc, 'Print Official Receipt', mx - 150, 270, 300, 34, colMode, { kind: 'primary' });
        } else {
          // Automated Backend Operations
          card(cc, mx - 220, 50, 440, 240, colMode);
          tx(cc, 'AUTOMATED BACKEND OPERATIONS', mx, 80, { s: 13, w: 800, col: colMode.blue, a: 'center' });
          box(cc, mx - 190, 105, 380, 55, { r: 8, fill: colMode.g100, stroke: colMode.greenBorder });
          tx(cc, 'Store Stock Decremented', mx - 170, 125, { s: 13, w: 700, col: colMode.green });
          tx(cc, 'Carbon Black: 22 units → 21 units', mx - 170, 142, { s: 11.5, col: colMode.g500 });

          box(cc, mx - 190, 175, 380, 55, { r: 8, fill: colMode.g100, stroke: colMode.blueBorder });
          tx(cc, 'Demand Record Generated', mx - 170, 195, { s: 13, w: 700, col: colMode.blue });
          tx(cc, 'Quantity 1 logged to demand database for SMA forecast', mx - 170, 212, { s: 11.5, col: colMode.g500 });
        }
      });
    });
  },

  history(s) {
    cam(s, [['#table', 550, 250, 1.3]]);
    const P = mkP(s);
    s.draw({ at: 0 }, (c) => {
      shell(c, { active: 'Transaction History', title: 'Transaction History' }, (cc, colMode) => {
        card(cc, 0, 30, 850, 280, colMode);
        tx(cc, 'Sales Transactions Audit Ledger', 16, 55, { s: 16, w: 700, col: colMode.ink });
        table(cc, 0, 75, 850, [
          { h: 'Txn ID', w: 0.18 }, { h: 'Date & Time', w: 0.22 }, { h: 'Cashier', w: 0.18 }, { h: 'Total', w: 0.16 }, { h: 'Actions', w: 0.26 }
        ], [
          ['TXN-2026-0042', 'Nov 19, 2026 2:15 PM', 'Admin Ako', '₱450.00', { btns: [{ l: 'Re-print Receipt', kind: 'ghost' }] }],
          ['TXN-2026-0041', 'Nov 19, 2026 1:40 PM', 'Staff Maria', '₱1,250.00', { btns: [{ l: 'Re-print Receipt', kind: 'ghost' }] }],
          ['TXN-2026-0040', 'Nov 19, 2026 11:20 AM', 'Admin Ako', '₱80.00', { btns: [{ l: 'Re-print Receipt', kind: 'ghost' }] }],
        ], colMode, { rh: 44 });
      });
    });
  },

  // Exact Product Management & Catalog matching Screenshots 9, 10, 11
  catalog(s) {
    cam(s, [['#table', 550, 240, 1.25], ['#csv', 550, 240, 1.25]]);
    const P = mkP(s);
    s.draw({ at: 0 }, (c) => {
      shell(c, { active: 'Catalog Management', title: 'Product Inventory Catalog' }, (cc, colMode) => {
        btn(cc, '+ Add Product', 720, 10, 130, 28, colMode, { kind: 'primary', s: 11 });
        btn(cc, 'Import CSV', 610, 10, 100, 28, colMode, { kind: 'ghost', s: 11 });

        // Filter row
        field(cc, 0, 10, 220, 28, '', colMode, { icon: 'search', ph: 'Search products or SKU...' });
        select(cc, 230, 10, 120, 28, 'All Categories', colMode, { s: 10 });
        select(cc, 360, 10, 100, 28, 'All Status', colMode, { s: 10 });
        select(cc, 470, 10, 100, 28, 'Sort: Name', colMode, { s: 10 });

        // Grid cards of the real 5 products
        const items = [
          ['Carbon Black', 'PRD-566E2Q', 'Store 22 - WH 0', '₱450.00', 'In Stock (22)', 'green'],
          ['Faucet Seal', 'FAU-297C37', 'Store 0 - WH 0', '₱125.00', 'Out of Stock', 'red'],
          ['Micron #1', 'MIC-EA2E3D', 'Store 5 - WH 3', '₱80.00', 'Low (8)', 'amber'],
          ['Micron #5', 'MIC-640E24', 'Store 10 - WH 10', '₱80.00', 'In Stock (20)', 'green'],
          ['Small Cap Seal', 'SMA-FAE299', 'Store 0 - WH 0', '₱105.00', 'Out of Stock', 'red'],
        ];
        items.forEach(([nm, sku, st, pr, pillTxt, pillCol], i) => {
          const x = i * 168, y = 50;
          card(cc, x, y, 160, 240, colMode);
          box(cc, x + 10, y + 10, 140, 80, { r: 10, fill: colMode.g100 });
          drawHydrotechLogo(cc, x + 80, y + 50, 32);

          tx(cc, nm, x + 10, y + 105, { s: 11.5, w: 700, col: colMode.ink });
          tx(cc, sku, x + 10, y + 120, { s: 9, col: colMode.g500 });
          tx(cc, st, x + 10, y + 135, { s: 9, col: colMode.g400 });

          pill(cc, pillTxt, x + 10, y + 160, pillCol, colMode, { s: 8.5 });
          tx(cc, pr, x + 10, y + 190, { s: 13, w: 800, col: colMode.ink });

          // Edit & Delete buttons
          btn(cc, 'Edit', x + 10, y + 208, 65, 22, colMode, { kind: 'subtle', s: 9 });
          btn(cc, 'Delete', x + 85, y + 208, 65, 22, colMode, { kind: 'danger', s: 9 });
        });
      });
    });
  },

  // Exact Inventory Management matching Screenshots 13, 14, 15
  inventory(s) {
    cam(s, [['#table', 550, 250, 1.25], ['#transfer', 550, 250, 1.35]]);
    const P = mkP(s);
    s.draw({ at: 0 }, (c) => {
      shell(c, { active: 'Inventory', title: 'Inventory Management' }, (cc, colMode) => {
        btn(cc, '+ Restock Mode', 710, 10, 140, 28, colMode, { kind: 'primary', s: 11 });
        btn(cc, 'Transfer Stock', 580, 10, 120, 28, colMode, { kind: 'ghost', s: 11 });

        card(cc, 0, 45, 850, 280, colMode);
        tx(cc, 'Store Counter vs Warehouse Inventory Segregation', 16, 68, { s: 14, w: 700, col: colMode.ink });
        table(cc, 0, 85, 850, [
          { h: 'Product Name', w: 0.25 }, { h: 'Product SKU', w: 0.18 }, { h: 'Store Qty', w: 0.14 }, { h: 'Warehouse Qty', w: 0.16 }, { h: 'Store Status', w: 0.14 }, { h: 'Actions', w: 0.13, a: 'right' }
        ], [
          ['Carbon Black', 'PRD-566E2Q', '22', '0', { pill: 'In Stock', tone: 'green' }, { btns: [{ l: 'Restock', kind: 'ghost' }] }],
          ['Faucet Seal', 'FAU-297C37', '0', '0', { pill: 'Out of Stock', tone: 'red' }, { btns: [{ l: 'Restock', kind: 'ghost' }] }],
          ['Micron #1', 'MIC-EA2E3D', '5', '3', { pill: 'Low Stock', tone: 'amber' }, { btns: [{ l: 'Transfer', kind: 'ghost' }] }],
          ['Micron #5', 'MIC-640E24', '10', '10', { pill: 'In Stock', tone: 'green' }, { btns: [{ l: 'Transfer', kind: 'ghost' }] }],
          ['Small Cap Seal', 'SMA-FAE299', '0', '0', { pill: 'Out of Stock', tone: 'red' }, { btns: [{ l: 'Restock', kind: 'ghost' }] }],
        ], colMode, { rh: 38 });
      });
    });
  },

  approvals(s) {
    cam(s, [['#queue', 550, 240, 1.25], ['#approve', 750, 200, 1.4]]);
    const P = mkP(s);
    s.sfx('ding', s.time('#approve'), { gain: 0.4 });
    s.draw({ at: 0 }, (c) => {
      shell(c, { active: 'Inventory', title: 'Pending Restock Approvals' }, (cc, colMode) => {
        card(cc, 0, 30, 850, 280, colMode);
        tx(cc, 'Restock Approval Queue (Manager Authorization)', 16, 55, { s: 15, w: 700, col: colMode.ink });
        table(cc, 0, 75, 850, [
          { h: 'Product', w: 0.25 }, { h: 'Type', w: 0.15 }, { h: 'Qty', w: 0.1 }, { h: 'Requester', w: 0.2 }, { h: 'Reason Note', w: 0.3 }
        ], [
          ['Faucet Seal (FAU-297C37)', 'Store Restock', '+50', 'Staff Maria', { pill: 'Pending Review', tone: 'amber' }],
          ['Small Cap Seal (SMA-FAE299)', 'Store Restock', '+40', 'Staff Maria', { pill: 'Pending Review', tone: 'amber' }],
        ], colMode, { rh: 42 });

        if (P.at('#approve')) {
          box(cc, 550, 100, 280, 120, { r: 12, fill: colMode.card, stroke: colMode.greenBorder, sh: 12 });
          tx(cc, '✓ Request Approved', 690, 130, { s: 15, w: 700, col: colMode.green, a: 'center' });
          tx(cc, 'Stock updated immediately across system', 690, 160, { s: 11, col: colMode.g500, a: 'center' });
        }
      });
    });
  },

  batch(s) {
    cam(s, [['#mode', 550, 240, 1.25], ['#logs', 550, 240, 1.25]]);
    const P = mkP(s);
    s.draw({ at: 0 }, (c) => {
      shell(c, { active: 'Inventory', title: 'Batch Restock Mode' }, (cc, colMode) => {
        card(cc, 0, 30, 850, 280, colMode);
        tx(cc, 'Fast Bulk Restock Entry (Shipment Intake)', 16, 55, { s: 15, w: 700, col: colMode.ink });
        table(cc, 0, 75, 850, [
          { h: 'Product Name', w: 0.35 }, { h: 'Current Stock', w: 0.2 }, { h: 'Add Quantity', w: 0.25 }, { h: 'New Total', w: 0.2 }
        ], [
          ['Faucet Seal (FAU-297C37)', '0 units', '+ 100 units', '100 units'],
          ['Small Cap Seal (SMA-FAE299)', '0 units', '+ 100 units', '100 units'],
          ['Micron #1 (MIC-EA2E3D)', '8 units', '+ 50 units', '58 units'],
        ], colMode, { rh: 42 });
      });
    });
  },

  requests(s) {
    cam(s, [['#pr', 550, 240, 1.25], ['#convert', 750, 180, 1.35]]);
    const P = mkP(s);
    s.draw({ at: 0 }, (c) => {
      shell(c, { active: 'Suppliers & POs', title: 'Purchase Requests (PR)' }, (cc, colMode) => {
        card(cc, 0, 30, 850, 280, colMode);
        tx(cc, 'Internal Material Requisitions', 16, 55, { s: 15, w: 700, col: colMode.ink });
        table(cc, 0, 75, 850, [
          { h: 'PR #', w: 0.18 }, { h: 'Submitted By', w: 0.22 }, { h: 'Items Requested', w: 0.3 }, { h: 'Status', w: 0.15 }, { h: 'Actions', w: 0.15 }
        ], [
          ['PR-2026-018', 'Warehouse Staff Alex', '234x Faucet Seals', { pill: 'Pending', tone: 'rose' }, { btns: [{ l: 'Convert to PO', kind: 'primary' }] }],
          ['PR-2026-017', 'Staff Maria', '100x Small Cap Seals', { pill: 'Approved', tone: 'green' }, { btns: [{ l: 'View', kind: 'ghost' }] }],
        ], colMode, { rh: 42 });
      });
    });
  },

  orders(s) {
    cam(s, [['#suggestions', 550, 240, 1.25], ['#po', 550, 240, 1.25], ['#pdf', 750, 180, 1.35]]);
    const P = mkP(s);
    s.sfx('ding', s.time('#pdf'), { gain: 0.4 });
    s.draw({ at: 0 }, (c) => {
      shell(c, { active: 'Suppliers & POs', title: 'Purchase Orders (PO) & Receiving' }, (cc, colMode) => {
        card(cc, 0, 30, 850, 280, colMode);
        tx(cc, 'Purchase Orders & Official Supplier Requisitions', 16, 55, { s: 15, w: 700, col: colMode.ink });
        table(cc, 0, 75, 850, [
          { h: 'PO #', w: 0.18 }, { h: 'Supplier', w: 0.25 }, { h: 'Recommended (EOQ)', w: 0.22 }, { h: 'Total Cost', w: 0.15 }, { h: 'Actions', w: 0.2 }
        ], [
          ['PO-2026-009', 'Apex Filtration Supply', '234 units (EOQ)', '₱17,550.00', { btns: [{ l: 'Export PDF', kind: 'primary' }] }],
          ['PO-2026-008', 'Hydratech Industrial Parts', '150 units (EOQ)', '₱9,000.00', { btns: [{ l: 'Receive Form', kind: 'ghost' }] }],
        ], colMode, { rh: 42 });
      });
    });
  },

  suppliers(s) {
    cam(s, [['#dir', 550, 240, 1.25], ['#ledger', 550, 240, 1.25], ['#discrepancy', 550, 240, 1.25]]);
    const P = mkP(s);
    s.draw({ at: 0 }, (c) => {
      shell(c, { active: 'Suppliers & POs', title: 'Suppliers Directory & Ledger' }, (cc, colMode) => {
        card(cc, 0, 30, 850, 280, colMode);
        tx(cc, 'Supplier Directory & Accounts Ledger', 16, 55, { s: 15, w: 700, col: colMode.ink });
        table(cc, 0, 75, 850, [
          { h: 'Supplier Name', w: 0.28 }, { h: 'Contact / Email', w: 0.24 }, { h: 'Outstanding Debt', w: 0.18 }, { h: 'Rating', w: 0.12 }, { h: 'Discrepancies', w: 0.18 }
        ], [
          ['Apex Filtration Supply', 'orders@apexfilter.ph', '₱12,500.00', '⭐ 4.9', { pill: '0 Issues', tone: 'green' }],
          ['Hydratech Industrial Parts', 'sales@hydratech.ph', '₱0.00', '⭐ 4.7', { pill: '1 Damaged', tone: 'amber' }],
        ], colMode, { rh: 44 });
      });
    });
  },

  forecast(s) {
    s.camera([]);
    const P = mkP(s);
    s.draw({ at: 0 }, (c) => {
      tx(c, 'The Simple Moving Average (SMA) Forecast', 960, 140, { s: 48, w: 800, col: '#fff', a: 'center' });

      show(c, P.on('#window', 0.5), () => {
        box(c, 260, 250, 420, 220, { r: 18, fill: '#161B26', stroke: '#1e2536' });
        tx(c, '1. Select Window (N)', 470, 290, { s: 22, w: 700, col: '#38bdf8', a: 'center' });
        tx(c, 'N = 30 Days', 470, 360, { s: 36, w: 800, col: '#fff', a: 'center' });
      });

      show(c, P.on('#sum', 0.5), () => {
        box(c, 750, 250, 420, 220, { r: 18, fill: '#161B26', stroke: '#1e2536' });
        tx(c, '2. Sum 30-Day Sales', 960, 290, { s: 22, w: 700, col: '#38bdf8', a: 'center' });
        tx(c, '∑ Sales = 360 units', 960, 360, { s: 36, w: 800, col: '#fff', a: 'center' });
      });

      show(c, P.on('#daily', 0.5), () => {
        box(c, 1240, 250, 420, 220, { r: 18, fill: '#161B26', stroke: '#1e2536' });
        tx(c, '3. Daily Demand (d)', 1450, 290, { s: 22, w: 700, col: '#10b981', a: 'center' });
        tx(c, '360 ÷ 30 = 12 / day', 1450, 360, { s: 32, w: 800, col: '#10b981', a: 'center' });
      });

      show(c, P.on('#annual', 0.6), () => {
        box(c, 460, 560, 1000, 130, { r: 24, fill: '#0284c7' });
        tx(c, 'Annual Demand (D) = 12 units/day × 365 = 4,380 units/year', 960, 625, { s: 34, w: 700, col: '#fff', a: 'center' });
      });
    });
  },

  settings(s) {
    cam(s, [['#global', 550, 240, 1.25], ['#overrides', 550, 240, 1.25]]);
    const P = mkP(s);
    s.draw({ at: 0 }, (c) => {
      shell(c, { active: 'Forecasting & EOQ', title: 'Forecast Settings' }, (cc, colMode) => {
        card(cc, 0, 30, 850, 90, colMode);
        tx(cc, 'Global Default Sliding Window: 30 Days', 20, 60, { s: 15, w: 700, col: colMode.ink });
        tx(cc, 'Applies to all products unless overridden by category or specific SKU', 20, 82, { s: 12, col: colMode.g500 });
        btn(cc, 'Edit Global', 720, 50, 110, 32, colMode, { kind: 'primary' });

        card(cc, 0, 135, 850, 180, colMode);
        tx(cc, 'Specific Sliding Window Overrides', 20, 160, { s: 14, w: 700, col: colMode.ink });
        table(cc, 0, 175, 850, [
          { h: 'Target', w: 0.35 }, { h: 'Scope', w: 0.25 }, { h: 'Window (N)', w: 0.2 }, { h: 'Rationale', w: 0.2 }
        ], [
          ['Faucet Seal (FAU-297C37)', 'Product Level', '14 Days', 'Fast mover - high responsiveness'],
          ['Carbon Black (PRD-566E2Q)', 'Product Level', '90 Days', 'Slow mover - smooth volatility'],
        ], colMode, { rh: 40 });
      });
    });
  },

  eoq(s) {
    s.camera([]);
    const P = mkP(s);
    s.draw({ at: 0 }, (c) => {
      tx(c, 'The Economic Order Quantity (EOQ)', 960, 130, { s: 50, w: 800, col: '#fff', a: 'center' });

      show(c, P.on('#formula', 0.5), () => {
        box(c, 560, 210, 800, 110, { r: 20, fill: '#161B26', stroke: '#0284c7', lw: 2 });
        tx(c, 'EOQ = √( 2 × D × S ÷ H )', 960, 265, { s: 42, w: 800, col: '#38bdf8', a: 'center' });
      });

      show(c, P.on('#curve', 0.5), () => {
        box(c, 360, 360, 1200, 380, { r: 24, fill: '#161B26', stroke: '#1e2536' });
        // Draw U-curve
        ccStroke(c, '#f43f5e', [[420, 440], [700, 520], [960, 600], [1400, 680]]); // Carrying cost
        ccStroke(c, '#f59e0b', [[420, 680], [700, 600], [960, 520], [1400, 440]]); // Ordering cost
        ccStroke(c, '#10b981', [[420, 690], [700, 550], [960, 480], [1200, 550], [1400, 690]]); // Total curve

        tx(c, 'Holding Cost (H)', 1280, 430, { s: 18, w: 700, col: '#f43f5e' });
        tx(c, 'Ordering Cost (S)', 1280, 690, { s: 18, w: 700, col: '#f59e0b' });
        tx(c, 'Total Cost Curve', 960, 450, { s: 22, w: 800, col: '#10b981', a: 'center' });

        show(c, P.on('#calc', 0.5), () => {
          box(c, 760, 600, 400, 80, { r: 16, fill: '#10b981' });
          tx(c, 'Optimal Order = 234 units', 960, 640, { s: 24, w: 800, col: '#fff', a: 'center' });
        });
      });
    });
  },

  calibration(s) {
    cam(s, [['#settings', 550, 240, 1.25], ['#table', 550, 240, 1.25]]);
    const P = mkP(s);
    s.draw({ at: 0 }, (c) => {
      shell(c, { active: 'Forecasting & EOQ', title: 'EOQ Calibration' }, (cc, colMode) => {
        select(cc, 0, 10, 160, 28, 'Annual Demand', colMode);
        field(cc, 170, 10, 130, 28, '₱150 Order Cost', colMode);
        field(cc, 310, 10, 130, 28, '₱24 Holding Cost', colMode);
        btn(cc, 'Apply Batch Override', 450, 10, 170, 28, colMode, { kind: 'primary' });

        card(cc, 0, 48, 850, 280, colMode);
        table(cc, 0, 48, 850, [
          { h: 'Product Name', w: 0.28 }, { h: 'Timeframe Demand', w: 0.2 }, { h: 'Order Cost (S)', w: 0.18 }, { h: 'Holding Cost (H)', w: 0.18 }, { h: 'Target EOQ', w: 0.16, a: 'right' }
        ], [
          ['Faucet Seal (FAU-297C37)', '4,380 / yr', '₱150.00', '₱24.00', { t: '234 units', col: colMode.green, w: 700 }],
          ['Small Cap Seal (SMA-FAE299)', '2,190 / yr', '₱150.00', '₱48.00', { t: '117 units', col: colMode.green, w: 700 }],
          ['Carbon Black (PRD-566E2Q)', '360 / yr', '₱150.00', '₱35.00', { t: '56 units', col: colMode.green, w: 700 }],
        ], colMode, { rh: 44 });
      });
    });
  },

  admin(s) {
    cam(s, [['#users', 550, 240, 1.25], ['#audit', 550, 240, 1.25], ['#backup', 550, 240, 1.25]]);
    const P = mkP(s);
    s.draw({ at: 0 }, (c) => {
      shell(c, { active: 'User Management', title: 'System Administration & Backups' }, (cc, colMode) => {
        btn(cc, '+ New User', 730, 10, 120, 28, colMode, { kind: 'primary' });
        card(cc, 0, 45, 850, 140, colMode);
        tx(cc, 'User Accounts & Role Permissions', 16, 68, { s: 14, w: 700, col: colMode.ink });
        table(cc, 0, 80, 850, [
          { h: 'Name', w: 0.25 }, { h: 'Email', w: 0.3 }, { h: 'Role', w: 0.25 }, { h: 'Status', w: 0.2 }
        ], [
          ['Admin Ako', 'admin@stevenhydrotech.com', { pill: 'Admin', tone: 'red' }, { pill: 'Active', tone: 'green' }],
          ['Operations Manager', 'manager@stevenhydrotech.com', { pill: 'Manager', tone: 'purple' }, { pill: 'Active', tone: 'green' }],
        ], colMode, { rh: 36 });

        card(cc, 0, 200, 850, 110, colMode);
        tx(cc, 'Database Backup & Disaster Recovery', 16, 225, { s: 14, w: 700, col: colMode.ink });
        btn(cc, 'Create Snapshot Backup (.sql)', 20, 245, 230, 32, colMode, { kind: 'primary' });
        btn(cc, 'Restore Snapshot', 260, 245, 160, 32, colMode, { kind: 'ghost' });
        tx(cc, 'Latest Backup: hydrotech_STABLE_backup.sql (Nov 19, 2026)', 440, 261, { s: 12, col: colMode.g500 });
      });
    });
  },

  payoff(s) {
    s.camera([]);
    const P = mkP(s);
    s.sfx('ding', s.time('#shelves'), { gain: 0.6 });
    s.draw({ at: 0 }, (c) => {
      const back = P.on('#monday', 0.8);
      al(c, 1 - back, () => {
        tx(c, 'The Closed-Loop Predictive Supply Chain', 960, 120, { s: 50, w: 800, col: '#fff', a: 'center' });
        const CXc = 960, CYc = 560, R = 280;
        const nodes = [
          ['POS Sale', '#0284c7'],
          ['Demand Record', '#8b5cf6'],
          ['SMA Forecast', '#f59e0b'],
          ['Target EOQ', '#10b981'],
          ['PR / PO', '#0284c7'],
          ['Approved Restock', '#10b981']
        ];
        nodes.forEach((n, i) => {
          const a = (-90 + i * 60) * Math.PI / 180;
          const nx = CXc + R * Math.cos(a), ny = CYc + R * Math.sin(a);
          box(c, nx - 90, ny - 30, 180, 60, { r: 16, fill: n[1] });
          tx(c, n[0], nx, ny + 1, { s: 18, w: 700, col: '#fff', a: 'center' });
        });
      });
      al(c, back, () => {
        tx(c, 'Monday Morning: Fully Stocked Shelves', 960, 150, { s: 60, w: 800, col: '#fff', a: 'center' });
        box(c, 220, 790, 1480, 18, { r: 9, fill: '#334155' });
        shelfTile(c, 560, 540, 'Faucet Seal', 234, 0, 0);
        shelfTile(c, 960, 540, 'Micron #1', 117, 1, 0);
        shelfTile(c, 1360, 540, 'Carbon Black', 56, 2, 0);
        show(c, P.on('#shelves', 0.5), () => {
          tx(c, '✓ This time, the system saw it coming.', 960, 920, { s: 44, w: 800, col: '#10b981', a: 'center' });
        });
      });
    });
  },

  outro(s) {
    s.camera([]);
    const P = mkP(s);
    s.sfx('chime', 0.4, { gain: 0.4 });
    s.draw({ at: 0 }, (c) => {
      show(c, P.on(0.2, 0.7), () => {
        drawHydrotechLogo(c, 960, 240, 96);
      });
      show(c, P.on(0.5, 0.7), () => tx(c, 'Smarter Forecasts. Optimal Orders. Leaner Shelves.', 960, 450, { s: 54, w: 800, col: '#fff', a: 'center' }));
      show(c, P.on(0.8, 0.7), () => tx(c, 'Steven Hydrotech Exponent Water Treatment and Supply Services', 960, 540, { s: 28, w: 600, col: '#94a3b8', a: 'center' }));
      show(c, P.on(1.2, 0.8), () => {
        box(c, 640, 680, 640, 90, { r: 45, fill: '#0284c7' });
        tx(c, 'Thank You For Watching', 960, 725, { s: 38, w: 800, col: '#fff', a: 'center' });
      });
    });
  },
};
