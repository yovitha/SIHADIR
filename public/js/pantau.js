/* Halaman pantau kehadiran: perbarui daftar secara berkala (KF-10) */
(function () {
  'use strict';

  var panel = document.getElementById('pantau');
  if (!panel) return;

  var url = panel.getAttribute('data-url');
  var badan = document.getElementById('badan-kehadiran');
  var angka = document.getElementById('angka-hadir');
  var penanda = document.getElementById('penanda');
  var statusEl = document.getElementById('status-kegiatan');
  var otomatis = true;
  var pewaktu = null;

  function muat() {
    return fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
      .then(function (r) { if (!r.ok) throw new Error(r.status); return r.json(); })
      .then(function (d) {
        badan.innerHTML = d.html;
        angka.textContent = d.hadir;
        if (statusEl) statusEl.textContent = d.status;
        penanda.classList.remove('mati');
      })
      .catch(function () { penanda.classList.add('mati'); });
  }

  function mulai() {
    clearInterval(pewaktu);
    pewaktu = setInterval(function () { if (otomatis && !document.hidden) muat(); }, 5000);
  }

  var saklar = document.getElementById('saklar-otomatis');
  if (saklar) saklar.addEventListener('change', function () {
    otomatis = saklar.checked;
    penanda.classList.toggle('mati', !otomatis);
    penanda.querySelector('span').textContent = otomatis ? 'Pembaruan otomatis' : 'Dijeda';
  });

  var muatUlang = document.getElementById('muat-ulang');
  if (muatUlang) muatUlang.addEventListener('click', muat);

  mulai();
})();
