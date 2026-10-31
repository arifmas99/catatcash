# TASKS.md — Daftar Tugas Kecil CatatCash v1

## Status

- PRD.md: DISETUJUI PENUH master 30 Oktober 2026.
- DESIGN.md: DISETUJUI FINAL master 31 Oktober 2026, ditambah Amandemen Layout Bab 13 pada 1 November 2026.
- Prototype: DISETUJUI FINAL master 1 November 2026, versi Argon di `prototype/argon/index.html`.
- Daftar tugas ini: DIISI 1 November 2026, MENUNGGU REVIEW master. Kode Laravel BELUM ditulis.
- Acuan tampilan setiap tugas: prototype Argon, bukan prototype Wing. Acuan teknis setiap tugas: DESIGN.md. Jika ada konflik, DESIGN.md menang, dan konflik itu dilaporkan ke master, bukan ditebak.

## Aturan Tugas (dari AGENTS.md)

- Satu tugas = satu perubahan kecil yang bisa dites sendiri.
- Urutan fondasi dulu, fitur di atas fondasi. Jangan melompati urutan tanpa persetujuan master.
- Setiap tugas: Cline mengerjakan HANYA tugas itu, master membaca diff, master menjalankan dan mengetes, lalu commit Git per tugas dengan pesan Bahasa Indonesia yang jelas.
- Jangan pernah auto-approve edit. Jangan mengubah scope diam-diam.
- Saldo selalu dihitung dari transaksi, tidak pernah disimpan sebagai angka terpisah.
- Nominal selalu integer rupiah, harus lebih dari nol. Validasi di server, bukan hanya di browser.
- PIN disimpan sebagai hash, tidak pernah sebagai angka polos.
- Jika satu tugas ternyata terlalu besar, pecah lagi, jangan dipaksakan.
- Jika AI gagal dua kali pada tugas yang sama: berhenti, laporkan ke master, jangan mencoba cara ketiga diam-diam.
- Jangan menambahkan fitur v2 (suara, lampiran/scan nota, transfer, rutin, anggaran, target tabungan, export, bagikan, Telegram) dan jangan menambahkan Reality Check.
- Jangan menyentuh VPS produksi Inlislite dari proyek ini. Proyek ini lokal dulu (Laragon di VivoBook).

## Daftar Tugas

### Tugas 1 — Setup proyek Laravel 13 + Git [DIMULAI 1 November 2026 — langkah pertama: cek PHP Laragon, menunggu laporan master]
- Cek dulu PHP di Laragon master sudah 8.3 atau lebih tinggi (syarat Laravel 13; PHP 8.2 tidak didukung).
- Buat proyek Laravel 13.x terbaru yang stabil, kunci satu set versi yang kompatibel untuk PHP, Tailwind, Vite, Alpine, dan Chart.js, catat versi terkunci itu di sini setelah instalasi.
- Buat repository Git lokal, pasang `.gitignore` dan `.clineignore` dari folder proyek ini.
- Hasil yang harus terlihat: halaman bawaan Laravel bisa dibuka di browser Laragon, tanpa fitur CatatCash apa pun dulu.
- Tes master: buka proyek di browser, halaman Laravel tampil, `php -v` menunjukkan 8.3 ke atas.
- Commit: `Setup proyek Laravel 13 dan Git`.

### Tugas 2 — Database, model, dan seeder [BELUM]
- Buat migrasi persis DESIGN Bab 3: `app_settings` (pin_hash, failed_attempts, locked_until), `wallets` (name unik, is_primary, tanpa kolom saldo), `categories` (name, type income/expense, unik per jenis), `transactions` (wallet_id, category_id, type, amount, transaction_date, transaction_time, notes).
- Buat model dan relasi, foreign key dengan ON DELETE RESTRICT.
- Buat seeder: 1 dompet "Dompet Utama" (is_primary true) dan kategori awal sesuai PRD (pemasukan: Gaji & Bonus, Modal Masuk, Penjualan, Uang Saku, Lainnya; pengeluaran: Belanja & Hiburan, Belanja Barang/Jasa, Gaji & Upah, Makan & Minum, Sosial, Lainnya).
- Hasil yang harus terlihat: migrasi jalan tanpa error, tabel terisi data awal, tidak ada halaman baru yang wajib dites master.
- Tes master: jalankan migrasi dan seeder, cek tabel dan isi awalnya di database.
- Commit: `Tambah database, model, dan seeder awal`.

### Tugas 3 — Kunci PIN: buat, buka, kunci [BELUM]
- Buat middleware dan halaman sesuai DESIGN Bab 5 dan 6: halaman Buat PIN 6 digit (hanya saat pin_hash masih kosong), halaman Masukkan PIN dengan keypad 6 bulatan, tombol Kunci Aplikasi.
- PIN disimpan sebagai hash. Sesi terkunci saat browser ditutup, bisa dikunci manual, dan terkunci otomatis setelah 15 menit diam.
- Lockout: 5 kali salah berturut-turut, kunci 5 menit, hitungan disimpan di database bukan di cookie.
- Perintah reset: `php artisan catatcash:reset-pin` dengan konfirmasi terminal, mengosongkan PIN dan membersihkan sesi.
- Hasil yang harus terlihat: pertama kali buka aplikasi diminta buat PIN, buka berikutnya diminta PIN, PIN salah menampilkan sisa percobaan.
- Tes master: buat PIN, tutup browser dan buka lagi (diminta PIN lagi), coba salah 5 kali (terkunci 5 menit), coba perintah reset di terminal.
- Commit: `Tambah kunci PIN, lockout, dan reset PIN`.

### Tugas 4 — Kerangka tampilan: token, layout mobile, layout desktop [BELUM]
- Terapkan token DESIGN Bab 7 (ungu, hijau, merah, kartu putih membulat, latar abu muda) dan komponen DESIGN Bab 8 yang bisa dipakai ulang.
- Mobile: satu kolom, navigasi bawah (Beranda, Transaksi, tombol + Catat, Dompet & Kategori, Laporan).
- Desktop (1024px ke atas): sidebar kiri tetap, navigasi bawah disembunyikan, area konten grid multi-kolom (DESIGN Bab 13).
- Hasil yang harus terlihat: kerangka halaman kosong dengan sidebar di laptop dan navigasi bawah di HP, tanpa data apa pun dulu.
- Tes master: buka di laptop (sidebar tampil) dan di HP (navigasi bawah tampil).
- Commit: `Tambah kerangka tampilan mobile dan desktop`.

### Tugas 5 — Beranda [BELUM]
- Kartu Total Saldo Semua Dompet (dihitung dari transaksi), ringkasan Pemasukan dan Pengeluaran bulan ini, daftar Dompet Saya, kotak Pantauan Anggaran dan Target Tabungan sebagai Segera Hadir yang tidak bisa diklik (elemen div statis, bukan tautan atau tombol), dan Transaksi Terbaru 5 baris dengan tombol Lihat Semua.
- Saldo dihitung lewat service perhitungan, bukan kolom saldo.
- Hasil yang harus terlihat: Beranda menampilkan saldo contoh dari data seeder dan transaksi percobaan, dengan 5 baris terbaru saja.
- Tes master: tambah transaksi percobaan lewat database atau tugas berikutnya, cek saldo dan 5 baris terbaru berubah dengan benar.
- Commit: `Tambah halaman Beranda`.

### Tugas 6 — Kelola Dompet [BELUM]
- Daftar dompet beserta saldo per dompet (dihitung), tambah dompet, ubah nama dompet, hapus dompet.
- Aturan hapus DESIGN Bab 4: hapus ditolak kalau dompet masih punya transaksi (>0), dompet terakhir tidak boleh dihapus, dompet utama yang dihapus diganti otomatis ke dompet paling awal lainnya.
- Hasil yang harus terlihat: master bisa menambah, mengubah nama, dan menghapus dompet kosong; penghapusan dompet berisi transaksi ditolak dengan pesan yang jelas.
- Tes master: tambah dompet baru, ubah namanya, coba hapus dompet yang masih punya transaksi (harus ditolak), hapus dompet kosong (harus berhasil).
- Commit: `Tambah kelola dompet`.

### Tugas 7 — Kelola Kategori [BELUM]
- Daftar kategori terpisah per jenis (tab Pemasukan dan Pengeluaran), tambah kategori, ubah nama kategori, hapus kategori.
- Aturan hapus: hapus ditolak kalau kategori masih dipakai transaksi (>0); kategori 0 transaksi boleh dihapus. Nama boleh sama di dua jenis berbeda, tidak boleh duplikat dalam jenis yang sama.
- Hasil yang harus terlihat: kategori awal dari seeder tampil per jenis, master bisa menambah kategori sendiri.
- Tes master: tambah kategori baru per jenis, coba duplikat dalam jenis yang sama (harus ditolak), coba hapus kategori yang masih dipakai (harus ditolak).
- Commit: `Tambah kelola kategori`.

### Tugas 8 — Transaksi: tambah, ubah, hapus, daftar lengkap [BELUM]
- Form Catat Transaksi: toggle Pemasukan/Pengeluaran yang mengubah daftar kategori, tanggal (default hari ini), jam (default waktu saat ini, bisa diubah), nominal, pilih dompet, kategori, catatan opsional.
- Daftar lengkap transaksi dengan ikon kategori, nama dompet, tanggal dan jam, nominal bertanda plus hijau atau minus merah, tombol Edit dan Hapus per baris.
- Validasi server: nominal integer >0, jenis transaksi harus sama dengan jenis kategorinya, dompet dan kategori wajib ada.
- Hasil yang harus terlihat: master bisa mencatat, mengubah, dan menghapus transaksi, dan saldo Beranda berubah mengikuti transaksi itu.
- Tes master: catat pemasukan dan pengeluaran, ubah salah satunya, hapus salah satunya, cek saldo dan daftar berubah dengan benar.
- Commit: `Tambah transaksi manual`.

### Tugas 9 — Laporan: filter, total, dan Laba Bersih [BELUM]
- Filter periode Harian, Mingguan (Senin sampai Minggu), Bulanan (default), dan Custom (start_date harus <= end_date), filter dompet (Semua Dompet atau satu dompet), filter kategori (Semua Kategori, semua pemasukan, semua pengeluaran, atau satu kategori).
- Tampilkan Total Pemasukan, Total Pengeluaran, dan Laba Bersih untuk kombinasi filter yang aktif, serta daftar transaksi yang cocok. Jika tidak ada data, tampilkan ringkasan Rp 0 dan pesan kosong yang jelas, bukan halaman error.
- Hasil yang harus terlihat: mengubah filter mengubah total dan daftar dengan benar.
- Tes master: coba tiap periode, tiap dompet, dan tiap kelompok kategori, lalu coba filter yang hasilnya kosong.
- Commit: `Tambah halaman laporan`.

### Tugas 10 — Grafik Tren Alur Kas [BELUM]
- Grafik dari data laporan yang sama: pemasukan hijau, pengeluaran merah, titik sumbu X sesuai periode (harian 1 titik, mingguan 7 hari, bulanan N hari, custom dikelompokkan per hari/minggu/bulan sesuai rentang).
- Tombol pemilih jenis grafik: Otomatis (Batang untuk Harian/Mingguan, Garis untuk Bulanan), Garis, dan Batang Berdampingan. Pilihan hanya mengubah tampilan, bukan data.
- Jika tidak ada data, area grafik menampilkan pesan kosong, bukan kanvas kosong.
- Hasil yang harus terlihat: grafik berubah mengikuti filter dan tombol jenis grafik.
- Tes master: coba tiap periode dan tiap tombol jenis grafik, lalu coba filter kosong.
- Commit: `Tambah grafik Tren Alur Kas`.

### Tugas 11 — Pengujian penuh dan rapikan dokumentasi [BELUM]
- Jalankan checklist TESTING.md dari awal sampai akhir: PIN, Beranda, dompet, kategori, transaksi, laporan, grafik, di HP dan di laptop.
- Perbaiki hanya bug yang ditemukan pengujian ini, satu per satu, tanpa menambah fitur baru.
- Perbarui README.md proyek dengan status akhir v1 dan cara menjalankan di Laragon.
- Hasil yang harus terlihat: semua checklist lulus, tidak ada fitur v2 yang ikut masuk.
- Tes master: jalankan ulang checklist bersama Wing, nyatakan v1 lulus atau sebutkan bug yang masih tersisa.
- Commit: `Rapikan dokumentasi setelah pengujian v1`.

## Yang BUKAN Tugas v1

- Suara, lampiran foto nota, scan nota, transfer antar dompet, transaksi rutin, fungsi anggaran, fungsi target tabungan, export PDF/Excel, bagikan laporan, bot Telegram, Reality Check, versi Android, publikasi Play Store.
- Semua itu tetap di v2 atau fase lanjutan, dan hanya masuk daftar tugas setelah master menyetujui scope barunya.
