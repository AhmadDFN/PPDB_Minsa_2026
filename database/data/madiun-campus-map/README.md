# Peta offline Kampus 1 MIN 1 Kota Madiun

Dataset ini adalah potongan data jalan dan bangunan OpenStreetMap untuk area sekitar Kampus 1, dikemas agar pemilih lokasi di form PPDB dapat bekerja tanpa tile map atau geocoding pihak ketiga.

- Sumber: [OpenStreetMap API map endpoint](https://api.openstreetmap.org/api/0.6/map?bbox=111.510,-7.665,111.519,-7.653)
- Cakupan: 111.510°–111.519° BT, 7.665°–7.653° LS.
- Diambil: 26 November 2026.
- Berkas sumber: `source.osm`.
- Berkas siap pakai: `public/maps/min1-madiun-campus.json`, dibuat oleh `scripts/build-offline-campus-map.php`.
- Lisensi data: Open Database License (ODbL); atribusi terlihat pada widget peta. Lihat [lisensi dan atribusi OpenStreetMap](https://www.openstreetmap.org/copyright).

Pembaruan dataset: ambil potongan bbox kecil yang sama dari OpenStreetMap API sesuai kebijakan API, simpan sebagai `source.osm`, lalu jalankan `php scripts/build-offline-campus-map.php`. Jangan mengirim koordinat calon siswa ke endpoint peta. Aplikasi memuat JSON statis dari origin sendiri; koordinat terpilih hanya dipakai untuk menghitung jarak dan tidak disimpan.
