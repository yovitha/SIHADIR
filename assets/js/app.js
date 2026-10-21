/* =====================================================================
   SIHADIR - Sistem Informasi Daftar Hadir Kegiatan (PROTOTIPE)
   Diskominfo Kota Tomohon
   ---------------------------------------------------------------------
   Prototipe ini 100% berjalan di browser (HTML + CSS + JavaScript).
   Data disimpan di localStorage, jadi tidak perlu PHP / MySQL / XAMPP.
   Cukup buka dengan Live Server di VS Code.
   ===================================================================== */
(function () {
  'use strict';

  /* ================= UTIL ================= */
  var KEY = 'sihadir_db_v1';
  var SESS = 'sihadir_session';
  var $ = function (s, r) { return (r || document).querySelector(s); };
  var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };
  var esc = function (s) {
    return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  };
  var pad = function (n) { return String(n).padStart(2, '0'); };
  var todayStr = function (d) { d = d || new Date(); return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()); };
  var hm = function (d) { return pad(d.getHours()) + ':' + pad(d.getMinutes()); };
  var dt = function (tgl, jam) {
    var a = tgl.split('-').map(Number), b = (jam || '00:00').split(':').map(Number);
    return new Date(a[0], a[1] - 1, a[2], b[0], b[1]);
  };
  function logoHtml(style) {
    return "<img class=\"logo\" src=\"assets/img/logo-diskominfo.png\" alt=\"Logo Dinas Komunikasi dan Informatika Kota Tomohon\"" +
      (style ? " style=\"" + style + "\"" : "") +
      " onerror=\"this.onerror=null;this.src='assets/img/logo-placeholder.svg'\">";
  }
  var MONTHS = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
  var MON3 = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
  var DAYS = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
  var tglPendek = function (t) { var a = t.split('-').map(Number); return a[2] + ' ' + MON3[a[1] - 1] + ' ' + a[0]; };
  var tglPanjang = function (t) { var d = dt(t); return DAYS[d.getDay()] + ', ' + d.getDate() + ' ' + MONTHS[d.getMonth()] + ' ' + d.getFullYear(); };
  var waktuStr = function (iso) { var d = new Date(iso); return d.getDate() + ' ' + MON3[d.getMonth()] + ' ' + d.getFullYear() + ', ' + hm(d); };
  var ROLE = { admin: 'Admin', operator: 'Operator', pimpinan: 'Pimpinan' };
  var STATUS = { draft: 'Draft', aktif: 'Aktif', selesai: 'Selesai', dibatalkan: 'Dibatalkan' };
  var badge = function (st) { return '<span class="badge ' + st + '">' + (STATUS[st] || st) + '</span>'; };

  var ICONS = {
    dashboard: '<path d="M3 3h8v10H3zM13 3h8v6h-8zM13 11h8v10h-8zM3 15h8v6H3z"/>',
    kegiatan: '<path d="M7 3v3M17 3v3M4 8h16M5 5h14a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1z"/>',
    pantau: '<path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>',
    peserta: '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c0-3.5 3-6 6.5-6s6.5 2.5 6.5 6M16 4.5a3.5 3.5 0 0 1 0 7M18 14c2 .7 3.5 2.6 3.5 5"/>',
    laporan: '<path d="M6 3h9l4 4v14H6zM14 3v5h5M9 13h7M9 17h7"/>',
    pengguna: '<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.5-7 8-7s8 3 8 7"/>',
    master: '<ellipse cx="12" cy="6" rx="8" ry="3"/><path d="M4 6v6c0 1.7 3.6 3 8 3s8-1.3 8-3V6M4 12v6c0 1.7 3.6 3 8 3s8-1.3 8-3v-6"/>',
    log: '<path d="M4 5h16M4 10h16M4 15h10M4 20h7"/>',
    logout: '<path d="M9 4H5a1 1 0 0 0-1 1v14a1 1 0 0 0 1 1h4M16 8l4 4-4 4M20 12H9"/>',
    menu: '<path d="M4 6h16M4 12h16M4 18h16"/>',
    key: '<circle cx="8" cy="15" r="4"/><path d="M11 12l9-9M16 7l3 3M14 9l2 2"/>',
    plus: '<path d="M12 5v14M5 12h14"/>'
  };
  var icon = function (n, s) {
    s = s || 18;
    return '<svg class="ic" width="' + s + '" height="' + s + '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">' + (ICONS[n] || '') + '</svg>';
  };

  function randCode(existing) {
    var A = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789', c, i;
    do {
      c = '';
      var arr = new Uint32Array(10);
      (window.crypto || window.msCrypto).getRandomValues(arr);
      for (i = 0; i < arr.length; i++) c += A[arr[i] % A.length];
    } while (existing && existing.has(c));
    return c;
  }

  function download(name, content, mime) {
    var b = content instanceof Blob ? content : new Blob([content], { type: mime || 'text/plain' });
    var a = document.createElement('a');
    a.href = URL.createObjectURL(b);
    a.download = name;
    document.body.appendChild(a);
    a.click();
    setTimeout(function () { URL.revokeObjectURL(a.href); a.remove(); }, 500);
  }

  function toast(msg, type) {
    var box = $('#toasts');
    if (!box) { box = document.createElement('div'); box.id = 'toasts'; document.body.appendChild(box); }
    var t = document.createElement('div');
    t.className = 'toast ' + (type || '');
    t.textContent = msg;
    box.appendChild(t);
    setTimeout(function () { t.classList.add('out'); }, 2800);
    setTimeout(function () { t.remove(); }, 3300);
  }

  function openModal(o) {
    var back = document.createElement('div');
    back.className = 'modal-back';
    back.innerHTML =
      '<div class="modal ' + (o.size || '') + '" role="dialog" aria-modal="true">' +
      '<div class="modal-head"><h3>' + esc(o.title) + '</h3><button class="icon-btn" type="button" data-close aria-label="Tutup">&times;</button></div>' +
      '<form class="modal-body" novalidate autocomplete="off">' + o.body + '<div class="form-err" hidden></div></form>' +
      '<div class="modal-foot">' +
      (o.cancel === null ? '' : '<button class="btn" type="button" data-close>' + (o.cancel || 'Batal') + '</button>') +
      (o.ok === null ? '' : '<button class="btn ' + (o.danger ? 'danger solid' : 'primary') + '" type="button" data-ok>' + (o.ok || 'Simpan') + '</button>') +
      '</div></div>';
    document.body.appendChild(back);
    var form = $('form', back);
    var close = function () { back.remove(); };
    var err = function (m) { var e = $('.form-err', back); e.textContent = m || ''; e.hidden = !m; };
    var api = { close: close, err: err, el: back };
    var doOk = function () {
      var data = {};
      new FormData(form).forEach(function (v, k) { data[k] = v; });
      var r = o.onOk ? o.onOk(data, err, close) : true;
      if (r !== false) close();
    };
    back.addEventListener('mousedown', function (e) { if (e.target === back) close(); });
    back.addEventListener('click', function (e) { if (e.target.closest('[data-close]')) close(); });
    form.addEventListener('submit', function (e) { e.preventDefault(); doOk(); });
    var ok = $('[data-ok]', back);
    if (ok) ok.addEventListener('click', doOk);
    setTimeout(function () { var f = $('input:not([type=hidden]),select,textarea', form); if (f) f.focus(); }, 40);
    return api;
  }

  function askConfirm(msg, yes, opt) {
    opt = opt || {};
    return openModal({
      title: opt.title || 'Konfirmasi', body: '<p>' + msg + '</p>', ok: opt.ok || 'Ya, lanjutkan', danger: opt.danger,
      onOk: function () { yes(); return true; }
    });
  }

  /* ================= DATA ================= */
  var DB = null;

  function nid(t) { DB.seq[t] = (DB.seq[t] || 0) + 1; return DB.seq[t]; }

  function sigSvg(i) {
    return 'data:image/svg+xml;utf8,' + encodeURIComponent(
      '<svg xmlns="http://www.w3.org/2000/svg" width="120" height="48" viewBox="0 0 120 48"><path d="M6 ' + (30 + (i % 5)) +
      ' C 22 ' + (4 + (i % 7)) + ', 30 ' + (50 - (i % 6)) + ', 46 24 S 78 ' + (8 + (i % 9)) + ', 94 ' + (28 - (i % 4)) +
      ' S 108 40, 114 ' + (18 + (i % 6)) + '" fill="none" stroke="#1a2b6b" stroke-width="2" stroke-linecap="round"/></svg>');
  }

  function seed() {
    var now = new Date();
    var nowMin = now.getHours() * 60 + now.getMinutes();
    var T = function (m) { return pad(Math.floor(m / 60)) + ':' + pad(m % 60); };
    var day = function (n) { return todayStr(new Date(now.getFullYear(), now.getMonth(), now.getDate() + n)); };
    var codes = new Set();
    var code = function () { var c = randCode(codes); codes.add(c); return c; };
    DB = {
      seq: {},
      settings: { demoWaktu: false, ttdWajib: true, toleransi: 15, baseUrl: '' },
      users: [
        { id: 1, nama: 'Administrator SIHADIR', username: 'admin', password: 'admin123', role: 'admin', aktif: 1 },
        { id: 2, nama: 'Operator Kegiatan', username: 'operator', password: 'operator123', role: 'operator', aktif: 1 },
        { id: 3, nama: 'Pimpinan Diskominfo', username: 'pimpinan', password: 'pimpinan123', role: 'pimpinan', aktif: 1 }
      ],
      kategori: [], instansi: [], peserta: [], kegiatan: [], kehadiran: [], log: []
    };
    DB.seq.users = 3;
    ['Rapat Koordinasi', 'Apel', 'Sosialisasi', 'Bimtek', 'Pelatihan'].forEach(function (n) { DB.kategori.push({ id: nid('kategori'), nama: n }); });
    ['Diskominfo Kota Tomohon', 'Sekretariat Daerah', 'Bappeda', 'BKPSDM', 'Dinas Pendidikan', 'Tamu Instansi Lain'].forEach(function (n) { DB.instansi.push({ id: nid('instansi'), nama: n }); });
    var P = [
      ['Jeffry Mamahit', 'Kepala Bidang Aptika', 1], ['Merry Kaunang', 'Analis Sistem Informasi', 1], ['Roy Lumentut', 'Staf Persandian', 1],
      ['Sherly Tumbelaka', 'Sekretaris Dinas', 1], ['Frans Wuisan', 'Kepala Sub Bagian Umum', 2], ['Debby Rondonuwu', 'Perencana Ahli Muda', 3],
      ['Stevie Walean', 'Pengelola Kepegawaian', 4], ['Ivonne Pangemanan', 'Staf Layanan Informasi', 1], ['Ronald Sumual', 'Pranata Komputer', 1],
      ['Grace Mokodompit', 'Guru Pembina', 5]
    ];
    P.forEach(function (p, i) {
      DB.peserta.push({ id: nid('peserta'), instansiId: p[2], nip: '1980' + pad(i + 1) + '0120100' + (1000 + i), nama: p[0], jabatan: p[1], hp: '', email: '' });
    });
    var K = function (uid, kat, nama, desk, tgl, ms, me, lok, pen, st) {
      var k = { id: nid('kegiatan'), userId: uid, kategoriId: kat, nama: nama, deskripsi: desk, tanggal: tgl, jam_mulai: ms, jam_selesai: me, lokasi: lok, penyelenggara: pen, kode: code(), status: st, created: new Date().toISOString() };
      DB.kegiatan.push(k); return k;
    };
    var msA = Math.max(0, nowMin - 60), meA = Math.min(23 * 60 + 59, nowMin + 180);
    if (meA - msA < 30) { msA = Math.max(0, meA - 30); }
    var k1 = K(2, 1, 'Rapat Koordinasi Transformasi Digital', 'Pembahasan rencana kerja transformasi digital perangkat daerah.', todayStr(), T(msA), T(meA), 'Ruang Rapat Utama Diskominfo', 'Bidang Aptika', 'aktif');
    var k2 = K(2, 2, 'Apel Gabungan Bulanan', 'Apel gabungan seluruh pegawai.', day(-7), '07:30', '08:30', 'Halaman Kantor Wali Kota', 'Sekretariat', 'selesai');
    var k3 = K(2, 3, 'Sosialisasi Keamanan Informasi', 'Sosialisasi kesadaran keamanan informasi bagi OPD.', day(-20), '09:00', '12:00', 'Aula Diskominfo', 'Bidang Persandian', 'selesai');
    var k4 = K(2, 4, 'Bimtek Pengelolaan Website OPD', 'Bimbingan teknis admin website perangkat daerah.', day(5), '09:00', '12:00', 'Laboratorium Komputer', 'Bidang Aptika', 'draft');
    var k5 = K(1, 5, 'Pelatihan Dasar Desain UI/UX', 'Pelatihan dasar desain antarmuka untuk staf.', day(-45), '13:00', '16:00', 'Ruang Pelatihan', 'Bidang Aptika', 'selesai');
    var at = function (tgl, jam, add) { return new Date(dt(tgl, jam).getTime() + add * 60000).toISOString(); };
    var hid = 0;
    var H = function (k, pi, add, guest) {
      hid++;
      var p = pi == null ? null : DB.peserta[pi];
      var w = k === k1 ? new Date(Date.now() - add * 60000).toISOString() : at(k.tanggal, k.jam_mulai, add);
      DB.kehadiran.push({
        id: nid('kehadiran'), kegiatanId: k.id, pesertaId: p ? p.id : null,
        nama: p ? p.nama : guest[0], instansi: p ? DB.instansi.find(function (x) { return x.id === p.instansiId; }).nama : guest[1], jabatan: p ? p.jabatan : guest[2],
        hp: '', waktu: w, ttd: sigSvg(hid), status: 'tercatat', ket: ''
      });
    };
    [0, 1, 2].forEach(function (i, n) { H(k1, i, 40 - n * 12); });
    [0, 1, 2, 3, 4, 7, 8].forEach(function (i, n) { H(k2, i, 5 + n * 3); });
    [0, 1, 3, 4, 5, 6, 9].forEach(function (i, n) { H(k3, i, 4 + n * 4); });
    H(k3, null, 30, ['Olivia Rumengan', 'Dinas Pendidikan', 'Operator Sekolah']);
    [1, 2, 5, 8].forEach(function (i, n) { H(k5, i, 6 + n * 5); });
    DB.log.push({ id: nid('log'), nama: 'Sistem', aktivitas: 'Data contoh dibuat', waktu: new Date().toISOString() });
    return DB;
  }

  function read() {
    try { var r = localStorage.getItem(KEY); if (r) return JSON.parse(r); } catch (e) { /* abaikan */ }
    return null;
  }
  function save() { localStorage.setItem(KEY, JSON.stringify(DB)); }
  function reload() { var d = read(); if (!d) { d = seed(); DB = d; save(); } DB = d; }

  function addLog(text, user) {
    var u = user === undefined ? me() : user;
    DB.log.push({ id: nid('log'), nama: u ? u.nama : 'Peserta (publik)', aktivitas: text, waktu: new Date().toISOString() });
    if (DB.log.length > 500) DB.log = DB.log.slice(-500);
  }

  /* ================= AUTH & ATURAN ================= */
  function me() {
    var id = +localStorage.getItem(SESS);
    return DB.users.find(function (u) { return u.id === id && u.aktif; }) || null;
  }
  function visK(u) { return u.role === 'operator' ? DB.kegiatan.filter(function (k) { return k.userId === u.id; }) : DB.kegiatan.slice(); }
  function canEdit(k, u) { return !!u && (u.role === 'admin' || (u.role === 'operator' && k.userId === u.id)); }
  function hadirOf(kid, onlyOk) {
    return DB.kehadiran.filter(function (h) { return h.kegiatanId === kid && (!onlyOk || h.status === 'tercatat'); })
      .sort(function (a, b) { return a.waktu.localeCompare(b.waktu); });
  }
  function katName(id) { var c = DB.kategori.find(function (x) { return x.id === id; }); return c ? c.nama : '-'; }
  function userName(id) { var c = DB.users.find(function (x) { return x.id === id; }); return c ? c.nama : '-'; }
  function insName(id) { var c = DB.instansi.find(function (x) { return x.id === id; }); return c ? c.nama : '-'; }

  // AB-03: rentang waktu presensi = jam mulai - toleransi s.d. jam selesai + toleransi
  function windowState(k) {
    if (DB.settings.demoWaktu) return 'ok';
    var tol = (+DB.settings.toleransi || 0) * 60000;
    var s = dt(k.tanggal, k.jam_mulai).getTime() - tol, e = dt(k.tanggal, k.jam_selesai).getTime() + tol, n = Date.now();
    return n < s ? 'belum' : n > e ? 'lewat' : 'ok';
  }
  // Kegiatan Aktif otomatis menjadi Selesai saat waktunya berakhir
  function autoClose() {
    var changed = false;
    DB.kegiatan.forEach(function (k) {
      if (k.status === 'aktif' && !DB.settings.demoWaktu && windowState(k) === 'lewat') {
        k.status = 'selesai'; changed = true; addLog('Kegiatan "' + k.nama + '" otomatis selesai (waktu berakhir)', null);
        DB.log[DB.log.length - 1].nama = 'Sistem';
      }
    });
    if (changed) save();
  }

  // Dipakai bersama oleh halaman presensi publik dan penambahan manual operator
  function addKehadiran(o) {
    var nama = String(o.nama || '').trim().replace(/\s+/g, ' ');
    var ins = String(o.instansi || '').trim();
    var jab = String(o.jabatan || '').trim();
    var reg = DB.peserta.find(function (p) { return p.nama.toLowerCase() === nama.toLowerCase(); });
    var dup = DB.kehadiran.find(function (h) {
      if (h.kegiatanId !== o.kegiatanId || h.status !== 'tercatat') return false;
      if (reg) return h.pesertaId === reg.id;
      return !h.pesertaId && h.nama.toLowerCase() === nama.toLowerCase() && h.instansi.toLowerCase() === ins.toLowerCase();
    });
    if (dup) return { ok: false, msg: 'Kehadiran atas nama ' + dup.nama + ' sudah tercatat pada kegiatan ini.' };
    var rec = {
      id: nid('kehadiran'), kegiatanId: o.kegiatanId, pesertaId: reg ? reg.id : null, nama: reg ? reg.nama : nama,
      instansi: ins, jabatan: jab, hp: o.hp || '', waktu: new Date().toISOString(), ttd: o.ttd || null, status: 'tercatat', ket: o.ket || ''
    };
    DB.kehadiran.push(rec);
    return { ok: true, rec: rec };
  }

  /* ================= MENU & HALAMAN ================= */
  var ALL = ['admin', 'operator', 'pimpinan'];
  var MENU = [
    { id: 'dashboard', label: 'Dashboard', roles: ALL },
    { id: 'kegiatan', label: 'Kegiatan & QR Code', roles: ALL },
    { id: 'pantau', label: 'Pantau Kehadiran', roles: ALL },
    { id: 'peserta', label: 'Data Peserta', roles: ['admin', 'operator'] },
    { id: 'laporan', label: 'Laporan & Ekspor', roles: ALL },
    { id: 'pengguna', label: 'Pengguna', roles: ['admin'] },
    { id: 'master', label: 'Master Data', roles: ['admin'] },
    { id: 'log', label: 'Log Aktivitas', roles: ['admin'] }
  ];
  var ACT = {};
  var PAGES = {};
  var page = document.body.getAttribute('data-page');

  document.addEventListener('click', function (e) {
    var a = e.target.closest('[data-act]');
    if (a && ACT[a.getAttribute('data-act')]) { ACT[a.getAttribute('data-act')](a, e); }
  });
  var FILT = { k: { q: '', kat: '', st: '', tgl: '' }, p: { q: '' }, l: { q: '' } };
  function onFilter(e) {
    var f = e.target.getAttribute && e.target.getAttribute('data-f');
    if (!f) return;
    var p = f.split(':');
    FILT[p[0]][p[1]] = e.target.value;
    if (PAGES[page] && PAGES[page].refresh) PAGES[page].refresh();
  }
  document.addEventListener('input', onFilter);
  document.addEventListener('change', onFilter);
  window.addEventListener('storage', function (e) {
    if (e.key === KEY) { reload(); if (PAGES[page] && PAGES[page].live) PAGES[page].live(); }
  });

  function rerender() {
    reload();
    var u = me(); var def = PAGES[page];
    if (!u || !def) return;
    $('#content').innerHTML = def.view(u);
    if (def.after) def.after(u);
  }

  function buildShell(u, def) {
    var nav = MENU.filter(function (m) { return m.roles.indexOf(u.role) >= 0; }).map(function (m) {
      return '<a class="nav-item ' + (m.id === def.nav ? 'active' : '') + '" href="' + m.id + '.html">' + icon(m.id) + '<span>' + m.label + '</span></a>';
    }).join('');
    var ini = u.nama.split(' ').slice(0, 2).map(function (s) { return s[0]; }).join('').toUpperCase();
    var sub = typeof def.sub === 'function' ? def.sub(u) : (def.sub || '');
    $('#app').innerHTML =
      '<div class="shell"><aside class="sidebar" id="sidebar">' +
      '<div class="brand">' + logoHtml() + '<div><b>SIHADIR</b><small>Diskominfo Kota Tomohon</small></div></div>' +
      '<nav>' + nav + '</nav>' +
      '<div class="side-foot"><button class="btn ghost-light" data-act="pw">' + icon('key') + ' Ganti kata sandi</button>' +
      '<button class="btn ghost-light" data-act="logout">' + icon('logout') + ' Keluar</button></div></aside>' +
      '<div class="scrim" data-act="menu"></div>' +
      '<main class="main"><header class="topbar no-print"><button class="icon-btn menu-btn" data-act="menu" aria-label="Menu">' + icon('menu', 22) + '</button>' +
      '<div class="titles"><h1>' + esc(def.title) + '</h1><p>' + esc(sub) + '</p></div>' +
      '<div class="topbar-actions">' + (def.actions ? def.actions(u) : '') + '</div>' +
      '<div class="userchip"><span class="avatar">' + esc(ini) + '</span><span><b>' + esc(u.nama) + '</b><small>' + ROLE[u.role] + '</small></span></div></header>' +
      '<section class="content" id="content"></section></main></div><div id="toasts"></div>';
  }

  ACT.menu = function () { document.body.classList.toggle('menu-open'); };
  ACT.logout = function () {
    reload(); addLog('Keluar dari sistem'); save();
    localStorage.removeItem(SESS); location.href = 'login.html';
  };
  ACT.pw = function () {
    openModal({
      title: 'Ganti Kata Sandi',
      body: '<div class="stack" style="gap:12px">' +
        '<label class="fld">Kata sandi saat ini<input class="inp" type="password" name="lama"></label>' +
        '<label class="fld">Kata sandi baru<input class="inp" type="password" name="baru"><span class="hint">Minimal 6 karakter.</span></label>' +
        '<label class="fld">Ulangi kata sandi baru<input class="inp" type="password" name="ulang"></label></div>',
      onOk: function (d, err) {
        reload(); var u = me();
        if (d.lama !== u.password) { err('Kata sandi saat ini salah.'); return false; }
        if (!d.baru || d.baru.length < 6) { err('Kata sandi baru minimal 6 karakter.'); return false; }
        if (d.baru !== d.ulang) { err('Ulangi kata sandi tidak sama.'); return false; }
        u.password = d.baru; addLog('Mengganti kata sandi'); save(); toast('Kata sandi berhasil diganti.');
      }
    });
  };

  /* ================= LOGIN ================= */
  function renderLogin() {
    if (me()) { location.replace('dashboard.html'); return; }
    var demo = DB.users.filter(function (u) { return u.aktif; }).slice(0, 3).map(function (u) {
      return '<button type="button" class="demo-item" data-act="demo" data-u="' + esc(u.username) + '" data-p="' + esc(u.password) + '">' +
        '<span><b>' + ROLE[u.role] + '</b><br><small>klik untuk mengisi otomatis</small></span><span><code>' + esc(u.username) + '</code> / <code>' + esc(u.password) + '</code></span></button>';
    }).join('');
    $('#app').innerHTML =
      '<div class="login-bg"><div class="login-wrap"><div class="login-info">' +
      '' + logoHtml('width:52px;height:52px') + '' +
      '<div class="pill"><i></i>Dinas Komunikasi dan Informatika Kota Tomohon</div>' +
      '<h1>SIHADIR<br><span>Daftar Hadir Kegiatan</span></h1><div class="bar-accent"></div>' +
      '<p class="lead">Sistem informasi daftar hadir kegiatan berbasis web. Buat kegiatan, bagikan QR Code, pantau kehadiran, dan cetak laporan dalam satu tempat.</p>' +
      '<ul><li>Kegiatan dan QR Code unik</li><li>Presensi peserta tanpa login</li><li>Laporan siap cetak, PDF, dan Excel</li></ul></div>' +
      '<div class="login-card"><h2>Masuk</h2><p class="sub">Gunakan akun yang diberikan oleh Admin.</p>' +
      '<form id="fLogin" novalidate><label class="fld">Username<input class="inp" id="lgU" name="u" autocomplete="username" autofocus></label>' +
      '<label class="fld">Password<span class="pw-row"><input class="inp" id="lgP" name="p" type="password" autocomplete="current-password"><button class="btn" type="button" data-act="showpw">Tampilkan</button></span></label>' +
      '<div class="form-err" id="lgErr" hidden></div><button class="btn primary block" type="submit">Masuk</button></form>' +
      '<details class="demo"><summary>Akun demo (khusus prototipe)</summary><div class="demo-list">' + demo + '</div></details></div></div></div><div id="toasts"></div>';
    $('#fLogin').addEventListener('submit', function (e) {
      e.preventDefault(); reload();
      var un = $('#lgU').value.trim().toLowerCase(), pw = $('#lgP').value, er = $('#lgErr');
      var u = DB.users.find(function (x) { return x.username.toLowerCase() === un; });
      var msg = '';
      if (!un || !pw) msg = 'Username dan password wajib diisi.';
      else if (!u || u.password !== pw) msg = 'Username atau password salah.';
      else if (!u.aktif) msg = 'Akun ini dinonaktifkan. Hubungi Admin.';
      if (msg) { er.textContent = msg; er.hidden = false; return; }
      localStorage.setItem(SESS, u.id); addLog('Masuk ke sistem', u); save();
      location.href = 'dashboard.html';
    });
  }
  ACT.showpw = function (el) {
    var i = $('#lgP'); var show = i.type === 'password';
    i.type = show ? 'text' : 'password'; el.textContent = show ? 'Sembunyikan' : 'Tampilkan';
  };
  ACT.demo = function (el) { $('#lgU').value = el.getAttribute('data-u'); $('#lgP').value = el.getAttribute('data-p'); $('#lgErr').hidden = true; };

  /* ================= DASHBOARD ================= */
  PAGES.dashboard = {
    nav: 'dashboard', roles: ALL, title: 'Dashboard',
    sub: function (u) { return 'Selamat datang, ' + u.nama + '. ' + tglPanjang(todayStr()); },
    view: function (u) {
      var ks = visK(u), ids = {};
      ks.forEach(function (k) { ids[k.id] = 1; });
      var hadir = DB.kehadiran.filter(function (h) { return ids[h.kegiatanId] && h.status === 'tercatat'; });
      var hariIni = ks.filter(function (k) { return k.tanggal === todayStr() && k.status !== 'dibatalkan'; }).length;
      var berjalan = ks.filter(function (k) { return k.status === 'aktif' || k.status === 'selesai'; }).length;
      var avg = berjalan ? (hadir.length / berjalan) : 0;
      var months = [], i;
      for (i = 5; i >= 0; i--) {
        var d = new Date(); d.setDate(1); d.setMonth(d.getMonth() - i);
        months.push({ key: d.getFullYear() + '-' + pad(d.getMonth() + 1), label: MON3[d.getMonth()], n: 0 });
      }
      hadir.forEach(function (h) {
        var k = ks.find(function (x) { return x.id === h.kegiatanId; });
        var m = months.find(function (x) { return x.key === k.tanggal.slice(0, 7); });
        if (m) m.n++;
      });
      var max = Math.max.apply(null, [1].concat(months.map(function (m) { return m.n; })));
      var bars = months.map(function (m) {
        return '<div class="bar-col"><span class="bar-val">' + m.n + '</span><div class="bar" style="height:' + Math.round(m.n / max * 100) + '%"></div><span class="bar-lbl">' + m.label + '</span></div>';
      }).join('');
      var aktif = ks.filter(function (k) { return k.status === 'aktif'; });
      var aktifHtml = aktif.length ? '<ul class="list-plain">' + aktif.map(function (k) {
        return '<li><div class="meta"><b>' + esc(k.nama) + '</b><small>' + tglPendek(k.tanggal) + ', ' + k.jam_mulai + '-' + k.jam_selesai + ' &middot; ' + hadirOf(k.id, true).length + ' hadir</small></div>' +
          '<div class="row-actions" style="display:flex;gap:6px"><a class="btn sm" href="detail-kegiatan.html?id=' + k.id + '">QR</a><a class="btn sm primary" href="pantau.html?id=' + k.id + '">Pantau</a></div></li>';
      }).join('') + '</ul>' : '<div class="empty">Tidak ada kegiatan yang sedang aktif.</div>';
      var terbaru = ks.slice().sort(function (a, b) { return (b.tanggal + b.jam_mulai).localeCompare(a.tanggal + a.jam_mulai); }).slice(0, 5);
      var terHtml = terbaru.length ? '<div class="table-wrap"><table class="tbl"><thead><tr><th>Kegiatan</th><th>Tanggal</th><th>Hadir</th><th>Status</th></tr></thead><tbody>' +
        terbaru.map(function (k) {
          return '<tr><td><span class="cell-title"><a href="detail-kegiatan.html?id=' + k.id + '">' + esc(k.nama) + '</a></span></td><td>' + tglPendek(k.tanggal) + '</td><td>' + hadirOf(k.id, true).length + '</td><td>' + badge(k.status) + '</td></tr>';
        }).join('') + '</tbody></table></div>' : '<div class="empty">Belum ada kegiatan.</div>';
      return '<div class="stats">' +
        '<div class="stat"><span>Total kegiatan</span><b>' + ks.length + '</b><small>' + (u.role === 'operator' ? 'milik Anda' : 'semua kegiatan') + '</small></div>' +
        '<div class="stat"><span>Total kehadiran</span><b>' + hadir.length + '</b><small>presensi tercatat</small></div>' +
        '<div class="stat"><span>Kegiatan hari ini</span><b>' + hariIni + '</b><small>' + tglPendek(todayStr()) + '</small></div>' +
        '<div class="stat"><span>Rata-rata hadir</span><b>' + (Math.round(avg * 10) / 10).toString().replace('.', ',') + '</b><small>per kegiatan</small></div></div>' +
        '<div class="two-col"><div class="stack"><div class="card"><h2>Kehadiran per bulan</h2><p class="sub">Enam bulan terakhir, berdasarkan tanggal kegiatan.</p><div class="bars">' + bars + '</div></div>' +
        '<div class="card"><h2>Kegiatan terbaru</h2><p class="sub">Lima kegiatan terakhir.</p>' + terHtml + '</div></div>' +
        '<div class="stack"><div class="card"><h2><span class="live-dot"></span>Sedang aktif</h2><p class="sub">Presensi sedang dibuka.</p>' + aktifHtml + '</div>' +
        '<div class="note"><b>Mode prototipe.</b> Data tersimpan di browser ini (localStorage) dan berisi data contoh. Versi produksi memakai server dan basis data MySQL sesuai rencana.</div></div></div>';
    },
    live: function () { rerender(); }
  };

  /* ================= KEGIATAN ================= */
  function kegRows(u) {
    var F = FILT.k, q = F.q.toLowerCase();
    var ks = visK(u).sort(function (a, b) { return (b.tanggal + b.jam_mulai).localeCompare(a.tanggal + a.jam_mulai); }).filter(function (k) {
      return (!q || k.nama.toLowerCase().indexOf(q) >= 0 || k.lokasi.toLowerCase().indexOf(q) >= 0) &&
        (!F.kat || String(k.kategoriId) === F.kat) && (!F.st || k.status === F.st) && (!F.tgl || k.tanggal === F.tgl);
    });
    if (!ks.length) return '<div class="empty">Tidak ada kegiatan yang cocok.</div>';
    return '<div class="table-wrap"><table class="tbl"><thead><tr><th>Kegiatan</th><th>Tanggal &amp; jam</th><th>Lokasi</th><th>Hadir</th><th>Status</th><th></th></tr></thead><tbody>' +
      ks.map(function (k) {
        return '<tr><td><span class="cell-title"><a href="detail-kegiatan.html?id=' + k.id + '">' + esc(k.nama) + '</a></span><span class="cell-sub">' + esc(katName(k.kategoriId)) + ' &middot; ' + esc(k.penyelenggara) + '</span></td>' +
          '<td>' + tglPendek(k.tanggal) + '<br><span class="cell-sub">' + k.jam_mulai + ' - ' + k.jam_selesai + '</span></td><td>' + esc(k.lokasi) + '</td>' +
          '<td><b>' + hadirOf(k.id, true).length + '</b></td><td>' + badge(k.status) + '</td>' +
          '<td class="right"><a class="btn sm" href="detail-kegiatan.html?id=' + k.id + '">Detail</a></td></tr>';
      }).join('') + '</tbody></table></div>';
  }
  PAGES.kegiatan = {
    nav: 'kegiatan', roles: ALL, title: 'Kegiatan & QR Code',
    sub: function (u) { return u.role === 'operator' ? 'Kegiatan yang Anda buat.' : u.role === 'pimpinan' ? 'Daftar seluruh kegiatan (hanya lihat).' : 'Kelola seluruh kegiatan.'; },
    actions: function (u) { return u.role === 'pimpinan' ? '' : '<button class="btn primary" data-act="k-new">' + icon('plus') + ' Tambah Kegiatan</button>'; },
    view: function (u) {
      var F = FILT.k;
      return '<div class="card"><div class="filters">' +
        '<input class="inp" data-f="k:q" placeholder="Cari nama atau lokasi..." value="' + esc(F.q) + '">' +
        '<select class="inp" data-f="k:kat"><option value="">Semua kategori</option>' + DB.kategori.map(function (c) { return '<option value="' + c.id + '"' + (String(c.id) === F.kat ? ' selected' : '') + '>' + esc(c.nama) + '</option>'; }).join('') + '</select>' +
        '<select class="inp" data-f="k:st"><option value="">Semua status</option>' + Object.keys(STATUS).map(function (s) { return '<option value="' + s + '"' + (s === F.st ? ' selected' : '') + '>' + STATUS[s] + '</option>'; }).join('') + '</select>' +
        '<input class="inp" type="date" data-f="k:tgl" value="' + esc(F.tgl) + '"></div><div id="kegTable">' + kegRows(u) + '</div></div>';
    },
    refresh: function () { $('#kegTable').innerHTML = kegRows(me()); },
    live: function () { this.refresh(); }
  };

  // Jam bawaan formulir kegiatan baru: mulai = sekarang (dibulatkan), selesai = +2 jam (maks. 23:59)
  function defTime(add) {
    var d = new Date(), m = Math.min(23 * 60 + 59, Math.ceil((d.getHours() * 60 + d.getMinutes()) / 5) * 5 + add);
    if (add === 0) m = Math.min(m, 23 * 60);
    return pad(Math.floor(m / 60)) + ':' + pad(m % 60);
  }
  function formKegiatan(id) {
    var u = me(), k = id ? DB.kegiatan.find(function (x) { return x.id === id; }) : null;
    if (id && (!k || !canEdit(k, u))) { toast('Anda tidak berhak mengubah kegiatan ini.', 'err'); return; }
    var v = function (f, d) { return esc(k ? k[f] : (d || '')); };
    openModal({
      title: k ? 'Ubah Kegiatan' : 'Tambah Kegiatan', size: 'wide',
      body: '<div class="grid2">' +
        '<label class="fld span2">Nama kegiatan *<input class="inp" name="nama" value="' + v('nama') + '" placeholder="Contoh: Rapat Koordinasi Bulanan"></label>' +
        '<label class="fld">Kategori *<select class="inp" name="kategori">' + DB.kategori.map(function (c) { return '<option value="' + c.id + '"' + (k && k.kategoriId === c.id ? ' selected' : '') + '>' + esc(c.nama) + '</option>'; }).join('') + '</select></label>' +
        '<label class="fld">Penyelenggara<input class="inp" name="penyelenggara" list="dlIns" value="' + v('penyelenggara', 'Diskominfo Kota Tomohon') + '"><datalist id="dlIns">' + DB.instansi.map(function (i) { return '<option value="' + esc(i.nama) + '">'; }).join('') + '</datalist></label>' +
        '<label class="fld">Tanggal *<input class="inp" type="date" name="tanggal" value="' + v('tanggal', todayStr()) + '"></label>' +
        '<div class="grid2" style="gap:10px"><label class="fld">Jam mulai *<input class="inp" type="time" name="mulai" value="' + v('jam_mulai', defTime(0)) + '"></label>' +
        '<label class="fld">Jam selesai *<input class="inp" type="time" name="selesai" value="' + v('jam_selesai', defTime(120)) + '"></label></div>' +
        '<label class="fld span2">Lokasi *<input class="inp" name="lokasi" value="' + v('lokasi') + '" placeholder="Contoh: Ruang Rapat Utama"></label>' +
        '<label class="fld span2">Deskripsi<textarea class="inp" name="deskripsi">' + v('deskripsi') + '</textarea></label></div>',
      onOk: function (d, err) {
        var nama = d.nama.trim(), lok = d.lokasi.trim();
        if (!nama) { err('Nama kegiatan wajib diisi.'); return false; }
        if (!d.tanggal) { err('Tanggal kegiatan wajib diisi.'); return false; }
        if (!d.mulai || !d.selesai) { err('Jam mulai dan jam selesai wajib diisi.'); return false; }
        if (d.selesai <= d.mulai) { err('Jam selesai harus setelah jam mulai.'); return false; }
        if (!lok) { err('Lokasi wajib diisi.'); return false; }
        reload();
        if (k) {
          var t = DB.kegiatan.find(function (x) { return x.id === k.id; });
          Object.assign(t, { nama: nama, kategoriId: +d.kategori, penyelenggara: d.penyelenggara.trim(), tanggal: d.tanggal, jam_mulai: d.mulai, jam_selesai: d.selesai, lokasi: lok, deskripsi: d.deskripsi.trim() });
          addLog('Mengubah kegiatan "' + nama + '"'); save(); toast('Kegiatan diperbarui.');
          rerender();
        } else {
          var rec = {
            id: nid('kegiatan'), userId: u.id, kategoriId: +d.kategori, nama: nama, deskripsi: d.deskripsi.trim(), tanggal: d.tanggal, jam_mulai: d.mulai, jam_selesai: d.selesai,
            lokasi: lok, penyelenggara: d.penyelenggara.trim(), kode: randCode(new Set(DB.kegiatan.map(function (x) { return x.kode; }))), status: 'draft', created: new Date().toISOString()
          };
          DB.kegiatan.push(rec); addLog('Menambah kegiatan "' + nama + '"'); save();
          location.href = 'detail-kegiatan.html?id=' + rec.id;
        }
      }
    });
  }
  ACT['k-new'] = function () { formKegiatan(); };
  ACT['k-edit'] = function (el) { formKegiatan(+el.getAttribute('data-id')); };

  /* ================= DETAIL KEGIATAN ================= */
  var qid = function () { return +new URLSearchParams(location.search).get('id'); };
  function presensiLink(k) {
    var base = DB.settings.baseUrl ? DB.settings.baseUrl.replace(/\/?$/, '/') : location.href.replace(/[^/]*([?#].*)?$/, '');
    return base + 'presensi.html?kode=' + k.kode;
  }
  function attTable(k, u, withAct) {
    var hs = hadirOf(k.id);
    if (!hs.length) return '<div class="empty">Belum ada yang presensi.</div>';
    var edit = withAct && canEdit(k, u);
    return '<div class="table-wrap"><table class="tbl"><thead><tr><th class="num">No</th><th>Nama</th><th>Instansi</th><th>Jabatan</th><th>Waktu</th><th>TTD</th><th>Ket.</th>' + (edit ? '<th></th>' : '') + '</tr></thead><tbody>' +
      hs.map(function (h, i) {
        var c = h.status === 'dibatalkan';
        return '<tr class="' + (c ? 'canceled' : '') + '"><td class="num">' + (i + 1) + '</td><td><b>' + esc(h.nama) + '</b></td><td>' + esc(h.instansi) + '</td><td>' + esc(h.jabatan) + '</td><td>' + waktuStr(h.waktu) + '</td>' +
          '<td>' + (h.ttd ? '<img class="sig" alt="tanda tangan" src="' + esc(h.ttd) + '">' : '<span class="cell-sub">-</span>') + '</td>' +
          '<td>' + (c ? '<span class="badge dibatalkan">Dibatalkan</span> ' : '') + esc(h.ket) + '</td>' +
          (edit ? '<td class="right">' + (c ? '' : '<button class="btn sm danger" data-act="h-cancel" data-id="' + h.id + '">Batalkan</button>') + '</td>' : '') + '</tr>';
      }).join('') + '</tbody></table></div>';
  }
  PAGES['detail-kegiatan'] = {
    nav: 'kegiatan', roles: ALL, title: 'Detail Kegiatan',
    sub: function () { var k = DB.kegiatan.find(function (x) { return x.id === qid(); }); return k ? k.nama : 'Kegiatan tidak ditemukan'; },
    actions: function () { return '<a class="btn" href="kegiatan.html">&larr; Daftar kegiatan</a>'; },
    view: function (u) {
      var k = DB.kegiatan.find(function (x) { return x.id === qid(); });
      if (!k || (u.role === 'operator' && k.userId !== u.id)) return '<div class="empty">Kegiatan tidak ditemukan atau Anda tidak berhak melihatnya.<br><br><a class="btn" href="kegiatan.html">Kembali</a></div>';
      var ed = canEdit(k, u), btn = '';
      if (ed) {
        if (k.status === 'draft') btn += '<button class="btn primary" data-act="k-status" data-s="aktif" data-id="' + k.id + '">Aktifkan presensi</button>';
        if (k.status === 'aktif') btn += '<button class="btn accent" data-act="k-status" data-s="selesai" data-id="' + k.id + '">Tutup presensi (selesai)</button>';
        btn += '<button class="btn" data-act="k-edit" data-id="' + k.id + '">Ubah</button>';
        if (k.status === 'draft' || k.status === 'aktif') btn += '<button class="btn danger" data-act="k-status" data-s="dibatalkan" data-id="' + k.id + '">Batalkan kegiatan</button>';
        if (!DB.kehadiran.some(function (h) { return h.kegiatanId === k.id; })) btn += '<button class="btn danger" data-act="k-del" data-id="' + k.id + '">Hapus</button>';
      }
      btn += '<a class="btn" href="pantau.html?id=' + k.id + '">Pantau</a><a class="btn" href="laporan.html?kid=' + k.id + '">Laporan</a>';
      var link = presensiLink(k);
      return '<div class="detail-grid"><div class="stack">' +
        '<div class="card"><div class="card-head"><div><h2 style="font-size:20px">' + esc(k.nama) + '</h2><p class="sub" style="margin:4px 0 0">' + esc(katName(k.kategoriId)) + '</p></div>' + badge(k.status) + '</div>' +
        '<dl class="info-list"><dt>Tanggal</dt><dd>' + tglPanjang(k.tanggal) + '</dd><dt>Jam</dt><dd>' + k.jam_mulai + ' - ' + k.jam_selesai + ' WITA</dd><dt>Lokasi</dt><dd>' + esc(k.lokasi) + '</dd>' +
        '<dt>Penyelenggara</dt><dd>' + esc(k.penyelenggara || '-') + '</dd><dt>Dibuat oleh</dt><dd>' + esc(userName(k.userId)) + '</dd><dt>Deskripsi</dt><dd style="font-weight:500">' + esc(k.deskripsi || '-') + '</dd></dl>' +
        '<div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:18px">' + btn + '</div></div>' +
        '<div class="card"><div class="card-head"><div><h2>Daftar hadir</h2><p class="sub" style="margin:0"><span id="detCount">' + hadirOf(k.id, true).length + '</span> orang tercatat hadir</p></div></div><div id="detAtt">' + attTable(k, u, true) + '</div></div></div>' +
        '<div class="card"><h2>QR Code presensi</h2><p class="sub">Tampilkan di layar atau cetak untuk peserta.</p><div class="qr-box" id="qrBox"><span class="qr-fallback">Membuat QR Code...</span></div>' +
        '<div class="linkbox"><input class="inp" id="plink" readonly value="' + esc(link) + '"><button class="btn sm" data-act="copy">Salin</button></div>' +
        '<div style="display:flex;gap:8px;margin-top:12px;flex-wrap:wrap"><button class="btn sm" data-act="qr-dl" data-id="' + k.id + '">Unduh PNG</button><button class="btn sm" data-act="qr-print" data-id="' + k.id + '">Cetak poster</button><a class="btn sm" target="_blank" rel="noopener" href="' + esc(link) + '">Buka presensi</a></div>' +
        (ed ? '<label class="fld" style="margin-top:16px">Alamat dasar untuk HP<input class="inp" id="baseUrl" value="' + esc(DB.settings.baseUrl) + '" placeholder="http://192.168.1.10:5500/sihadir/"><span class="hint">Opsional. Ganti 127.0.0.1 dengan IP laptop agar QR bisa dibuka dari HP di Wi-Fi yang sama.</span></label><button class="btn sm" style="margin-top:8px" data-act="set-base">Simpan alamat</button>' : '') +
        '<div class="note" style="margin-top:16px;font-size:12.5px">Prototipe menyimpan data di browser ini. Untuk mencoba presensi, buka tautan di tab baru pada browser yang sama.</div></div></div>';
    },
    after: function () {
      var k = DB.kegiatan.find(function (x) { return x.id === qid(); });
      if (k) drawQR(presensiLink(k), 0);
    },
    live: function () {
      var u = me(), k = DB.kegiatan.find(function (x) { return x.id === qid(); });
      if (!k || !$('#detAtt')) return;
      $('#detAtt').innerHTML = attTable(k, u, true); $('#detCount').textContent = hadirOf(k.id, true).length;
    }
  };
  function drawQR(text, n) {
    var box = $('#qrBox'); if (!box) return;
    if (window.QRCode) {
      box.innerHTML = '';
      new window.QRCode(box, { text: text, width: 200, height: 200, correctLevel: window.QRCode.CorrectLevel.M });
      return;
    }
    if (n < 4) { setTimeout(function () { drawQR(text, n + 1); }, 1200); return; }
    box.innerHTML = '<div class="qr-fallback"><b>QR Code belum bisa dibuat.</b><br>Pustaka QR dimuat dari internet. Periksa koneksi lalu muat ulang halaman. Tautan presensi di bawah tetap dapat dipakai.</div>';
  }
  function qrDataUrl() {
    var c = $('#qrBox canvas'), i = $('#qrBox img');
    if (c) return c.toDataURL('image/png');
    return i ? i.src : null;
  }
  ACT.copy = function () {
    var i = $('#plink'); i.select();
    try { navigator.clipboard.writeText(i.value).then(function () { toast('Tautan disalin.'); }, function () { document.execCommand('copy'); toast('Tautan disalin.'); }); }
    catch (e) { document.execCommand('copy'); toast('Tautan disalin.'); }
  };
  ACT['qr-dl'] = function (el) {
    var url = qrDataUrl(); if (!url) { toast('QR Code belum tersedia.', 'err'); return; }
    var k = DB.kegiatan.find(function (x) { return x.id === +el.getAttribute('data-id'); });
    var a = document.createElement('a'); a.href = url; a.download = 'QR-' + k.kode + '.png'; document.body.appendChild(a); a.click(); a.remove();
  };
  ACT['qr-print'] = function (el) {
    var url = qrDataUrl(); if (!url) { toast('QR Code belum tersedia.', 'err'); return; }
    var k = DB.kegiatan.find(function (x) { return x.id === +el.getAttribute('data-id'); });
    var w = window.open('', '_blank');
    if (!w) { toast('Pop-up diblokir browser. Izinkan pop-up lalu coba lagi.', 'err'); return; }
    w.document.write('<!DOCTYPE html><html><head><meta charset="utf-8"><title>Poster QR</title><style>body{font-family:Arial,sans-serif;text-align:center;padding:40px}h1{font-size:30px;margin:0 0 6px}h2{font-size:20px;font-weight:normal;margin:0 0 26px;color:#444}img{width:380px;height:380px}p{font-size:18px;margin-top:20px}small{color:#666;word-break:break-all}</style></head><body><h2>Pindai untuk mengisi daftar hadir</h2><h1>' +
      esc(k.nama) + '</h1><h2>' + esc(tglPanjang(k.tanggal)) + ' &middot; ' + k.jam_mulai + '-' + k.jam_selesai + '<br>' + esc(k.lokasi) + '</h2><img src="' + url + '" alt="QR"><p>Atau buka tautan:<br><small>' + esc(presensiLink(k)) + '</small></p><script>window.onload=function(){window.print()}<\/script></body></html>');
    w.document.close();
  };
  ACT['set-base'] = function () {
    reload(); DB.settings.baseUrl = $('#baseUrl').value.trim(); save(); toast('Alamat dasar disimpan.'); rerender();
  };
  ACT['k-status'] = function (el) {
    var id = +el.getAttribute('data-id'), s = el.getAttribute('data-s');
    reload(); var u = me(), k = DB.kegiatan.find(function (x) { return x.id === id; });
    if (!k || !canEdit(k, u)) { toast('Tidak berhak.', 'err'); return; }
    var go = function () {
      reload(); var kk = DB.kegiatan.find(function (x) { return x.id === id; }); kk.status = s;
      addLog('Mengubah status kegiatan "' + kk.nama + '" menjadi ' + STATUS[s]); save(); toast('Status kegiatan: ' + STATUS[s] + '.'); rerender();
    };
    if (s === 'aktif') return go();
    askConfirm(s === 'selesai' ? 'Tutup presensi dan tandai kegiatan <b>selesai</b>? Peserta tidak dapat presensi lagi.' : 'Batalkan kegiatan ini? Data kehadiran yang ada tetap tersimpan.', go, { danger: s === 'dibatalkan', ok: s === 'selesai' ? 'Ya, tutup' : 'Ya, batalkan' });
  };
  ACT['k-del'] = function (el) {
    var id = +el.getAttribute('data-id');
    askConfirm('Hapus kegiatan ini secara permanen?', function () {
      reload(); var k = DB.kegiatan.find(function (x) { return x.id === id; });
      if (DB.kehadiran.some(function (h) { return h.kegiatanId === id; })) { toast('Kegiatan sudah punya data kehadiran. Gunakan Batalkan.', 'err'); return; }
      DB.kegiatan = DB.kegiatan.filter(function (x) { return x.id !== id; }); addLog('Menghapus kegiatan "' + k.nama + '"'); save();
      location.href = 'kegiatan.html';
    }, { danger: true, ok: 'Ya, hapus' });
  };
  ACT['h-cancel'] = function (el) {
    var id = +el.getAttribute('data-id');
    askConfirm('Batalkan kehadiran ini? Pembatalan dicatat pada log aktivitas.', function () {
      reload(); var h = DB.kehadiran.find(function (x) { return x.id === id; }); var k = DB.kegiatan.find(function (x) { return x.id === h.kegiatanId; });
      if (!canEdit(k, me())) { toast('Tidak berhak.', 'err'); return; }
      h.status = 'dibatalkan'; h.ket = (h.ket ? h.ket + '; ' : '') + 'Dibatalkan oleh ' + me().nama;
      addLog('Membatalkan kehadiran ' + h.nama + ' pada "' + k.nama + '"'); save(); toast('Kehadiran dibatalkan.');
      if (PAGES[page].live) PAGES[page].live();
    }, { danger: true, ok: 'Ya, batalkan' });
  };

  /* ================= PANTAU KEHADIRAN ================= */
  var PID = null, pantauTimer = null;
  function pantauKs(u) {
    var o = { aktif: 0, selesai: 1, draft: 2, dibatalkan: 3 };
    return visK(u).sort(function (a, b) { return (o[a.status] - o[b.status]) || (b.tanggal + b.jam_mulai).localeCompare(a.tanggal + a.jam_mulai); });
  }
  function pantauBody(u) {
    var k = DB.kegiatan.find(function (x) { return x.id === PID; });
    if (!k) return '<div class="empty">Belum ada kegiatan untuk dipantau.</div>';
    var ok = hadirOf(k.id, true).length, ed = canEdit(k, u);
    return '<div class="card-head"><div><span class="big-count">' + ok + '</span> <span class="sub" style="margin:0">orang hadir</span><br><span class="cell-sub">' + esc(k.nama) + ' &middot; ' + tglPendek(k.tanggal) + ', ' + k.jam_mulai + '-' + k.jam_selesai + ' &middot; ' + badge(k.status) + '</span></div>' +
      '<div style="display:flex;gap:8px;flex-wrap:wrap"><a class="btn" target="_blank" rel="noopener" href="presensi.html?kode=' + esc(k.kode) + '">Buka halaman presensi</a>' +
      (ed && k.status !== 'dibatalkan' ? '<button class="btn primary" data-act="h-add" data-id="' + k.id + '">' + icon('plus') + ' Tambah manual</button>' : '') + '</div></div>' + attTable(k, u, true);
  }
  PAGES.pantau = {
    nav: 'pantau', roles: ALL, title: 'Pantau Kehadiran',
    sub: function () { return 'Daftar hadir real-time. Halaman diperbarui otomatis.'; },
    view: function (u) {
      var ks = pantauKs(u), qs = +new URLSearchParams(location.search).get('id');
      if (PID === null || !ks.some(function (k) { return k.id === PID; })) {
        PID = ks.some(function (k) { return k.id === qs; }) ? qs : (ks[0] ? ks[0].id : 0);
      }
      if (!ks.length) return '<div class="empty">Belum ada kegiatan.</div>';
      return '<div class="card"><div class="filters"><select class="inp" id="pSel" style="max-width:420px" data-act="p-sel">' +
        ks.map(function (k) { return '<option value="' + k.id + '"' + (k.id === PID ? ' selected' : '') + '>' + esc(k.nama) + ' (' + tglPendek(k.tanggal) + ' - ' + STATUS[k.status] + ')</option>'; }).join('') +
        '</select></div><div id="pBody">' + pantauBody(u) + '</div><p class="cell-sub" style="margin-top:12px"><span class="live-dot"></span>Terhubung. Data baru muncul otomatis.</p></div>';
    },
    after: function () {
      var sel = $('#pSel');
      if (sel) sel.addEventListener('change', function () { PID = +sel.value; PAGES.pantau.live(); });
      clearInterval(pantauTimer);
      pantauTimer = setInterval(function () { if (!$('.modal-back')) { reload(); autoClose(); PAGES.pantau.live(); } }, 4000);
    },
    live: function () { var b = $('#pBody'); if (b) b.innerHTML = pantauBody(me()); }
  };
  ACT['h-add'] = function (el) {
    var kid = +el.getAttribute('data-id');
    openModal({
      title: 'Tambah Kehadiran Manual',
      body: '<div class="stack" style="gap:12px"><label class="fld">Nama *<input class="inp" name="nama" list="dlP"><datalist id="dlP">' + DB.peserta.map(function (p) { return '<option value="' + esc(p.nama) + '">'; }).join('') + '</datalist></label>' +
        '<label class="fld">Instansi *<input class="inp" name="instansi" list="dlI"><datalist id="dlI">' + DB.instansi.map(function (i) { return '<option value="' + esc(i.nama) + '">'; }).join('') + '</datalist></label>' +
        '<label class="fld">Jabatan *<input class="inp" name="jabatan"></label>' +
        '<label class="fld">Keterangan<input class="inp" name="ket" value="Ditambah manual oleh operator"></label></div>',
      onOk: function (d, err) {
        if (!d.nama.trim() || !d.instansi.trim() || !d.jabatan.trim()) { err('Nama, instansi, dan jabatan wajib diisi.'); return false; }
        reload();
        var k = DB.kegiatan.find(function (x) { return x.id === kid; });
        if (!canEdit(k, me())) { err('Tidak berhak mengubah kegiatan ini.'); return false; }
        var r = addKehadiran({ kegiatanId: kid, nama: d.nama, instansi: d.instansi, jabatan: d.jabatan, ket: d.ket });
        if (!r.ok) { err(r.msg); return false; }
        addLog('Menambah kehadiran manual ' + r.rec.nama + ' pada "' + k.nama + '"'); save(); toast('Kehadiran ditambahkan.');
        PAGES[page].live();
      }
    });
  };

  /* ================= PRESENSI PESERTA (PUBLIK) ================= */
  var sig = { drawing: false, empty: true, ctx: null, cv: null };
  function pubShell(inner) {
    $('#app').innerHTML = '<div class="pub-bg"><div class="pub"><div class="pub-head">' + logoHtml() + '<div><b>SIHADIR</b><small>Daftar Hadir Kegiatan &middot; Diskominfo Kota Tomohon</small></div></div>' + inner + '</div></div><div id="toasts"></div>';
  }
  function stateCard(cls, sym, title, text) {
    return '<div class="pub-card"><div class="state ' + cls + '"><div class="big">' + sym + '</div><h2>' + title + '</h2><p>' + text + '</p></div></div>';
  }
  function renderPresensi() {
    var kode = (new URLSearchParams(location.search).get('kode') || '').trim().toUpperCase();
    if (!kode) {
      pubShell('<div class="pub-card"><div class="state wait"><div class="big">?</div><h2>Kode kegiatan</h2><p style="margin-bottom:14px">Masukkan kode kegiatan atau pindai QR Code dari panitia.</p></div>' +
        '<form id="fKode" class="pub-form"><input class="inp" name="kode" placeholder="Contoh: AB12CD34EF" style="text-transform:uppercase"><button class="btn primary block" type="submit">Lanjut</button></form></div>');
      $('#fKode').addEventListener('submit', function (e) { e.preventDefault(); var v = e.target.kode.value.trim(); if (v) location.href = 'presensi.html?kode=' + encodeURIComponent(v); });
      return;
    }
    var k = DB.kegiatan.find(function (x) { return x.kode.toUpperCase() === kode; });
    if (!k) { pubShell(stateCard('bad', '&#10005;', 'Kegiatan tidak ditemukan', 'Kode atau tautan tidak dikenal. Periksa kembali QR Code dari panitia.')); return; }
    var info = '<div class="event-box"><h2>' + esc(k.nama) + '</h2><p>' + esc(tglPanjang(k.tanggal)) + '<br>' + k.jam_mulai + ' - ' + k.jam_selesai + ' WITA &middot; ' + esc(k.lokasi) + '</p></div>';
    var closed = null;
    if (k.status === 'dibatalkan') closed = ['bad', '&#10005;', 'Kegiatan dibatalkan', 'Kegiatan ini dibatalkan. Presensi tidak dapat dilakukan.'];
    else if (k.status === 'selesai') closed = ['bad', '&#10003;', 'Presensi sudah ditutup', 'Kegiatan ini sudah selesai.'];
    else if (k.status === 'draft') closed = ['wait', '&#8987;', 'Presensi belum dibuka', 'Panitia belum membuka presensi. Coba lagi sebentar lagi.'];
    else {
      var w = windowState(k);
      if (w === 'belum') closed = ['wait', '&#8987;', 'Presensi belum dibuka', 'Presensi dibuka mulai ' + k.jam_mulai + ' (toleransi ' + DB.settings.toleransi + ' menit sebelumnya).'];
      if (w === 'lewat') closed = ['bad', '&#10005;', 'Presensi sudah ditutup', 'Waktu presensi kegiatan ini telah berakhir.'];
    }
    if (closed) { pubShell('<div class="pub-card">' + info + '</div>' + stateCard.apply(null, closed)); return; }
    var ttd = DB.settings.ttdWajib;
    pubShell('<div class="pub-card">' + info +
      '<form id="fPres" class="pub-form" novalidate autocomplete="off">' +
      '<label class="fld">Nama lengkap *<input class="inp" name="nama" id="pNama" list="dlPN" placeholder="Ketik atau pilih nama"><datalist id="dlPN">' + DB.peserta.map(function (p) { return '<option value="' + esc(p.nama) + '">'; }).join('') + '</datalist></label>' +
      '<label class="fld">Instansi *<input class="inp" name="instansi" id="pIns" list="dlPI" placeholder="Asal instansi"><datalist id="dlPI">' + DB.instansi.map(function (i) { return '<option value="' + esc(i.nama) + '">'; }).join('') + '</datalist></label>' +
      '<label class="fld">Jabatan *<input class="inp" name="jabatan" id="pJab"></label>' +
      '<label class="fld">Nomor telepon <span class="hint">(opsional)</span><input class="inp" name="hp" inputmode="tel"></label>' +
      '<div><div style="font-size:12.5px;font-weight:600;color:#33454f;margin-bottom:6px">Tanda tangan ' + (ttd ? '*' : '<span class="hint" style="font-weight:400">(opsional)</span>') + '</div>' +
      '<div class="sig-wrap"><canvas id="sigCv"></canvas><div class="sig-ph" id="sigPh">Tanda tangan di sini</div></div>' +
      '<div class="sig-actions"><span>Gunakan jari atau mouse</span><button class="btn sm" type="button" data-act="sig-clear">Hapus</button></div></div>' +
      '<div class="form-err" id="pErr" hidden></div><button class="btn primary block" type="submit" style="padding:13px">Kirim kehadiran</button></form></div>');
    initSig();
    $('#pNama').addEventListener('input', function () {
      var v = this.value.trim().toLowerCase();
      var p = DB.peserta.find(function (x) { return x.nama.toLowerCase() === v; });
      if (p) { if (!$('#pIns').value) $('#pIns').value = insName(p.instansiId); if (!$('#pJab').value) $('#pJab').value = p.jabatan; }
    });
    $('#fPres').addEventListener('submit', function (e) {
      e.preventDefault(); submitPresensi(k);
    });
  }
  function initSig() {
    var cv = $('#sigCv'); sig.cv = cv; sig.empty = true;
    var ratio = window.devicePixelRatio || 1, r = cv.getBoundingClientRect();
    cv.width = Math.round(r.width * ratio); cv.height = Math.round(r.height * ratio);
    var ctx = cv.getContext('2d'); ctx.scale(ratio, ratio); ctx.lineWidth = 2.4; ctx.lineCap = 'round'; ctx.lineJoin = 'round'; ctx.strokeStyle = '#1a2b6b';
    sig.ctx = ctx;
    var pos = function (e) { var b = cv.getBoundingClientRect(); return { x: e.clientX - b.left, y: e.clientY - b.top }; };
    cv.addEventListener('pointerdown', function (e) {
      e.preventDefault(); cv.setPointerCapture(e.pointerId); sig.drawing = true;
      var p = pos(e); ctx.beginPath(); ctx.moveTo(p.x, p.y); ctx.lineTo(p.x + 0.1, p.y + 0.1); ctx.stroke();
      sig.empty = false; $('#sigPh').hidden = true;
    });
    cv.addEventListener('pointermove', function (e) { if (!sig.drawing) return; e.preventDefault(); var p = pos(e); ctx.lineTo(p.x, p.y); ctx.stroke(); });
    var stop = function () { sig.drawing = false; };
    cv.addEventListener('pointerup', stop); cv.addEventListener('pointercancel', stop); cv.addEventListener('pointerleave', stop);
  }
  ACT['sig-clear'] = function () {
    var cv = sig.cv; if (!cv) return;
    sig.ctx.save(); sig.ctx.setTransform(1, 0, 0, 1, 0, 0); sig.ctx.clearRect(0, 0, cv.width, cv.height); sig.ctx.restore();
    sig.empty = true; $('#sigPh').hidden = false;
  };
  function submitPresensi(k) {
    reload(); autoClose();
    var er = $('#pErr'), f = $('#fPres'), fail = function (m) { er.textContent = m; er.hidden = false; er.scrollIntoView({ block: 'nearest' }); };
    var kk = DB.kegiatan.find(function (x) { return x.id === k.id; });
    if (kk.status !== 'aktif' || windowState(kk) !== 'ok') { renderPresensi(); return; }
    var d = { nama: f.nama.value.trim(), instansi: f.instansi.value.trim(), jabatan: f.jabatan.value.trim(), hp: f.hp.value.trim() };
    if (!d.nama || !d.instansi || !d.jabatan) return fail('Nama, instansi, dan jabatan wajib diisi.');
    var ttd = null;
    if (!sig.empty) {
      ttd = sig.cv.toDataURL('image/png');
      if (ttd.length * 0.75 > 200 * 1024) return fail('Gambar tanda tangan terlalu besar (maksimal 200 KB). Hapus lalu gambar lebih sederhana.');
    } else if (DB.settings.ttdWajib) return fail('Tanda tangan wajib diisi.');
    var r = addKehadiran({ kegiatanId: kk.id, nama: d.nama, instansi: d.instansi, jabatan: d.jabatan, hp: d.hp, ttd: ttd });
    if (!r.ok) return fail(r.msg);
    addLog('Presensi: ' + r.rec.nama + ' pada "' + kk.nama + '"', null); save();
    pubShell('<div class="pub-card"><div class="state ok"><div class="big">&#10003;</div><h2>Kehadiran tercatat</h2><p>Terima kasih, <b>' + esc(r.rec.nama) + '</b>.<br>' + esc(kk.nama) + '<br>' + waktuStr(r.rec.waktu) + '</p></div>' +
      '<button class="btn block" style="margin-top:12px" data-act="pres-again">Isi untuk peserta lain</button></div>');
  }
  ACT['pres-again'] = function () { renderPresensi(); };

  /* ================= DATA PESERTA ================= */
  function pesRows() {
    var q = FILT.p.q.toLowerCase();
    var ps = DB.peserta.slice().sort(function (a, b) { return a.nama.localeCompare(b.nama); }).filter(function (p) {
      return !q || p.nama.toLowerCase().indexOf(q) >= 0 || (p.nip || '').indexOf(q) >= 0 || p.jabatan.toLowerCase().indexOf(q) >= 0 || insName(p.instansiId).toLowerCase().indexOf(q) >= 0;
    });
    if (!ps.length) return '<div class="empty">Data peserta tidak ditemukan.</div>';
    return '<div class="table-wrap"><table class="tbl"><thead><tr><th class="num">No</th><th>Nama</th><th>NIP/NIK</th><th>Jabatan</th><th>Instansi</th><th></th></tr></thead><tbody>' +
      ps.map(function (p, i) {
        return '<tr><td class="num">' + (i + 1) + '</td><td><b>' + esc(p.nama) + '</b><span class="cell-sub" style="display:block">' + esc(p.hp || p.email || '') + '</span></td><td>' + esc(p.nip || '-') + '</td><td>' + esc(p.jabatan) + '</td><td>' + esc(insName(p.instansiId)) + '</td>' +
          '<td class="right"><div class="row-actions"><button class="btn sm" data-act="p-edit" data-id="' + p.id + '">Ubah</button><button class="btn sm danger" data-act="p-del" data-id="' + p.id + '">Hapus</button></div></td></tr>';
      }).join('') + '</tbody></table></div>';
  }
  PAGES.peserta = {
    nav: 'peserta', roles: ['admin', 'operator'], title: 'Data Peserta', sub: function () { return 'Peserta terdaftar dan master instansi. Impor dari CSV tersedia.'; },
    actions: function () { return '<button class="btn" data-act="p-import">Impor CSV</button><button class="btn primary" data-act="p-new">' + icon('plus') + ' Tambah Peserta</button>'; },
    view: function () {
      return '<div class="card"><div class="filters"><input class="inp" data-f="p:q" placeholder="Cari nama, NIP/NIK, jabatan, instansi..." value="' + esc(FILT.p.q) + '" style="max-width:420px"></div><div id="pesTable">' + pesRows() + '</div></div>';
    },
    refresh: function () { $('#pesTable').innerHTML = pesRows(); }
  };
  function formPeserta(id) {
    var p = id ? DB.peserta.find(function (x) { return x.id === id; }) : null;
    var v = function (f) { return esc(p ? p[f] : ''); };
    openModal({
      title: p ? 'Ubah Peserta' : 'Tambah Peserta',
      body: '<div class="grid2"><label class="fld span2">Nama lengkap *<input class="inp" name="nama" value="' + v('nama') + '"></label>' +
        '<label class="fld">NIP / NIK <span class="hint">(opsional, unik)</span><input class="inp" name="nip" value="' + v('nip') + '"></label>' +
        '<label class="fld">Jabatan *<input class="inp" name="jabatan" value="' + v('jabatan') + '"></label>' +
        '<label class="fld span2">Instansi *<select class="inp" name="instansi">' + DB.instansi.map(function (i) { return '<option value="' + i.id + '"' + (p && p.instansiId === i.id ? ' selected' : '') + '>' + esc(i.nama) + '</option>'; }).join('') + '</select></label>' +
        '<label class="fld">No. telepon<input class="inp" name="hp" value="' + v('hp') + '"></label><label class="fld">Email<input class="inp" name="email" value="' + v('email') + '"></label></div>',
      onOk: function (d, err) {
        reload();
        var nama = d.nama.trim(), nip = d.nip.trim();
        if (!nama || !d.jabatan.trim()) { err('Nama dan jabatan wajib diisi.'); return false; }
        if (nip && DB.peserta.some(function (x) { return x.nip === nip && (!p || x.id !== p.id); })) { err('NIP/NIK sudah dipakai peserta lain.'); return false; }
        var rec = { instansiId: +d.instansi, nip: nip, nama: nama, jabatan: d.jabatan.trim(), hp: d.hp.trim(), email: d.email.trim() };
        if (p) { Object.assign(DB.peserta.find(function (x) { return x.id === p.id; }), rec); addLog('Mengubah peserta ' + nama); }
        else { rec.id = nid('peserta'); DB.peserta.push(rec); addLog('Menambah peserta ' + nama); }
        save(); toast('Data peserta disimpan.'); PAGES.peserta.refresh();
      }
    });
  }
  ACT['p-new'] = function () { formPeserta(); };
  ACT['p-edit'] = function (el) { formPeserta(+el.getAttribute('data-id')); };
  ACT['p-del'] = function (el) {
    var id = +el.getAttribute('data-id');
    askConfirm('Hapus data peserta ini?', function () {
      reload();
      if (DB.kehadiran.some(function (h) { return h.pesertaId === id; })) { toast('Peserta sudah punya riwayat kehadiran, tidak dapat dihapus.', 'err'); return; }
      var p = DB.peserta.find(function (x) { return x.id === id; });
      DB.peserta = DB.peserta.filter(function (x) { return x.id !== id; }); addLog('Menghapus peserta ' + p.nama); save(); toast('Peserta dihapus.'); PAGES.peserta.refresh();
    }, { danger: true, ok: 'Ya, hapus' });
  };
  function parseCSV(text) {
    text = text.replace(/^\ufeff/, '');
    var lines = text.split(/\r?\n/).filter(function (l) { return l.trim(); });
    if (!lines.length) return [];
    var head = lines[0];
    var delim = [';', ',', '\t'].map(function (d) { return [d, head.split(d).length]; }).sort(function (a, b) { return b[1] - a[1]; })[0][0];
    var split = function (l) {
      var out = [], cur = '', q = false, i, c;
      for (i = 0; i < l.length; i++) {
        c = l[i];
        if (q) { if (c === '"') { if (l[i + 1] === '"') { cur += '"'; i++; } else q = false; } else cur += c; }
        else if (c === '"') q = true; else if (c === delim) { out.push(cur); cur = ''; } else cur += c;
      }
      out.push(cur);
      return out.map(function (s) { return s.trim(); });
    };
    return lines.map(split);
  }
  function importPeserta(text) {
    reload();
    var rows = parseCSV(text), res = { ok: 0, bad: [] };
    if (!rows.length) { res.bad.push('Berkas kosong.'); return res; }
    var h = rows[0].map(function (s) { return s.toLowerCase().replace(/[^a-z_]/g, ''); });
    var ix = function (n) { return h.indexOf(n); };
    if (ix('nama') < 0) { res.bad.push('Kolom "nama" tidak ditemukan pada baris judul. Gunakan templat.'); return res; }
    rows.slice(1).forEach(function (r, i) {
      var g = function (n) { return ix(n) >= 0 ? (r[ix(n)] || '').trim() : ''; };
      var line = i + 2, nama = g('nama'), jab = g('jabatan'), ins = g('instansi'), nip = g('nipnik') || g('nip_nik') || g('nip');
      if (!nama) return res.bad.push('Baris ' + line + ': nama kosong.');
      if (!jab) return res.bad.push('Baris ' + line + ' (' + nama + '): jabatan kosong.');
      if (!ins) return res.bad.push('Baris ' + line + ' (' + nama + '): instansi kosong.');
      if (nip && DB.peserta.some(function (x) { return x.nip === nip; })) return res.bad.push('Baris ' + line + ' (' + nama + '): NIP/NIK sudah ada.');
      var I = DB.instansi.find(function (x) { return x.nama.toLowerCase() === ins.toLowerCase(); });
      if (!I) { I = { id: nid('instansi'), nama: ins }; DB.instansi.push(I); }
      DB.peserta.push({ id: nid('peserta'), instansiId: I.id, nip: nip, nama: nama, jabatan: jab, hp: g('nohp') || g('no_hp'), email: g('email') });
      res.ok++;
    });
    addLog('Impor peserta: ' + res.ok + ' berhasil, ' + res.bad.length + ' ditolak'); save();
    return res;
  }
  ACT['p-import'] = function () {
    var m = openModal({
      title: 'Impor Peserta dari CSV', ok: 'Impor',
      body: '<p style="margin-bottom:12px;font-size:13.5px">Kolom: <code>nip_nik; nama; jabatan; instansi; no_hp; email</code>. Dari Excel, simpan sebagai <b>CSV</b>. Baris yang tidak valid dilaporkan tanpa membatalkan baris yang valid.</p>' +
        '<button type="button" class="btn sm" data-act="p-tpl" style="margin-bottom:12px">Unduh templat</button><input class="inp" type="file" accept=".csv,.txt">',
      onOk: function (d, err) {
        var f = $('input[type=file]', m.el).files[0];
        if (!f) { err('Pilih berkas CSV terlebih dahulu.'); return false; }
        var rd = new FileReader();
        rd.onload = function () {
          var res = importPeserta(String(rd.result)); m.close(); PAGES.peserta.refresh();
          openModal({
            title: 'Hasil Impor', cancel: null, ok: 'Tutup',
            body: '<p><b>' + res.ok + '</b> baris berhasil diimpor, <b>' + res.bad.length + '</b> ditolak.</p>' + (res.bad.length ? '<ul style="margin:10px 0 0;padding-left:18px;font-size:13px;color:#8e2c22">' + res.bad.map(function (b) { return '<li>' + esc(b) + '</li>'; }).join('') + '</ul>' : '')
          });
        };
        rd.readAsText(f);
        return false;
      }
    });
  };
  ACT['p-tpl'] = function () {
    download('templat-peserta.csv', '\ufeffnip_nik;nama;jabatan;instansi;no_hp;email\r\n;Contoh Nama;Staf;Diskominfo Kota Tomohon;;\r\n', 'text/csv;charset=utf-8');
  };

  /* ================= LAPORAN ================= */
  var R = { tab: 'kegiatan', kid: 0, from: '', to: '', nama: '' };
  function kop(title) {
    return '<div class="kop">' + logoHtml() + '<div><h2>PEMERINTAH KOTA TOMOHON</h2><h3>DINAS KOMUNIKASI DAN INFORMATIKA</h3></div></div><div class="rep-title">' + title + '</div>';
  }
  function repKegiatan(u) {
    var ks = visK(u).sort(function (a, b) { return (b.tanggal + b.jam_mulai).localeCompare(a.tanggal + a.jam_mulai); });
    var sel = '<div class="filters no-print"><select class="inp" data-act="r-kid" id="rKid" style="max-width:480px">' + ks.map(function (k) { return '<option value="' + k.id + '"' + (k.id === R.kid ? ' selected' : '') + '>' + esc(k.nama) + ' (' + tglPendek(k.tanggal) + ')</option>'; }).join('') + '</select></div>';
    var k = ks.find(function (x) { return x.id === R.kid; });
    if (!k) return sel + '<div class="empty">Pilih kegiatan.</div>';
    var hs = hadirOf(k.id, true);
    return sel + '<div class="print-area">' + kop('Daftar Hadir Kegiatan') +
      '<div class="rep-meta"><span>Kegiatan</span><b>: ' + esc(k.nama) + '</b><span>Kategori</span><b>: ' + esc(katName(k.kategoriId)) + '</b><span>Hari/tanggal</span><b>: ' + tglPanjang(k.tanggal) + '</b><span>Waktu</span><b>: ' + k.jam_mulai + ' - ' + k.jam_selesai + ' WITA</b><span>Tempat</span><b>: ' + esc(k.lokasi) + '</b><span>Penyelenggara</span><b>: ' + esc(k.penyelenggara || '-') + '</b></div>' +
      (hs.length ? '<div class="table-wrap"><table class="tbl"><thead><tr><th class="num">No</th><th>Nama</th><th>Instansi</th><th>Jabatan</th><th>Waktu hadir</th><th>Tanda tangan</th><th>Ket.</th></tr></thead><tbody>' +
        hs.map(function (h, i) { return '<tr><td class="num">' + (i + 1) + '</td><td>' + esc(h.nama) + '</td><td>' + esc(h.instansi) + '</td><td>' + esc(h.jabatan) + '</td><td>' + waktuStr(h.waktu) + '</td><td>' + (h.ttd ? '<img class="sig" alt="" src="' + esc(h.ttd) + '">' : '-') + '</td><td>' + esc(h.ket) + '</td></tr>'; }).join('') + '</tbody></table></div>' : '<div class="empty">Belum ada kehadiran.</div>') +
      '<p style="margin-top:12px;font-weight:700">Jumlah hadir: ' + hs.length + ' orang</p>' +
      '<div class="rep-sign"><div>Tomohon, ' + tglPanjang(todayStr()).split(', ')[1] + '<br>Penanggung jawab kegiatan<div class="space"></div><b><u>' + esc(userName(k.userId)) + '</u></b></div></div></div>';
  }
  function repPeriode(u) {
    var from = R.from, to = R.to;
    var ks = visK(u).filter(function (k) { return k.status !== 'dibatalkan' && (!from || k.tanggal >= from) && (!to || k.tanggal <= to); }).sort(function (a, b) { return a.tanggal.localeCompare(b.tanggal); });
    var total = 0;
    var body = ks.map(function (k, i) { var n = hadirOf(k.id, true).length; total += n; return '<tr><td class="num">' + (i + 1) + '</td><td>' + esc(k.nama) + '</td><td>' + esc(katName(k.kategoriId)) + '</td><td>' + tglPendek(k.tanggal) + '</td><td>' + STATUS[k.status] + '</td><td><b>' + n + '</b></td></tr>'; }).join('');
    return '<div class="filters no-print"><label class="fld" style="flex-direction:row;align-items:center;gap:8px">Dari<input class="inp" type="date" data-act="r-from" id="rFrom" value="' + esc(from) + '"></label><label class="fld" style="flex-direction:row;align-items:center;gap:8px">Sampai<input class="inp" type="date" data-act="r-to" id="rTo" value="' + esc(to) + '"></label></div>' +
      '<div class="print-area">' + kop('Rekapitulasi Kehadiran per Periode') +
      '<p style="margin-bottom:12px">Periode: <b>' + (from ? tglPendek(from) : 'awal') + ' s.d. ' + (to ? tglPendek(to) : 'sekarang') + '</b></p>' +
      (ks.length ? '<div class="table-wrap"><table class="tbl"><thead><tr><th class="num">No</th><th>Kegiatan</th><th>Kategori</th><th>Tanggal</th><th>Status</th><th>Hadir</th></tr></thead><tbody>' + body + '</tbody></table></div>' : '<div class="empty">Tidak ada kegiatan pada periode ini.</div>') +
      '<p style="margin-top:12px;font-weight:700">Jumlah kegiatan: ' + ks.length + ' &middot; Total kehadiran: ' + total + '</p></div>';
  }
  function repPeserta(u) {
    var ids = {}; visK(u).forEach(function (k) { ids[k.id] = k; });
    var q = R.nama.trim().toLowerCase();
    var hs = q ? DB.kehadiran.filter(function (h) { return ids[h.kegiatanId] && h.status === 'tercatat' && h.nama.toLowerCase() === q; }).sort(function (a, b) { return ids[a.kegiatanId].tanggal.localeCompare(ids[b.kegiatanId].tanggal); }) : [];
    var names = {}; DB.kehadiran.forEach(function (h) { if (ids[h.kegiatanId]) names[h.nama] = 1; });
    return '<div class="filters no-print"><input class="inp" id="rNama" list="dlRN" placeholder="Ketik nama peserta lalu pilih" value="' + esc(R.nama) + '" data-act="r-nama" style="max-width:380px"><datalist id="dlRN">' + Object.keys(names).sort().map(function (n) { return '<option value="' + esc(n) + '">'; }).join('') + '</datalist></div>' +
      '<div class="print-area">' + kop('Riwayat Kehadiran Peserta') + '<p style="margin-bottom:12px">Nama: <b>' + esc(R.nama || '-') + '</b></p>' +
      (hs.length ? '<div class="table-wrap"><table class="tbl"><thead><tr><th class="num">No</th><th>Kegiatan</th><th>Tanggal</th><th>Instansi</th><th>Jabatan</th><th>Waktu hadir</th></tr></thead><tbody>' + hs.map(function (h, i) { var k = ids[h.kegiatanId]; return '<tr><td class="num">' + (i + 1) + '</td><td>' + esc(k.nama) + '</td><td>' + tglPendek(k.tanggal) + '</td><td>' + esc(h.instansi) + '</td><td>' + esc(h.jabatan) + '</td><td>' + waktuStr(h.waktu) + '</td></tr>'; }).join('') + '</tbody></table></div><p style="margin-top:12px;font-weight:700">Total kegiatan dihadiri: ' + hs.length + '</p>' : '<div class="empty">' + (q ? 'Tidak ada riwayat kehadiran untuk nama ini.' : 'Pilih nama peserta.') + '</div>') + '</div>';
  }
  function repHtml(u) {
    return '<div class="card"><div class="tabs">' + [['kegiatan', 'Per kegiatan'], ['periode', 'Per periode'], ['peserta', 'Per peserta']].map(function (t) { return '<button class="tab ' + (R.tab === t[0] ? 'active' : '') + '" data-act="r-tab" data-t="' + t[0] + '">' + t[1] + '</button>'; }).join('') + '</div>' +
      '<div class="no-print" style="display:flex;gap:8px;justify-content:flex-end;margin-bottom:12px"><button class="btn" data-act="r-xls">Unduh Excel</button><button class="btn primary" data-act="r-print">Cetak / Simpan PDF</button></div>' +
      '<div id="repBody">' + (R.tab === 'kegiatan' ? repKegiatan(u) : R.tab === 'periode' ? repPeriode(u) : repPeserta(u)) + '</div></div>';
  }
  PAGES.laporan = {
    nav: 'laporan', roles: ALL, title: 'Laporan & Ekspor', sub: function () { return 'Cetak, simpan sebagai PDF, atau unduh ke Excel.'; },
    view: function (u) {
      var ks = visK(u).sort(function (a, b) { return (b.tanggal + b.jam_mulai).localeCompare(a.tanggal + a.jam_mulai); });
      var qk = +new URLSearchParams(location.search).get('kid');
      if (!R.kid || !ks.some(function (k) { return k.id === R.kid; })) { R.kid = ks.some(function (k) { return k.id === qk; }) ? qk : (ks[0] ? ks[0].id : 0); }
      return repHtml(u);
    },
    refresh: function () { $('#content').innerHTML = repHtml(me()); }
  };
  ACT['r-tab'] = function (el) { R.tab = el.getAttribute('data-t'); PAGES.laporan.refresh(); };
  document.addEventListener('change', function (e) {
    var a = e.target.getAttribute && e.target.getAttribute('data-act');
    if (page !== 'laporan' || !a) return;
    if (a === 'r-kid') R.kid = +e.target.value;
    else if (a === 'r-from') R.from = e.target.value;
    else if (a === 'r-to') R.to = e.target.value;
    else if (a === 'r-nama') R.nama = e.target.value;
    else return;
    PAGES.laporan.refresh();
  });
  ACT['r-print'] = function () { window.print(); };
  ACT['r-xls'] = function () {
    var u = me(), head, rows, name = 'laporan';
    if (R.tab === 'kegiatan') {
      var k = visK(u).find(function (x) { return x.id === R.kid; }); if (!k) { toast('Pilih kegiatan.', 'err'); return; }
      head = ['No', 'Nama', 'Instansi', 'Jabatan', 'Waktu hadir', 'Tanda tangan', 'Keterangan'];
      rows = hadirOf(k.id, true).map(function (h, i) { return [i + 1, h.nama, h.instansi, h.jabatan, waktuStr(h.waktu), h.ttd ? 'Ya' : '-', h.ket]; });
      name = 'daftar-hadir-' + k.kode;
    } else if (R.tab === 'periode') {
      head = ['No', 'Kegiatan', 'Kategori', 'Tanggal', 'Status', 'Hadir'];
      rows = visK(u).filter(function (k) { return k.status !== 'dibatalkan' && (!R.from || k.tanggal >= R.from) && (!R.to || k.tanggal <= R.to); }).sort(function (a, b) { return a.tanggal.localeCompare(b.tanggal); })
        .map(function (k, i) { return [i + 1, k.nama, katName(k.kategoriId), tglPendek(k.tanggal), STATUS[k.status], hadirOf(k.id, true).length]; });
      name = 'rekap-periode';
    } else {
      var ids = {}; visK(u).forEach(function (k) { ids[k.id] = k; });
      head = ['No', 'Kegiatan', 'Tanggal', 'Instansi', 'Jabatan', 'Waktu hadir'];
      rows = DB.kehadiran.filter(function (h) { return ids[h.kegiatanId] && h.status === 'tercatat' && h.nama.toLowerCase() === R.nama.trim().toLowerCase(); })
        .map(function (h, i) { var k = ids[h.kegiatanId]; return [i + 1, k.nama, tglPendek(k.tanggal), h.instansi, h.jabatan, waktuStr(h.waktu)]; });
      name = 'riwayat-peserta';
    }
    if (!rows.length) { toast('Tidak ada data untuk diekspor.', 'err'); return; }
    var tbl = '<table border="1"><tr>' + head.map(function (x) { return '<th style="background:#ddd">' + esc(x) + '</th>'; }).join('') + '</tr>' + rows.map(function (r) { return '<tr>' + r.map(function (c) { return '<td>' + esc(c) + '</td>'; }).join('') + '</tr>'; }).join('') + '</table>';
    download(name + '.xls', '\ufeff<html xmlns:x="urn:schemas-microsoft-com:office:excel"><head><meta charset="utf-8"></head><body>' + tbl + '</body></html>', 'application/vnd.ms-excel;charset=utf-8');
    toast('File Excel diunduh.');
  };

  /* ================= PENGGUNA ================= */
  function userRows(u) {
    return '<div class="table-wrap"><table class="tbl"><thead><tr><th>Nama</th><th>Username</th><th>Peran</th><th>Status</th><th></th></tr></thead><tbody>' +
      DB.users.map(function (x) {
        var self = x.id === u.id;
        return '<tr><td><b>' + esc(x.nama) + '</b>' + (self ? ' <span class="cell-sub">(Anda)</span>' : '') + '</td><td>' + esc(x.username) + '</td><td><span class="badge role">' + ROLE[x.role] + '</span></td><td>' + (x.aktif ? '<span class="badge aktif">Aktif</span>' : '<span class="badge off">Nonaktif</span>') + '</td>' +
          '<td class="right"><div class="row-actions"><button class="btn sm" data-act="u-edit" data-id="' + x.id + '">Ubah</button><button class="btn sm" data-act="u-reset" data-id="' + x.id + '">Reset sandi</button>' +
          (self ? '' : '<button class="btn sm ' + (x.aktif ? 'danger' : '') + '" data-act="u-toggle" data-id="' + x.id + '">' + (x.aktif ? 'Nonaktifkan' : 'Aktifkan') + '</button>') + '</div></td></tr>';
      }).join('') + '</tbody></table></div>';
  }
  PAGES.pengguna = {
    nav: 'pengguna', roles: ['admin'], title: 'Pengguna', sub: function () { return 'Kelola akun, peran, dan status pengguna.'; },
    actions: function () { return '<button class="btn primary" data-act="u-new">' + icon('plus') + ' Tambah Pengguna</button>'; },
    view: function (u) { return '<div class="card">' + userRows(u) + '</div>'; }
  };
  function formUser(id) {
    var x = id ? DB.users.find(function (a) { return a.id === id; }) : null;
    openModal({
      title: x ? 'Ubah Pengguna' : 'Tambah Pengguna',
      body: '<div class="stack" style="gap:12px"><label class="fld">Nama lengkap *<input class="inp" name="nama" value="' + esc(x ? x.nama : '') + '"></label>' +
        '<label class="fld">Username * <span class="hint">(unik)</span><input class="inp" name="username" value="' + esc(x ? x.username : '') + '"></label>' +
        '<label class="fld">Peran *<select class="inp" name="role">' + ['admin', 'operator', 'pimpinan'].map(function (r) { return '<option value="' + r + '"' + (x && x.role === r ? ' selected' : '') + '>' + ROLE[r] + '</option>'; }).join('') + '</select></label>' +
        (x ? '' : '<label class="fld">Kata sandi awal * <span class="hint">(min. 6 karakter)</span><input class="inp" name="password" type="text"></label>') + '</div>',
      onOk: function (d, err) {
        reload();
        var nama = d.nama.trim(), un = d.username.trim().toLowerCase();
        if (!nama || !un) { err('Nama dan username wajib diisi.'); return false; }
        if (!/^[a-z0-9._-]{3,30}$/.test(un)) { err('Username 3-30 karakter: huruf kecil, angka, titik, garis bawah, atau strip.'); return false; }
        if (DB.users.some(function (a) { return a.username.toLowerCase() === un && (!x || a.id !== x.id); })) { err('Username sudah dipakai.'); return false; }
        if (x) {
          var t = DB.users.find(function (a) { return a.id === x.id; });
          if (t.id === me().id && d.role !== 'admin') { err('Anda tidak dapat mengubah peran akun Anda sendiri.'); return false; }
          t.nama = nama; t.username = un; t.role = d.role; addLog('Mengubah akun ' + un);
        } else {
          if (!d.password || d.password.length < 6) { err('Kata sandi awal minimal 6 karakter.'); return false; }
          DB.users.push({ id: nid('users'), nama: nama, username: un, password: d.password, role: d.role, aktif: 1 }); addLog('Menambah akun ' + un);
        }
        save(); toast('Akun disimpan.'); rerender();
      }
    });
  }
  ACT['u-new'] = function () { formUser(); };
  ACT['u-edit'] = function (el) { formUser(+el.getAttribute('data-id')); };
  ACT['u-toggle'] = function (el) {
    reload(); var x = DB.users.find(function (a) { return a.id === +el.getAttribute('data-id'); });
    x.aktif = x.aktif ? 0 : 1; addLog((x.aktif ? 'Mengaktifkan' : 'Menonaktifkan') + ' akun ' + x.username); save(); toast('Akun ' + (x.aktif ? 'diaktifkan' : 'dinonaktifkan') + '.'); rerender();
  };
  ACT['u-reset'] = function (el) {
    var id = +el.getAttribute('data-id');
    openModal({
      title: 'Reset Kata Sandi', body: '<label class="fld">Kata sandi baru <span class="hint">(min. 6 karakter)</span><input class="inp" name="pw" type="text"></label>',
      onOk: function (d, err) {
        if (!d.pw || d.pw.length < 6) { err('Minimal 6 karakter.'); return false; }
        reload(); var x = DB.users.find(function (a) { return a.id === id; }); x.password = d.pw; addLog('Mereset kata sandi akun ' + x.username); save(); toast('Kata sandi direset.');
      }
    });
  };

  /* ================= MASTER DATA & PENGATURAN ================= */
  function chips(arr, type) {
    return '<div class="chips">' + arr.map(function (c) { return '<span class="chip">' + esc(c.nama) + '<button type="button" title="Hapus" data-act="m-del" data-t="' + type + '" data-id="' + c.id + '">&times;</button></span>'; }).join('') + '</div>' +
      '<div class="mini-add"><input class="inp" id="add-' + type + '" placeholder="Tambah ' + (type === 'kategori' ? 'kategori' : 'instansi') + ' baru"><button class="btn primary" data-act="m-add" data-t="' + type + '">Tambah</button></div>';
  }
  PAGES.master = {
    nav: 'master', roles: ['admin'], title: 'Master Data', sub: function () { return 'Kategori kegiatan, instansi, dan pengaturan sistem.'; },
    view: function () {
      var s = DB.settings;
      return '<div class="two-col"><div class="stack"><div class="card"><h2>Kategori kegiatan</h2><p class="sub">Dipakai pada formulir kegiatan.</p>' + chips(DB.kategori, 'kategori') + '</div>' +
        '<div class="card"><h2>Instansi / bidang</h2><p class="sub">Dipakai pada data peserta dan pilihan presensi.</p>' + chips(DB.instansi, 'instansi') + '</div></div>' +
        '<div class="stack"><div class="card"><h2>Pengaturan sistem</h2><p class="sub">Aturan presensi.</p><div class="stack" style="gap:14px">' +
        '<label class="check"><input type="checkbox" id="sTtd"' + (s.ttdWajib ? ' checked' : '') + '><span>Tanda tangan digital wajib saat presensi</span></label>' +
        '<label class="check"><input type="checkbox" id="sDemo"' + (s.demoWaktu ? ' checked' : '') + '><span>Mode demo: abaikan batas waktu presensi<br><small style="color:var(--muted)">Berguna saat uji coba. Matikan untuk menguji aturan waktu.</small></span></label>' +
        '<label class="fld">Toleransi waktu presensi (menit)<input class="inp" type="number" min="0" max="120" id="sTol" value="' + esc(s.toleransi) + '" style="max-width:140px"></label>' +
        '<div><button class="btn primary" data-act="m-save">Simpan pengaturan</button></div></div></div>' +
        '<div class="card"><h2>Data prototipe</h2><p class="sub">Kembalikan seluruh data ke data contoh awal. Tindakan ini menghapus semua kegiatan, peserta, dan kehadiran yang Anda buat.</p><button class="btn danger" data-act="m-reset">Reset data demo</button></div></div></div>';
    }
  };
  ACT['m-add'] = function (el) {
    var t = el.getAttribute('data-t'), i = $('#add-' + t), v = i.value.trim();
    if (!v) { toast('Isi nama terlebih dahulu.', 'err'); return; }
    reload();
    if (DB[t].some(function (x) { return x.nama.toLowerCase() === v.toLowerCase(); })) { toast('Sudah ada.', 'err'); return; }
    DB[t].push({ id: nid(t), nama: v }); addLog('Menambah master ' + t + ': ' + v); save(); toast('Ditambahkan.'); rerender();
  };
  ACT['m-del'] = function (el) {
    var t = el.getAttribute('data-t'), id = +el.getAttribute('data-id');
    reload();
    var used = t === 'kategori' ? DB.kegiatan.some(function (k) { return k.kategoriId === id; }) : DB.peserta.some(function (p) { return p.instansiId === id; });
    if (used) { toast('Masih dipakai data lain, tidak dapat dihapus.', 'err'); return; }
    var x = DB[t].find(function (a) { return a.id === id; });
    DB[t] = DB[t].filter(function (a) { return a.id !== id; }); addLog('Menghapus master ' + t + ': ' + x.nama); save(); toast('Dihapus.'); rerender();
  };
  ACT['m-save'] = function () {
    reload(); var tol = parseInt($('#sTol').value, 10);
    DB.settings.ttdWajib = $('#sTtd').checked; DB.settings.demoWaktu = $('#sDemo').checked; DB.settings.toleransi = isNaN(tol) || tol < 0 ? 15 : Math.min(tol, 120);
    addLog('Mengubah pengaturan sistem'); save(); toast('Pengaturan disimpan.'); rerender();
  };
  ACT['m-reset'] = function () {
    askConfirm('Kembalikan semua data ke data contoh awal? Tindakan ini tidak dapat dibatalkan.', function () {
      localStorage.removeItem(KEY); seed(); save(); toast('Data demo dikembalikan.'); rerender();
    }, { danger: true, ok: 'Ya, reset' });
  };

  /* ================= LOG ================= */
  function logRows() {
    var q = FILT.l.q.toLowerCase();
    var ls = DB.log.slice().reverse().filter(function (l) { return !q || l.aktivitas.toLowerCase().indexOf(q) >= 0 || l.nama.toLowerCase().indexOf(q) >= 0; }).slice(0, 200);
    if (!ls.length) return '<div class="empty">Belum ada aktivitas.</div>';
    return '<div class="table-wrap"><table class="tbl"><thead><tr><th>Waktu</th><th>Pengguna</th><th>Aktivitas</th></tr></thead><tbody>' +
      ls.map(function (l) { return '<tr><td style="white-space:nowrap">' + waktuStr(l.waktu) + '</td><td>' + esc(l.nama) + '</td><td>' + esc(l.aktivitas) + '</td></tr>'; }).join('') + '</tbody></table></div>';
  }
  PAGES.log = {
    nav: 'log', roles: ['admin'], title: 'Log Aktivitas', sub: function () { return '200 aktivitas terbaru.'; },
    view: function () { return '<div class="card"><div class="filters"><input class="inp" data-f="l:q" placeholder="Cari aktivitas atau pengguna..." value="' + esc(FILT.l.q) + '" style="max-width:420px"></div><div id="logTable">' + logRows() + '</div></div>'; },
    refresh: function () { $('#logTable').innerHTML = logRows(); }
  };

  /* ================= START ================= */
  function init() {
    reload(); autoClose();
    if (page === 'presensi') { renderPresensi(); return; }
    if (page === 'login') { renderLogin(); return; }
    var u = me(), def = PAGES[page];
    if (!u) { location.replace('login.html'); return; }
    if (!def) { location.replace('dashboard.html'); return; }
    if (def.roles.indexOf(u.role) < 0) { location.replace('dashboard.html'); return; }
    buildShell(u, def);
    $('#content').innerHTML = def.view(u);
    if (def.after) def.after(u);
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
})();
