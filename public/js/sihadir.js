/* SIHADIR — perilaku antarmuka bersama */
(function () {
  'use strict';

  var kurangiGerak = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ---------- Animasi goyang saat diklik ---------- */
  var SASARAN = '.btn, .stat, .fitur, .card-sel, .menu-atas a.tautan, .sisi nav a, .merek, .langkah li, .daftar-ringkas li a, .pengguna-kartu, [data-goyang]';

  function goyangkan(el) {
    el.classList.remove('goyang');
    void el.offsetWidth; // memulai ulang animasi
    el.classList.add('goyang');
    el.addEventListener('animationend', function h() {
      el.classList.remove('goyang');
      el.removeEventListener('animationend', h);
    });
  }

  document.addEventListener('click', function (e) {
    var el = e.target.closest(SASARAN);
    if (!el || el.disabled) return;

    goyangkan(el);

    // Tautan pindah halaman: tunda sebentar agar goyangannya terlihat.
    var a = el.tagName === 'A' ? el : el.closest('a');
    if (!a || kurangiGerak) return;

    var href = a.getAttribute('href');
    if (!href || href.charAt(0) === '#' || a.target === '_blank' || a.hasAttribute('download') ||
        a.hasAttribute('data-tanpa-tunda') || e.ctrlKey || e.metaKey || e.shiftKey || e.button !== 0 ||
        /^(mailto:|tel:|javascript:)/i.test(href)) return;

    e.preventDefault();
    setTimeout(function () { window.location.href = a.href; }, 320);
  });

  /* ---------- Menu mobile halaman depan ---------- */
  var tombolMenu = document.querySelector('.tombol-menu');
  var menuAtas = document.querySelector('.menu-atas');
  if (tombolMenu && menuAtas) {
    tombolMenu.addEventListener('click', function () { menuAtas.classList.toggle('buka'); });
    menuAtas.addEventListener('click', function (e) {
      if (e.target.closest('a')) menuAtas.classList.remove('buka');
    });
  }

  /* ---------- Sidebar panel mobile ---------- */
  var sisi = document.querySelector('.sisi');
  var gelap = document.querySelector('.latar-gelap');
  function toggleSisi(buka) {
    if (!sisi) return;
    sisi.classList.toggle('buka', buka);
    if (gelap) gelap.classList.toggle('buka', buka);
  }
  document.querySelectorAll('.tombol-sisi').forEach(function (b) {
    b.addEventListener('click', function () { toggleSisi(!sisi.classList.contains('buka')); });
  });
  if (gelap) gelap.addEventListener('click', function () { toggleSisi(false); });

  /* ---------- Lihat / sembunyikan kata sandi ---------- */
  document.querySelectorAll('[data-lihat-sandi]').forEach(function (b) {
    b.addEventListener('click', function () {
      var input = document.getElementById(b.getAttribute('data-lihat-sandi'));
      if (!input) return;
      input.type = input.type === 'password' ? 'text' : 'password';
    });
  });

  /* ---------- Konfirmasi sebelum aksi berisiko ---------- */
  document.addEventListener('submit', function (e) {
    var f = e.target;
    var pesan = f.getAttribute && f.getAttribute('data-konfirmasi');
    if (pesan && !window.confirm(pesan)) e.preventDefault();
  });

  /* ---------- Salin teks ---------- */
  document.querySelectorAll('[data-salin]').forEach(function (b) {
    b.addEventListener('click', function () {
      var sumber = document.querySelector(b.getAttribute('data-salin'));
      if (!sumber) return;
      var teks = sumber.value || sumber.textContent;
      var selesai = function () {
        var asli = b.innerHTML;
        b.textContent = 'Tersalin ✓';
        setTimeout(function () { b.innerHTML = asli; }, 1600);
      };
      if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(teks).then(selesai);
      } else {
        sumber.select && sumber.select();
        try { document.execCommand('copy'); selesai(); } catch (err) { /* abaikan */ }
      }
    });
  });

  /* ---------- QR Code (pustaka qrcode-generator) ---------- */
  function buatQrSvg(teks) {
    var qr = window.qrcode(0, 'M');
    qr.addData(teks);
    qr.make();
    return qr.createSvgTag({ cellSize: 8, margin: 16, scalable: true }).replace('fill="black"', 'fill="#0A3B39"');
  }
  window.SIHADIR_QR = buatQrSvg;

  document.querySelectorAll('[data-qr]').forEach(function (el) {
    if (!window.qrcode) return;
    el.innerHTML = buatQrSvg(el.getAttribute('data-qr'));
  });

  document.querySelectorAll('[data-unduh-qr]').forEach(function (b) {
    b.addEventListener('click', function () {
      var teks = b.getAttribute('data-unduh-qr');
      var nama = b.getAttribute('data-nama') || 'qr-presensi';
      var svg = buatQrSvg(teks);
      var img = new Image();
      var url = URL.createObjectURL(new Blob([svg], { type: 'image/svg+xml' }));
      img.onload = function () {
        var c = document.createElement('canvas');
        c.width = c.height = 1024;
        var ctx = c.getContext('2d');
        ctx.fillStyle = '#fff';
        ctx.fillRect(0, 0, 1024, 1024);
        ctx.drawImage(img, 0, 0, 1024, 1024);
        c.toBlob(function (blob) {
          var a = document.createElement('a');
          a.href = URL.createObjectURL(blob);
          a.download = nama + '.png';
          a.click();
          URL.revokeObjectURL(url);
        }, 'image/png');
      };
      img.src = url;
    });
  });

  /* ---------- Tombol layar penuh ---------- */
  document.querySelectorAll('[data-layar-penuh]').forEach(function (b) {
    b.addEventListener('click', function () {
      if (document.fullscreenElement) document.exitFullscreen();
      else document.documentElement.requestFullscreen && document.documentElement.requestFullscreen();
    });
  });
})();
