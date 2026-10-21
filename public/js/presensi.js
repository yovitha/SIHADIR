/* Halaman presensi publik: tanda tangan digital + pencarian peserta terdaftar */
(function () {
  'use strict';

  var form = document.getElementById('form-presensi');
  if (!form) return;

  /* ---------- Tanda tangan digital (Signature Pad) ---------- */
  var kanvas = document.getElementById('kanvas-ttd');
  var keluaran = document.getElementById('tanda_tangan');
  var kotak = document.getElementById('ttd-kotak');
  var pad = null;

  function ukuran() {
    if (!kanvas) return;
    var rasio = Math.max(window.devicePixelRatio || 1, 1);
    var data = pad && !pad.isEmpty() ? pad.toData() : null;
    kanvas.width = kanvas.offsetWidth * rasio;
    kanvas.height = kanvas.offsetHeight * rasio;
    kanvas.getContext('2d').scale(rasio, rasio);
    if (pad) { pad.clear(); if (data) pad.fromData(data); }
  }

  if (kanvas && window.SignaturePad) {
    pad = new window.SignaturePad(kanvas, { minWidth: 0.8, maxWidth: 2.6, penColor: '#0A3B39' });
    ukuran();
    window.addEventListener('resize', ukuran);
    pad.addEventListener('beginStroke', function () { kotak.classList.add('terisi'); });

    var hapus = document.getElementById('hapus-ttd');
    if (hapus) hapus.addEventListener('click', function () {
      pad.clear();
      kotak.classList.remove('terisi');
      keluaran.value = '';
    });

    form.addEventListener('submit', function (e) {
      var wajib = kanvas.getAttribute('data-wajib') === '1';
      if (pad.isEmpty()) {
        keluaran.value = '';
        if (wajib) {
          e.preventDefault();
          document.getElementById('galat-ttd').textContent = 'Silakan bubuhkan tanda tangan pada kotak di atas.';
          kotak.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        return;
      }
      keluaran.value = pad.toDataURL('image/png');
    });
  }

  /* ---------- Pencarian peserta terdaftar ---------- */
  var cari = document.getElementById('nama');
  var saran = document.getElementById('saran-peserta');
  var idPeserta = document.getElementById('id_peserta');
  var urlCari = form.getAttribute('data-url-cari');
  var tunda = null;

  function tutupSaran() { saran.hidden = true; saran.innerHTML = ''; }

  function pilih(p) {
    idPeserta.value = p.id;
    cari.value = p.nama;
    document.getElementById('jabatan').value = p.jabatan;
    document.getElementById('instansi').value = p.instansi;
    ['jabatan', 'instansi'].forEach(function (k) { document.getElementById(k).readOnly = true; });
    document.getElementById('terdaftar').hidden = false;
    tutupSaran();
  }

  function lepas() {
    if (!idPeserta.value) return;
    idPeserta.value = '';
    ['jabatan', 'instansi'].forEach(function (k) { document.getElementById(k).readOnly = false; });
    document.getElementById('terdaftar').hidden = true;
  }

  if (cari && saran && urlCari) {
    cari.addEventListener('input', function () {
      lepas();
      clearTimeout(tunda);
      var q = cari.value.trim();
      if (q.length < 2) { tutupSaran(); return; }
      tunda = setTimeout(function () {
        fetch(urlCari + '?q=' + encodeURIComponent(q), { headers: { 'Accept': 'application/json' } })
          .then(function (r) { return r.ok ? r.json() : []; })
          .then(function (daftar) {
            saran.innerHTML = '';
            if (!daftar.length) { tutupSaran(); return; }
            daftar.forEach(function (p) {
              var b = document.createElement('button');
              b.type = 'button';
              var n = document.createElement('b'); n.textContent = p.nama;
              var s = document.createElement('small'); s.textContent = p.jabatan + ' · ' + p.instansi;
              b.appendChild(n); b.appendChild(s);
              b.addEventListener('click', function () { pilih(p); });
              saran.appendChild(b);
            });
            saran.hidden = false;
          })
          .catch(tutupSaran);
      }, 250);
    });
    document.addEventListener('click', function (e) {
      if (!e.target.closest('.posisi-rel')) tutupSaran();
    });
  }

  var lepasTombol = document.getElementById('lepas-peserta');
  if (lepasTombol) lepasTombol.addEventListener('click', function () {
    lepas();
    ['jabatan', 'instansi'].forEach(function (k) { document.getElementById(k).value = ''; });
  });
})();
