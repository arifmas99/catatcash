# PRD — CatatCash (Self-Hosted)

## Status Dokumen

- Versi: v1 — DISETUJUI master
- Tanggal: 30 Oktober 2026
- Status: DISETUJUI MASTER 30 Oktober 2026 ("saya sudah setuju semua"). Belum ada kode yang dibangun dari dokumen ini; langkah berikutnya adalah DESIGN.md.
- Aturan: perubahan scope setelah dokumen ini disetujui harus lewat persetujuan master dulu, lalu dokumen ini diperbarui. Jangan mengubah scope diam-diam saat coding.

## 1. Latar Belakang dan Tujuan

Master saat ini memakai aplikasi "Catat-in: Pembukuan Pake Suara" di HP.
Aplikasi itu berjalan di server pihak lain, sehingga datanya tidak berada
di server master sendiri. Tujuan proyek ini adalah membuat versi master
sendiri yang:

1. Berjalan di server master sendiri (self-hosted), datanya milik master.
2. Gratis penuh, tanpa pembelian dalam aplikasi dan tanpa langganan.
3. Dipakai sehari-hari untuk mencatat pemasukan dan pengeluaran.
4. Sekaligus menjadi proyek belajar pertama workflow vibe coding yang
   rapih: PRD dulu, desain, prototype, tugas kecil, review diff,
   commit per tugas, tes, dokumentasi, deploy staging dulu.

Proyek ini BUKAN tiruan piksel-per-piksel aplikasi asli. Yang dicocokkan
adalah fungsi dan alurnya, berdasarkan tangkapan layar aplikasi asli
yang dikirim master pada 30 Oktober 2026.

## 2. Pengguna

- Satu pengguna saja di v1: master sendiri.
- Tidak ada multi-user, tidak ada pendaftaran akun baru, tidak ada
  hubung QR atau kode undangan seperti aplikasi asli. Aplikasi master
  memang web sejak awal, jadi laporan langsung dibuka dari browser.
- Perangkat utama: browser di laptop (VivoBook) dan HP, lewat server
  lokal dulu (Laragon), lalu DeskMini setelah v1 stabil.

## 3. Gambaran Aplikasi

Aplikasi web pencatat keuangan pribadi dengan:

- Kunci PIN saat masuk, seperti bank digital.
- Beranda yang menampilkan saldo dan ringkasan periode berjalan.
- Dompet: satu atau lebih dompet, tiap transaksi wajib memilih dompet.
- Transaksi manual: pemasukan dan pengeluaran.
- Kategori yang berbeda untuk pemasukan dan pengeluaran.
- Laporan: filter periode, dompet, dan kategori, plus total dan
  laba bersih.

## 4. Fitur v1 (Wajib Jalan Penuh)

### 4.1 Kunci PIN saat masuk

- Saat pertama kali dipakai, master membuat PIN.
- Setiap membuka aplikasi, master diminta memasukkan PIN dulu
  sebelum beranda tampil.
- PIN disimpan dalam bentuk hash, BUKAN angka polos.
- Diputuskan master 30 Oktober 2026: PIN 6 digit.
- Jika PIN salah berulang kali, aplikasi mengunci sementara
  (detail jumlah percobaan dan durasi kunci ditentukan di DESIGN.md).
- Lupa PIN di v1 ditangani oleh pemilik server lewat perintah lokal
  (bukan lewat email), karena ini aplikasi single-user self-hosted.
  Detail perintahnya ditentukan di DESIGN.md, bukan di PRD ini.

Kriteria selesai: tanpa PIN yang benar, beranda, daftar transaksi,
dan laporan tidak bisa dibuka.

### 4.2 Dompet

- Master bisa menambah dompet baru.
- Isi awal v1: satu dompet (Dompet Utama). Strukturnya sejak awal
  mendukung banyak dompet, jadi menambah dompet nanti tidak perlu
  mengubah struktur database.
- Setiap dompet menampilkan saldonya sendiri.
- Beranda menampilkan total saldo seluruh dompet.
- Saldo dompet DIHITUNG dari transaksi (total pemasukan dikurangi
  total pengeluaran dompet itu), bukan disimpan sebagai angka
  terpisah yang bisa meleset dari transaksi.
- Menghapus dompet yang masih berisi transaksi TIDAK diizinkan di v1.
  Master harus memindahkan atau menghapus transaksinya dulu
  (aturan pastinya ditentukan di DESIGN.md).

Kriteria selesai: tambah dompet berhasil, saldo per dompet benar,
total saldo sama dengan jumlah saldo semua dompet.

### 4.3 Transaksi manual

Form "Catat Transaksi" berisi:

- Jenis: Pemasukan atau Pengeluaran (pilihan, wajib).
- Tanggal transaksi: default hari ini, bisa diubah (wajib).
- Nominal: angka rupiah, harus lebih dari nol (wajib).
- Pilih Dompet: wajib, default dompet pertama master.
- Kategori: wajib, daftarnya tergantung jenis (lihat 4.4).
- Catatan: opsional, teks bebas.

Perilaku:

- Transaksi bisa ditambah, diubah (edit), dan dihapus.
- Daftar transaksi menampilkan: kategori, catatan, tanggal dan jam,
  serta nominal dengan tanda tambah untuk pemasukan dan minus
  untuk pengeluaran.
- Daftar terbaru tampil di Beranda, dan daftar lengkap bisa dibuka
  dari Beranda ("Lihat Semua" di aplikasi asli).

Kriteria selesai: tambah, edit, dan hapus transaksi semuanya
mengubah saldo dompet dan total saldo dengan benar.

### 4.4 Kategori

- Kategori dipisah per jenis. Kategori pemasukan tidak muncul
  saat mencatat pengeluaran, dan sebaliknya.
- Master bisa menambah kategori sendiri (nama kategori, jenisnya).
- Daftar awal v1, diambil dari aplikasi asli:

  Pemasukan:
  - Gaji & Bonus
  - Modal Masuk
  - Penjualan
  - Uang Saku
  - Lainnya

  Pengeluaran:
  - Belanja & Hiburan
  - Belanja Barang/Jasa
  - Gaji & Upah
  - Makan & Minum
  - Sosial
  - Lainnya

- Diputuskan master 30 Oktober 2026: "Lain-lain" dan "Lainnya"
  DIGABUNG menjadi satu kategori. Aplikasi asli menampilkan
  keduanya sebagai dua entri terpisah; di CatatCash hanya ada
  satu. Label gabungannya ditulis "Lainnya" di daftar ini.
  Jika master mau labelnya "Lain-lain", koreksi di review PRD.

Kriteria selesai: kategori pemasukan hanya tampil untuk pemasukan,
kategori pengeluaran hanya untuk pengeluaran, tambah kategori
berhasil dan langsung bisa dipakai di form.

### 4.5 Beranda

Beranda menampilkan:

- Kartu total saldo seluruh dompet.
- Total pemasukan dan total pengeluaran periode berjalan
  (periode default: bulan berjalan).
- Daftar Dompet Saya dengan saldo per dompet, plus Kelola Dompet.
- Daftar Transaksi Terbaru.

Yang TIDAK tampil sebagai fitur jalan di v1: Pantauan Anggaran,
Target Tabungan. Keduanya adalah fitur v2, jadi jangan dibuat
setengah jalan di v1. Diputuskan master 30 Oktober 2026:
kotaknya BOLEH ditampilkan di Beranda v1 sebagai "segera hadir"
yang tidak bisa diklik menjadi fitur, bukan disembunyikan.

Kriteria selesai: semua angka di Beranda sama dengan hasil
perhitungan dari transaksi yang tersimpan.

### 4.6 Laporan

Halaman Laporan menampilkan:

- Filter periode: Harian, Mingguan, Bulanan, Custom
  (rentang tanggal yang dipilih master).
- Filter dompet: Semua dompet atau satu dompet tertentu.
- Filter kategori: dipisah pemasukan dan pengeluaran,
  plus pilihan Semua.
- Grafik Tren Alur Kas sederhana: pemasukan vs pengeluaran
  pada periode yang dipilih.
- Total pemasukan, total pengeluaran, dan Laba Bersih
  (pemasukan dikurangi pengeluaran) pada periode yang dipilih.
- Keadaan kosong yang jelas: jika tidak ada data pada periode
  itu, tampilkan pesan bahwa belum ada data, bukan grafik kosong
  tanpa penjelasan.

Kriteria selesai: mengubah filter periode, dompet, atau kategori
mengubah total dan grafik dengan benar; periode tanpa transaksi
menampilkan pesan kosong.

## 5. Alur Utama (User Flow)

1. Buka aplikasi -> layar PIN -> PIN benar -> Beranda.
2. Dari Beranda, ketuk tambah transaksi -> isi jenis, tanggal,
   nominal, dompet, kategori, catatan opsional -> Simpan ->
   kembali ke Beranda, saldo dan daftar terbaru berubah.
3. Dari Beranda, buka daftar transaksi lengkap -> pilih satu
   transaksi -> edit atau hapus -> saldo berubah.
4. Dari Beranda atau navigasi, buka Laporan -> pilih periode,
   dompet, kategori -> total dan grafik berubah.
5. Dari Beranda, buka Kelola Dompet -> tambah dompet baru ->
   dompet muncul di Beranda dan di form transaksi.

## 6. Data (Garis Besar Saja, Detail di DESIGN.md)

- Dompet: nama dompet. Saldo dihitung, bukan disimpan.
- Kategori: nama kategori, jenis (pemasukan atau pengeluaran).
- Transaksi: jenis, tanggal, nominal, dompet, kategori, catatan.
- PIN: hash PIN, bukan PIN polos.

Aturan data yang mengikat:

- Nominal selalu disimpan sebagai angka bulat rupiah, tanpa koma
  desimal, dan harus lebih dari nol.
- Tanggal transaksi adalah tanggal yang dipilih master, bukan
  otomatis tanggal simpan, karena transaksi bisa dicatat belakangan.
- Menghapus kategori yang masih dipakai transaksi TIDAK diizinkan
  di v1, atau kategori itu tetap tampil di transaksi lama
  (diputuskan di DESIGN.md, jangan ditebak saat coding).
- Tidak ada data contoh palsu yang ikut terkirim sebagai data
  master. Data uji hanya ada di lingkungan staging lokal master.

## 7. Tampilan (Acuan, Bukan Desain Final)

- Acuan visual: aplikasi asli (kartu saldo ungu, hijau untuk
  pemasukan, merah untuk pengeluaran, kartu putih membulat).
- Desain final, termasuk token warna, jarak, dan komponen,
  ditentukan di DESIGN.md dan prototype, BUKAN di PRD ini.
- Bahasa tampilan: Bahasa Indonesia.
- Dokumen proyek: Bahasa Indonesia. Identifier kode: Bahasa Inggris.

## 8. Batasan dan Aturan Proyek

- Stack: Laravel, dibangun lokal dulu (Laragon di VivoBook).
  JANGAN langsung deploy ke VPS produksi atau server lain
  sebelum v1 lulus pengujian.
- Single-user. Tidak ada pendaftaran, tidak ada multi-user di v1.
- Tidak ada koneksi ke layanan pihak ketiga di v1.
- Tidak ada pembelian dalam aplikasi, tidak ada langganan.
- Kode rahasia (.env, kunci, token) tidak pernah dikirim lewat chat
  dan tidak pernah di-commit ke Git. Lihat .clineignore.
- Setiap tugas dikerjakan satu per satu, diff dibaca master,
  commit per tugas. Jika AI gagal dua kali pada tugas yang sama,
  berhenti, perkecil tugasnya, atau kerjakan bagian itu manual.
  Jangan menumpuk perbaikan di atas hasil yang berantakan.

## 9. Yang SENGAJA Tidak Masuk v1

### v2 (setelah v1 jalan penuh dan diuji)

- Input suara: ucapkan barang plus nominal, diubah menjadi
  transaksi oleh AI. Termasuk nominal ucapan sehari-hari
  seperti ribu, juta, dan setengah juta.
- Lampiran foto nota pada transaksi manual.
- Scan Nota: foto struk dibaca menjadi transaksi.
- Transfer antar dompet.
- Transaksi Rutin / Ulangi (transaksi berulang otomatis).
- Pantauan Anggaran (anggaran per kategori).
- Target Tabungan.
- Export PDF dan Excel, serta Bagikan Laporan.
- Bot Telegram: catat transaksi lewat chat Telegram
  (teks atau foto struk menjadi transaksi otomatis).
  Diputuskan master 30 Oktober 2026: DIPERTAHANKAN untuk v2.
  Telegram BUKAN v1.

### Fase lanjutan (setelah v2, bukan prioritas sekarang)

- Reality Check: Insight plus Kalkulator Realita
  (Keuangan, Waktu & Hidup, Kebiasaan). Master belum pernah
  memakainya di aplikasi asli, tapi explicitly minta tetap
  diadakan untuk dicoba nanti. Jadi tidak dibuang, tapi juga
  tidak dibangun di v1 atau v2.
- Asisten AI gaya Pro aplikasi asli. Di versi master, peran
  asisten ini digantikan pembagian kerja: Argon untuk desain
  dan review, Cline untuk eksekusi harian, Antigravity untuk
  tugas paralel. Bukan fitur aplikasi v1.

## 10. Kriteria Selesai v1 (Definisi "v1 Jalan")

v1 dinyatakan selesai HANYA jika semua ini benar:

1. PIN mengunci aplikasi, dan tanpa PIN tidak ada data terlihat.
2. Tambah, edit, hapus transaksi bekerja dan saldo selalu benar.
3. Dompet bisa ditambah, saldo per dompet dan total benar.
4. Kategori per jenis bekerja, tambah kategori bekerja.
5. Beranda menampilkan angka yang sama dengan perhitungan transaksi.
6. Laporan dengan semua filter menampilkan total dan grafik yang benar.
7. Periode tanpa data menampilkan pesan kosong yang jelas.
8. Tidak ada fitur v2 yang setengah jalan dan membingungkan.
9. Semua pengujian di TESTING.md lulus, termasuk data kosong,
   input salah, dan alur penuh dari PIN sampai laporan.

## 11. Pertanyaan Terbuka — SUDAH DIJAWAB MASTER 30 Oktober 2026

1. PIN: 6 digit. (lihat 4.1)
2. Kategori "Lain-lain" dan "Lainnya": DIGABUNG menjadi satu.
   Label gabungannya ditulis "Lainnya"; koreksi jika master
   mau labelnya "Lain-lain". (lihat 4.4)
3. Bot Telegram: DIPERTAHANKAN untuk v2. (lihat 9)
4. Kotak Pantauan Anggaran dan Target Tabungan di Beranda v1:
   BOLEH ditampilkan sebagai "segera hadir" yang tidak bisa
   diklik menjadi fitur. (lihat 4.5)

PRD ini DISETUJUI PENUH oleh master pada 30 Oktober 2026.
Langkah berikutnya adalah Tahap 2: DESIGN.md, lalu Tahap 3:
prototype, lalu TASKS.md. Tidak ada kode aplikasi yang dibangun
sebelum DESIGN.md juga disetujui.
