# DESIGN.md — CatatCash (Self-Hosted) v1

## Status Dokumen

- **Sumber**: Disusun oleh Gemini Argon (aplikasi Gemini, tingkat penalaran Tinggi) pada 30 Oktober 2026, dari PRD CatatCash yang sudah disetujui. File ini adalah hasil Argon yang dirapikan Wing ke dalam folder proyek tanpa mengubah isi teknisnya.
- **Versi**: v1.0 (Desain Teknis — DISETUJUI FINAL Master pada 31 Oktober 2026, ditambah Amandemen Layout Bab 13 pada 1 November 2026)
- **Keputusan Master 31 Oktober 2026**: 7 pertanyaan Bab 11 SUDAH DIJAWAB master dan dimasukkan ke dalam dokumen ini sebagai Bab 12 (Amandemen). Versi yang sudah diamandemen ini DISETUJUI FINAL oleh master pada 31 Oktober 2026 ("ok aku setuju"). Dokumen ini terkunci sebagai acuan implementasi; perubahan scope setelah ini lewat persetujuan master dan dicatat sebagai amandemen baru.
- **Acuan Dokumen**: `PRD.md` (Disetujui Master pada 30 Oktober 2026)
- **Lingkungan Target Awal**: Laragon di Laptop Windows (VivoBook), sebelum nantinya dipindah ke server lokal DeskMini setelah v1 lulus uji.
- **Aturan Ketat**:
  - Dokumen ini hanya mendefinisikan desain teknis **Fitur v1**.
  - Tidak ada kode aplikasi yang ditulis sebelum dokumen ini disetujui oleh Master.
  - Bahasa dokumen: **Bahasa Indonesia**.
  - Identifier kode (tabel, kolom, route, class, variabel): **Bahasa Inggris**.
  - Label antarmuka pengguna (UI): **Bahasa Indonesia**.

---

## 1. Versi Stack Teknologi yang Disarankan

Seluruh komponen dipilih agar dapat berjalan **100% lokal (offline-ready)** di lingkungan Laragon tanpa ketergantungan layanan pihak ketiga maupun CDN eksternal saat aplikasi dijalankan:

| Lapisan | Teknologi | Versi yang Disarankan | Alasan Pemilihan |
| :--- | :--- | :--- | :--- |
| **Runtime Server** | PHP | `8.3.x`–`8.5.x` | Syarat Laravel 13; PHP 8.2 sudah tidak didukung Laravel 13, jadi jangan pakai PHP 8.2. |
| **Framework Backend** | Laravel | `13.x` terbaru yang stabil saat implementasi dimulai (per 27 Oktober 2026: 13.39.0) | Diputuskan master 31 Oktober 2026: pakai Laravel terbaru, bukan 11.x/12.x. Laravel 12 sudah lewat masa perbaikan bug (berakhir 13 Agustus 2026, hanya perbaikan keamanan sampai 24 Februari 2027); Laravel 13 mendapat perbaikan bug sampai Q3 2027 dan keamanan sampai 17 Maret 2028. |
| **Database** | MySQL / MariaDB | MySQL `8.0+` / MariaDB `10.11+` | Bawaan langsung Laragon, mendukung penuh *foreign key constraint* (`ON DELETE RESTRICT`) dan tipe integer besar. |
| **Templating UI** | Laravel Blade | Bawaan Laravel | Cepat, dirender di sisi server, mudah dipelihara untuk proyek *single-user*. |
| **Styling CSS** | Tailwind CSS | Versi terakhir yang kompatibel dengan Laravel 13 terpilih (bukan dipatok 3.4.x) | Memudahkan penerapan token desain (kartu ungu, indikator hijau/merah) secara konsisten dan responsif di layar HP maupun laptop. Kunci satu set versi yang kompatibel saat instalasi, jangan campur Tailwind/Vite dari generasi Laravel lama. |
| **Interaktivitas UI** | Alpine.js | `3.x` (dibundel via NPM/Vite) | Sangat ringan untuk interaksi lokal seperti numpad PIN, pemilihan tab Pemasukan/Pengeluaran, dan filter kategori dinamis tanpa reload penuh. |
| **Grafik Laporan** | Chart.js | `4.x` (dibundel via NPM/Vite) | Gratis penuh, berjalan di browser tanpa koneksi internet, ringan untuk merender grafik **Tren Alur Kas**. |
| **Build Tool** | Vite | Versi terakhir yang kompatibel dengan Laravel 13 terpilih (bukan dipatok 5.x) | Bawaan standar Laravel untuk membundel aset CSS dan JS secara lokal. |

---

## 2. Struktur Folder Proyek

Struktur proyek mengikuti standar Laravel dengan pemisahan logika perhitungan ke dalam `Services` agar `Controllers` tetap bersih dan mudah diuji:

```text
catatcash/
├── app/
│   ├── Console/
│   │   └── Commands/
│   │       └── ResetPinCommand.php          # Perintah CLI lokal untuk reset PIN saat lupa
│   ├── Enums/
│   │   └── TransactionType.php              # Enum: 'income' dan 'expense'
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── PinController.php            # Setup PIN pertama kali, buka kunci, dan kunci sesi
│   │   │   ├── DashboardController.php      # Halaman Beranda
│   │   │   ├── WalletController.php         # Daftar, tambah, ubah, dan hapus Dompet
│   │   │   ├── CategoryController.php       # Daftar, tambah, ubah, dan hapus Kategori
│   │   │   ├── TransactionController.php    # CRUD Transaksi manual & daftar lengkap
│   │   │   └── ReportController.php         # Halaman Laporan, filter, dan data grafik
│   │   ├── Middleware/
│   │   │   ├── EnsurePinIsConfigured.php    # Cek apakah PIN sudah dibuat saat pertama pakai
│   │   │   └── EnsurePinIsUnlocked.php      # Wajibkan sesi PIN terbuka sebelum akses halaman
│   │   └── Requests/
│   │       ├── SetupPinRequest.php
│   │       ├── UnlockPinRequest.php
│   │       ├── StoreWalletRequest.php
│   │       ├── StoreCategoryRequest.php
│   │       └── UpsertTransactionRequest.php
│   ├── Models/
│   │   ├── AppSetting.php                   # Model konfigurasi keamanan PIN
│   │   ├── Wallet.php                       # Model Dompet
│   │   ├── Category.php                     # Model Kategori
│   │   └── Transaction.php                  # Model Transaksi
│   └── Services/
│       ├── PinSecurityService.php           # Logika verifikasi hash, hitung salah, & lockout
│       ├── BalanceCalculatorService.php     # Perhitungan saldo per dompet & total saldo
│       └── ReportAggregatorService.php      # Perhitungan ringkasan laporan & deret grafik
├── database/
│   ├── migrations/
│   │   ├── 0001_01_01_000001_create_sessions_table.php
│   │   ├── 0001_01_01_000002_create_app_settings_table.php
│   │   ├── 0001_01_01_000003_create_wallets_table.php
│   │   ├── 0001_01_01_000004_create_categories_table.php
│   │   └── 0001_01_01_000005_create_transactions_table.php
│   └── seeders/
│       ├── DatabaseSeeder.php
│       ├── InitialWalletSeeder.php          # Seed 1 dompet: "Dompet Utama"
│       └── InitialCategorySeeder.php        # Seed kategori awal Pemasukan & Pengeluaran
├── resources/
│   ├── css/
│   │   └── app.css                          # Definisi Tailwind & CSS variabel token desain
│   ├── js/
│   │   ├── app.js                           # Inisialisasi Alpine.js
│   │   └── cashflow-chart.js                # Inisialisasi Chart.js untuk halaman Laporan
│   └── views/
│       ├── components/                      # Komponen Blade yang dipakai ulang
│       ├── layouts/
│       │   ├── app.blade.php                # Layout utama aplikasi (setelah PIN terbuka)
│       │   └── pin.blade.php                # Layout khusus layar kunci PIN
│       └── pages/
│           ├── pin/
│           │   ├── setup.blade.php
│           │   └── unlock.blade.php
│           ├── dashboard/
│           │   └── index.blade.php
│           ├── transactions/
│           │   ├── index.blade.php
│           │   ├── create.blade.php
│           │   └── edit.blade.php
│           ├── wallets/
│           │   └── index.blade.php
│           ├── categories/
│           │   └── index.blade.php
│           └── reports/
│               └── index.blade.php
├── routes/
│   └── web.php                              # Definisi seluruh route web v1
└── tests/
    └── Feature/                             # Pengujian otomatis sesuai TESTING.md nantinya
```

---

## 3. Skema Database Lengkap

Database dirancang tanpa menyimpan angka saldo statis. Seluruh saldo merupakan hasil agregasi dari tabel `transactions`.

### 3.1. Tabel `app_settings`
Menyimpan status keamanan PIN untuk aplikasi *single-user*. Tabel ini hanya akan memiliki tepat $1$ baris data (`id = 1`).

| Nama Kolom | Tipe Data | Nullable | Default | Keterangan |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED` | Tidak | Auto Increment | Primary Key |
| `pin_hash` | `VARCHAR(255)` | Ya | `NULL` | Hash dari PIN 6 digit (menggunakan Bcrypt/Argon2id). Bernilai `NULL` hanya sebelum PIN dibuat pertama kali. |
| `failed_attempts` | `TINYINT UNSIGNED` | Tidak | `0` | Jumlah percobaan PIN salah berturut-turut saat ini. |
| `locked_until` | `TIMESTAMP` | Ya | `NULL` | Waktu berakhirnya penguncian sementara jika melampaui batas salah. |
| `created_at` | `TIMESTAMP` | Ya | `NULL` | Waktu pembuatan baris pengaturan. |
| `updated_at` | `TIMESTAMP` | Ya | `NULL` | Waktu pembaruan terakhir. |

### 3.2. Tabel `wallets`
Menyimpan daftar dompet milik master. **Tidak ada kolom saldo** pada tabel ini agar tidak pernah terjadi selisih antara saldo dompet dan riwayat transaksi.

| Nama Kolom | Tipe Data | Nullable | Default | Keterangan |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED` | Tidak | Auto Increment | Primary Key |
| `name` | `VARCHAR(100)` | Tidak | - | Nama dompet (unik, wajib diisi). Contoh: `"Dompet Utama"`. |
| `is_primary` | `BOOLEAN` | Tidak | `false` | Penanda dompet default yang terpilih otomatis di form transaksi. |
| `created_at` | `TIMESTAMP` | Ya | `NULL` | Waktu pembuatan dompet. |
| `updated_at` | `TIMESTAMP` | Ya | `NULL` | Waktu pembaruan dompet. |

- **Constraint**: `UNIQUE (name)`
- **Data Awal (Seeder)**:
  - `name = "Dompet Utama"`, `is_primary = true`

### 3.3. Tabel `categories`
Menyimpan daftar kategori yang dipisahkan secara ketat berdasarkan jenis transaksi (`income` atau `expense`).

| Nama Kolom | Tipe Data | Nullable | Default | Keterangan |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED` | Tidak | Auto Increment | Primary Key |
| `name` | `VARCHAR(100)` | Tidak | - | Nama kategori dalam Bahasa Indonesia. |
| `type` | `ENUM('income', 'expense')` | Tidak | - | Jenis kategori: `'income'` (Pemasukan) atau `'expense'` (Pengeluaran). |
| `created_at` | `TIMESTAMP` | Ya | `NULL` | Waktu pembuatan kategori. |
| `updated_at` | `TIMESTAMP` | Ya | `NULL` | Waktu pembaruan kategori. |

- **Constraint & Indeks**:
  - `UNIQUE (name, type)` — Nama yang sama boleh ada di dua jenis berbeda (contoh: kategori `"Lainnya"` ada satu untuk `income` dan satu untuk `expense`), tetapi tidak boleh duplikat dalam jenis yang sama.
  - `INDEX idx_categories_type (type)`
- **Data Awal (Seeder)** sesuai PRD (dengan `"Lain-lain"` dan `"Lainnya"` telah digabung menjadi satu kategori `"Lainnya"`):
  - **Pemasukan (`income`)**:
    1. `Gaji & Bonus`
    2. `Modal Masuk`
    3. `Penjualan`
    4. `Uang Saku`
    5. `Lainnya`
  - **Pengeluaran (`expense`)**:
    1. `Belanja & Hiburan`
    2. `Belanja Barang/Jasa`
    3. `Gaji & Upah`
    4. `Makan & Minum`
    5. `Sosial`
    6. `Lainnya`

### 3.4. Tabel `transactions`
Menyimpan seluruh catatan pemasukan dan pengeluaran manual.

| Nama Kolom | Tipe Data | Nullable | Default | Keterangan |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED` | Tidak | Auto Increment | Primary Key |
| `wallet_id` | `BIGINT UNSIGNED` | Tidak | - | Foreign Key ke `wallets.id`. |
| `category_id` | `BIGINT UNSIGNED` | Tidak | - | Foreign Key ke `categories.id`. |
| `type` | `ENUM('income', 'expense')` | Tidak | - | Jenis transaksi: `'income'` (Pemasukan) atau `'expense'` (Pengeluaran). |
| `amount` | `BIGINT UNSIGNED` | Tidak | - | Nominal angka bulat Rupiah tanpa desimal. Wajib $> 0$. |
| `transaction_date` | `DATE` | Tidak | - | Tanggal terjadinya transaksi yang dipilih master (default hari ini). |
| `transaction_time` | `TIME` | Tidak | Waktu saat ini | Jam dan menit terjadinya transaksi. Diputuskan master 31 Oktober 2026: form WAJIB menyediakan input jam; defaultnya otomatis mengikuti waktu saat ini, master boleh mengubahnya bila ingin menyesuaikan. Daftar transaksi menampilkan `transaction_date` + `transaction_time`, BUKAN `created_at`. Kolom `created_at` tetap ada sebagai waktu pencatatan sistem, bukan waktu terjadinya transaksi. |
| `notes` | `TEXT` | Ya | `NULL` | Catatan tambahan bebas (opsional). |
| `created_at` | `TIMESTAMP` | Ya | `NULL` | Waktu transaksi dicatat ke sistem (digunakan juga untuk informasi jam pencatatan). |
| `updated_at` | `TIMESTAMP` | Ya | `NULL` | Waktu perubahan terakhir. |

- **Relasi & Aturan Hapus (Foreign Keys)**:
  - `FOREIGN KEY (wallet_id) REFERENCES wallets(id) ON UPDATE CASCADE ON DELETE RESTRICT`
  - `FOREIGN KEY (category_id) REFERENCES categories(id) ON UPDATE CASCADE ON DELETE RESTRICT`
- **Indeks Performa Query & Laporan**:
  - `INDEX idx_transactions_date_type (transaction_date, type)`
  - `INDEX idx_transactions_wallet_type (wallet_id, type)`
  - `INDEX idx_transactions_category_date (category_id, transaction_date)`
- **Validasi Konsistensi Data (Application Layer)**:
  - Nilai `type` pada `transactions` **wajib sama** dengan nilai `type` pada `categories` yang dirujuk oleh `category_id`. Sistem menolak penyimpanan jika transaksi bertipe `expense` dipasangkan dengan kategori bertipe `income` (dan sebaliknya).
  - Nilai `amount` wajib berupa bilangan bulat (integer) dengan syarat $\text{amount} > 0$.

---

## 4. Aturan Hapus Dompet dan Kategori yang Masih Dipakai Transaksi

Untuk menjaga keakuratan saldo dan riwayat laporan, diterapkan perlindungan ganda (di level aplikasi dan level database):

### 4.1. Aturan Hapus Dompet
1. **Dilarang Menghapus Dompet yang Memiliki Transaksi**:
   - Saat master menekan tombol hapus pada suatu dompet, sistem menghitung jumlah baris di tabel `transactions` dengan `wallet_id` tersebut.
   - Jika jumlah transaksi $> 0$, penghapusan **ditolak** dengan pesan kesalahan:
     > *"Dompet '[Nama Dompet]' tidak dapat dihapus karena masih memiliki [N] transaksi. Silakan pindahkan transaksi ke dompet lain atau hapus transaksinya terlebih dahulu."*
2. **Dilarang Menghapus Dompet Terakhir**:
   - Jika total dompet di tabel `wallets` hanya tersisa $1$, penghapusan **ditolak** dengan pesan:
     > *"Minimal harus ada satu dompet aktif di dalam aplikasi."*
3. **Pergantian Dompet Utama (`is_primary`)**:
   - Jika dompet yang dihapus (karena sudah kosong transaksi) kebetulan berstatus `is_primary = true`, sistem otomatis menetapkan dompet paling awal lainnya sebagai `is_primary = true`.

### 4.2. Aturan Hapus Kategori
1. **Keputusan Desain v1 — Tolak Hapus Jika Masih Dipakai (`RESTRICT`)**:
   - Sesuai arahan PRD Bagian 6, penghapusan kategori yang masih dipakai transaksi **tidak diizinkan di v1**.
   - Jika jumlah transaksi dengan `category_id` tersebut $> 0$, penghapusan **ditolak** dengan pesan kesalahan:
     > *"Kategori '[Nama Kategori]' tidak dapat dihapus karena sedang digunakan oleh [N] transaksi. Silakan ubah kategori pada transaksi terkait terlebih dahulu."*
   - Kategori yang belum pernah dipakai transaksi ($0$ transaksi) dapat dihapus dengan bebas.

---

## 5. Aturan Keamanan PIN

### 5.1. Spesifikasi PIN
- **Format**: Tepat 6 digit angka (`0` sampai `9`), divalidasi dengan pola regex `/^\d{6}$/`.
- **Penyimpanan**: Di-hash menggunakan `Hash::make($pin)` (Bcrypt/Argon2id bawaan Laravel) di kolom `app_settings.pin_hash`. Angka PIN polos tidak pernah disimpan di database, session, maupun file log.

### 5.2. Alur Pertama Kali Pakai vs Buka Aplikasi Harian
1. **Kondisi Belum Ada PIN (`pin_hash IS NULL`)**:
   - Middleware `EnsurePinIsConfigured` mendeteksi bahwa PIN belum diatur.
   - Seluruh akses URL diarahkan paksa ke halaman **Buat PIN Baru** (`/pin/setup`).
   - Master memasukkan 6 digit PIN dan 6 digit Konfirmasi PIN. Setelah cocok, hash disimpan dan master diarahkan ke halaman masukkan PIN atau langsung ke Beranda.
2. **Kondisi Buka Aplikasi (`pin_hash IS NOT NULL`)**:
   - Middleware `EnsurePinIsUnlocked` memeriksa flag session `session('pin_unlocked_at')`.
   - Konfigurasi session Laravel diatur dengan `SESSION_EXPIRE_ON_CLOSE=true` agar setiap kali browser ditutup dan aplikasi dibuka kembali, master wajib memasukkan PIN kembali.
   - Tersedia juga tombol **"Kunci Aplikasi"** di header/navigasi untuk mengunci sesi secara instan kapan saja.
  - **Idle timeout (diputuskan master 31 Oktober 2026): 15 menit.** Jika tidak ada aktivitas selama 15 menit, sesi buka kunci otomatis terkunci dan master wajib memasukkan PIN lagi. Ini berlaku di samping kunci saat browser ditutup dan tombol Kunci Aplikasi.

### 5.3. Mekanisme Penguncian Sementara (Brute-Force Protection)
Status percobaan disimpan di tabel database `app_settings` (bukan di session browser) agar batas percobaan tidak dapat dilewati dengan menghapus cookie atau membuka *Incognito*:
- **Batas Maksimal Percobaan Salah (diputuskan master 31 Oktober 2026: SETUJU)**: $5$ kali berturut-turut.
- **Durasi Penguncian Sementara (diputuskan master 31 Oktober 2026: SETUJU)**: $5$ menit ($300$ detik) sejak kesalahan ke-$5$.
- **Perilaku Saat Salah Input**:
  - Setiap kali PIN salah, `failed_attempts` bertambah $1$.
  - Selama `failed_attempts` $< 5$, tampil pesan: *"PIN salah. Sisa percobaan: [5 - failed_attempts] kali."*
  - Saat `failed_attempts` mencapai $5$, kolom `locked_until` diisi dengan `now()->addMinutes(5)`.
  - Selama waktu server saat ini masih kurang dari `locked_until`, form PIN dinonaktifkan dan menampilkan pesan: *"Terlalu banyak percobaan salah. Aplikasi dikunci sementara, silakan coba lagi dalam [Sisa Waktu]."*
- **Perilaku Saat Berhasil Masuk**:
  - Begitu PIN benar (atau setelah masa tunggu `locked_until` habis lalu memasukkan PIN yang benar), `failed_attempts` di-reset menjadi $0$ dan `locked_until` dikosongkan (`NULL`).

### 5.4. Penanganan Lupa PIN (Perintah Lokal Pemilik Server)
Karena CatatCash v1 adalah aplikasi *single-user self-hosted* tanpa pengiriman email, lupa PIN ditangani langsung oleh master melalui terminal Laragon di laptop/server:
- **Perintah Artisan**:
  ```bash
  php artisan catatcash:reset-pin
  ```
- **Cara Kerja Perintah**:
  1. Meminta konfirmasi di terminal: `"Apakah Anda yakin ingin mereset PIN CatatCash? (yes/no)"`.
  2. Mengosongkan `pin_hash = NULL`, mereset `failed_attempts = 0`, dan `locked_until = NULL` pada tabel `app_settings`, serta membersihkan semua sesi aktif di server.
  3. Menampilkan pesan sukses di terminal. Saat master membuka kembali aplikasi di browser, halaman **Buat PIN Baru** (`/pin/setup`) otomatis tampil untuk membuat PIN 6 digit yang baru.

---

## 6. Daftar Halaman dan Route

Seluruh route menggunakan identifier Bahasa Inggris, sedangkan judul dan elemen tampilan menggunakan Bahasa Indonesia.

| Method | URI | Route Name | Controller & Action | Middleware | Nama Halaman / Fungsi (UI Bahasa Indonesia) |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `GET` | `/pin/setup` | `pin.setup` | `PinController@showSetupForm` | `web` | Halaman **Buat PIN 6 Digit** (hanya aktif jika PIN belum ada) |
| `POST` | `/pin/setup` | `pin.setup.store` | `PinController@storeSetup` | `web` | Proses simpan PIN pertama kali |
| `GET` | `/pin/unlock` | `pin.unlock` | `PinController@showUnlockForm` | `web, pin.configured` | Halaman **Masukkan PIN** (Layar Kunci) |
| `POST` | `/pin/unlock` | `pin.unlock.verify` | `PinController@verifyUnlock` | `web, pin.configured` | Proses verifikasi PIN & cek penguncian sementara |
| `POST` | `/pin/lock` | `pin.lock` | `PinController@lockSession` | `web, pin.unlocked` | Aksi **Kunci Aplikasi** (menghapus sesi buka kunci) |
| `GET` | `/` | `dashboard.index` | `DashboardController@index` | `web, pin.unlocked` | Halaman **Beranda** (Total Saldo, Ringkasan Bulan Ini, Dompet Saya, Kotak Segera Hadir, Transaksi Terbaru) |
| `GET` | `/transactions` | `transactions.index` | `TransactionController@index` | `web, pin.unlocked` | Halaman **Semua Transaksi** (dibuka dari "Lihat Semua" di Beranda) |
| `GET` | `/transactions/create` | `transactions.create` | `TransactionController@create` | `web, pin.unlocked` | Halaman **Catat Transaksi** (Form tambah transaksi baru) |
| `POST` | `/transactions` | `transactions.store` | `TransactionController@store` | `web, pin.unlocked` | Proses simpan transaksi baru |
| `GET` | `/transactions/{transaction}/edit` | `transactions.edit` | `TransactionController@edit` | `web, pin.unlocked` | Halaman **Ubah Transaksi** (Form edit transaksi) |
| `PUT` | `/transactions/{transaction}` | `transactions.update` | `TransactionController@update` | `web, pin.unlocked` | Proses perbarui data transaksi |
| `DELETE` | `/transactions/{transaction}` | `transactions.destroy` | `TransactionController@destroy` | `web, pin.unlocked` | Proses hapus transaksi |
| `GET` | `/wallets` | `wallets.index` | `WalletController@index` | `web, pin.unlocked` | Halaman **Kelola Dompet** (Daftar dompet beserta saldo & form tambah dompet) |
| `POST` | `/wallets` | `wallets.store` | `WalletController@store` | `web, pin.unlocked` | Proses tambah dompet baru |
| `PUT` | `/wallets/{wallet}` | `wallets.update` | `WalletController@update` | `web, pin.unlocked` | Proses ubah nama dompet |
| `DELETE` | `/wallets/{wallet}` | `wallets.destroy` | `WalletController@destroy` | `web, pin.unlocked` | Proses hapus dompet (ditolak jika masih ada transaksi) |
| `GET` | `/categories` | `categories.index` | `CategoryController@index` | `web, pin.unlocked` | Halaman **Kelola Kategori** (Tab Pemasukan & Pengeluaran + form tambah kategori) |
| `POST` | `/categories` | `categories.store` | `CategoryController@store` | `web, pin.unlocked` | Proses tambah kategori baru |
| `PUT` | `/categories/{category}` | `categories.update` | `CategoryController@update` | `web, pin.unlocked` | Proses ubah nama kategori |
| `DELETE` | `/categories/{category}` | `categories.destroy` | `CategoryController@destroy` | `web, pin.unlocked` | Proses hapus kategori (ditolak jika masih dipakai transaksi) |
| `GET` | `/reports` | `reports.index` | `ReportController@index` | `web, pin.unlocked` | Halaman **Laporan** (Filter Periode, Dompet, Kategori, Total, Laba Bersih, & Grafik Tren Alur Kas) |

---

## 7. Token Desain (Design Tokens)

Mengacu pada spesifikasi visual di PRD Bagian 7 (kartu saldo ungu, hijau untuk pemasukan, merah untuk pengeluaran, kartu putih membulat):

### 7.1. Palet Warna (Color Tokens)

| Nama Token | Nilai Hex / CSS | Kelas Tailwind Acuan | Penggunaan di Antarmuka |
| :--- | :--- | :--- | :--- |
| `--color-primary-purple` | `#6D28D9` | `bg-violet-700` | Warna dasar kartu Total Saldo & tombol aksi utama (Simpan, Masuk). |
| `--color-primary-gradient` | `linear-gradient(135deg, #7C3AED 0%, #4C1D95 100%)` | `bg-gradient-to-br from-violet-600 to-violet-950` | Latar kartu utama **Total Saldo** di Beranda & header layar PIN. |
| `--color-income` | `#16A34A` | `text-green-600` / `bg-green-600` | Angka nominal pemasukan (`+ Rp ...`), badge pemasukan, garis/batang grafik pemasukan. |
| `--color-income-soft` | `#DCFCE7` | `bg-green-100` | Latar ikon/badge untuk transaksi dan kategori Pemasukan. |
| `--color-expense` | `#DC2626` | `text-red-600` / `bg-red-600` | Angka nominal pengeluaran (`- Rp ...`), badge pengeluaran, garis/batang grafik pengeluaran. |
| `--color-expense-soft` | `#FEE2E2` | `bg-red-100` | Latar ikon/badge untuk transaksi dan kategori Pengeluaran. |
| `--color-bg-app` | `#F8FAFC` | `bg-slate-50` | Latar belakang utama halaman di belakang kartu putih. |
| `--color-surface-card` | `#FFFFFF` | `bg-white` | Latar belakang kartu dompet, daftar transaksi, filter laporan, dan kotak form. |
| `--color-surface-muted` | `#F1F5F9` | `bg-slate-100` | Latar belakang kotak fitur **"Segera Hadir"** (Pantauan Anggaran & Target Tabungan). |
| `--color-text-primary` | `#0F172A` | `text-slate-900` | Judul halaman, nama dompet, nama kategori, dan angka utama. |
| `--color-text-secondary` | `#64748B` | `text-slate-500` | Tanggal, jam, catatan transaksi, dan teks sub-label. |
| `--color-border` | `#E2E8F0` | `border-slate-200` | Garis tepi kartu dan pemisah antar baris transaksi. |

### 7.2. Tipografi & Format Angka
- **Font Family**: `'Inter', system-ui, -apple-system, sans-serif` (menggunakan system font stack atau font lokal agar tidak memanggil Google Fonts eksternal).
- **Ukuran Teks**:
  - Nominal Total Saldo Utama: `1.875rem` (`30px`, `font-bold`)
  - Judul Halaman / Saldo per Dompet: `1.25rem` (`20px`, `font-semibold`)
  - Nama Kategori / Nominal Transaksi: `1rem` (`16px`, `font-semibold`)
  - Catatan & Label Form: `0.875rem` (`14px`, `font-normal`)
  - Tanggal, Jam & Badge "Segera Hadir": `0.75rem` (`12px`, `font-medium`)
- **Aturan Format Nominal Rupiah**:
  - Pemisah ribuan menggunakan titik (`.`), tanpa angka desimal di belakang koma.
  - Saldo Dompet & Total: `Rp 1.250.000` (jika saldo bernilai negatif karena pengeluaran melebihi pemasukan: `-Rp 50.000`).
  - Item Transaksi Pemasukan: `+ Rp 500.000` (warna `--color-income`).
  - Item Transaksi Pengeluaran: `- Rp 75.000` (warna `--color-expense`).

### 7.3. Bentuk, Jarak, dan Tata Letak (Spacing & Radius)
- **Lebar Kontainer**:
  - Tampilan responsif berfokus pada kenyamanan di HP dan laptop: kontainer utama memiliki lebar maksimal `max-w-3xl` (`768px`) terpusat di tengah layar dengan padding tepi `16px` (`px-4`) di HP dan `24px` (`px-6`) di laptop. **Amandemen layout 1 November 2026 (disetujui master, lihat Bab 13): aturan ini hanya berlaku untuk tampilan mobile. Di desktop (layar lebar), tampilannya BERBEDA: dashboard dengan sidebar kiri dan grid multi-kolom, bukan satu kolom sempit di tengah.**
- **Sudut Membulat (Border Radius)**:
  - Kartu Saldo Ungu Utama: `20px` (`rounded-2xl`)
  - Kartu Putih (Dompet, Transaksi, Grafik, Segera Hadir): `16px` (`rounded-2xl`)
  - Input Form & Tombol: `12px` (`rounded-xl`)
  - Badge Status / Kategori: `9999px` (`rounded-full`)
- **Bayangan (Elevation)**:
  - Kartu Putih: `0 1px 3px 0 rgba(15, 23, 42, 0.06), 0 1px 2px -1px rgba(15, 23, 42, 0.06)` (`shadow-sm border border-slate-200/80`).

---

## 8. Komponen Tampilan yang Dipakai Ulang (Reusable Components)

Seluruh komponen disimpan di `resources/views/components/` agar tampilan seragam di seluruh halaman:

1. **`<x-summary-balance-card>`**
   - **Fungsi**: Kartu ungu utama di bagian paling atas Beranda.
   - **Data Input (`props`)**: `totalBalance`, `currentMonthIncome`, `currentMonthExpense`, `periodLabel`.
   - **Tampilan**: Menampilkan label `"Total Saldo Semua Dompet"`, angka nominal besar berwarna putih, serta dua kotak ringkas di bagian bawah kartu untuk `"Pemasukan Bulan Ini"` (ikon panah hijau) dan `"Pengeluaran Bulan Ini"` (ikon panah merah).
2. **`<x-wallet-card>`**
   - **Fungsi**: Menampilkan satu item dompet beserta saldonya di Beranda maupun di halaman Kelola Dompet.
   - **Data Input (`props`)**: `walletName`, `calculatedBalance`, `isPrimary`, `transactionCount`.
   - **Tampilan**: Kartu putih membulat dengan nama dompet, badge `"Utama"` (jika `isPrimary`), dan nominal saldo hasil perhitungan transaksi.
3. **`<x-coming-soon-card>`**
   - **Fungsi**: Menampilkan kotak fitur v2 (**Pantauan Anggaran** dan **Target Tabungan**) di Beranda sesuai keputusan PRD 4.5.
   - **Data Input (`props`)**: `title`, `description`.
   - **Perilaku Ketat**: Menggunakan elemen `<div>` statis (bukan `<a>` dan bukan `<button>`), memiliki `cursor-default` dan `pointer-events-none`, berlatar abu-abu lembut (`--color-surface-muted`) dengan badge `"Segera Hadir"`. Sama sekali tidak bisa diklik menjadi fitur.
4. **`<x-transaction-item>`**
   - **Fungsi**: Menampilkan satu baris transaksi di daftar Transaksi Terbaru (Beranda) dan halaman Semua Transaksi.
   - **Data Input (`props`)**: `transaction` (memuat relasi `category` dan `wallet`).
   - **Tampilan**:
     - Kiri: Indikator warna jenis (hijau/merah), Nama Kategori (`font-semibold`), Nama Dompet, Catatan (jika ada), serta Tanggal dan Jam.
     - Kanan: Nominal dengan tanda `+ Rp ...` (hijau) untuk pemasukan atau `- Rp ...` (merah) untuk pengeluaran, disertai tombol aksi cepat Edit / Hapus.
5. **`<x-empty-state>`**
   - **Fungsi**: Menampilkan pesan kosong yang jelas saat daftar transaksi atau hasil filter laporan tidak memiliki data.
   - **Data Input (`props`)**: `title`, `message`, `actionUrl` (opsional), `actionLabel` (opsional).
   - **Tampilan**: Ilustrasi ikon sederhana (SVG inline), judul tebal (misal: `"Belum Ada Data Transaksi"`), dan kalimat penjelas dalam Bahasa Indonesia.
6. **`<x-pin-keypad>`**
   - **Fungsi**: Antarmuka input 6 bulatan indikator PIN dan papan tombol angka `0-9` + tombol `Hapus` di layar kunci dan layar pembuatan PIN.
   - **Data Input (`props`)**: `formAction`, `title`, `subtitle`, `isLocked`, `remainingSeconds`.
7. **`<x-bottom-nav>`**
   - **Fungsi**: Navigasi utama aplikasi yang selalu mudah dijangkau di HP maupun laptop:
     - **Beranda** (`dashboard.index`)
     - **Transaksi** (`transactions.index`)
     - **+ Catat** (`transactions.create` — tombol menonjol berwarna ungu)
     - **Dompet & Kategori** (`wallets.index` / `categories.index`)
     - **Laporan** (`reports.index`)

---

## 9. Definisi Filter Laporan dan Cara Menghitung Saldo & Grafik

### 9.1. Rumus Perhitungan Saldo dan Ringkasan
Seluruh perhitungan dilakukan di database menggunakan agregasi `SUM` agar akurat dan cepat:

1. **Saldo per Dompet ($w$)**:
   $$\text{Saldo}(w) = \sum_{t \in \text{Income}(w)} \text{amount}_t - \sum_{t \in \text{Expense}(w)} \text{amount}_t$$
   Jika dompet belum memiliki transaksi, maka $\text{Saldo}(w) = 0$.

2. **Total Saldo Seluruh Dompet (Beranda)**:
   $$\text{Total Saldo} = \sum_{w \in \text{Wallets}} \text{Saldo}(w)$$

3. **Total Pemasukan, Total Pengeluaran, dan Laba Bersih pada Periode & Filter Laporan ($F$)**:
   $$\text{Total Pemasukan}(F) = \sum_{t \in \text{Income}(F)} \text{amount}_t$$
   $$\text{Total Pengeluaran}(F) = \sum_{t \in \text{Expense}(F)} \text{amount}_t$$
   $$\text{Laba Bersih}(F) = \text{Total Pemasukan}(F) - \text{Total Pengeluaran}(F)$$
   *(Catatan: Jika $\text{Laba Bersih}(F) < 0$, angka ditampilkan dengan tanda minus `-Rp ...` berwarna merah; jika $\ge 0$ ditampilkan berwarna hijau/netral).*

### 9.2. Definisi Filter pada Halaman Laporan (`GET /reports`)

Halaman Laporan menerima parameter query string berikut dan menyimpannya sebagai state filter aktif di tampilan:

1. **Filter Periode (`period`)**:
   - **`daily` (Harian)**:
     - Parameter tambahan: `date` (format `YYYY-MM-DD`, default: tanggal hari ini).
     - Query menyaring transaksi dengan `transaction_date = date`.
   - **`weekly` (Mingguan)**:
     - Parameter tambahan: `week_start` atau tanggal acuan (default: minggu berjalan dari hari **Senin** pukul `00:00:00` sampai hari **Minggu** pukul `23:59:59`).
     - Query menyaring transaksi dengan `transaction_date BETWEEN start_of_week AND end_of_week`.
   - **`monthly` (Bulanan — Default saat membuka halaman Laporan)**:
     - Parameter tambahan: `month` (format `YYYY-MM`, default: bulan dan tahun berjalan).
     - Query menyaring transaksi dari tanggal $1$ hingga tanggal terakhir pada bulan tersebut (`28`/`29`/`30`/`31`).
   - **`custom` (Custom / Rentang Tanggal Pilihan Master)**:
     - Parameter tambahan: `start_date` (`YYYY-MM-DD`) dan `end_date` (`YYYY-MM-DD`).
     - Validasi wajib: `start_date <= end_date`.
     - Query menyaring transaksi dengan `transaction_date BETWEEN start_date AND end_date`.

2. **Filter Dompet (`wallet_id`)**:
   - Pilihan `"all"` (**Semua Dompet** — default): Tidak membatasi `wallet_id`.
   - Pilihan ID dompet tertentu (misal `wallet_id = 1`): Menambahkan klausa `WHERE wallet_id = 1`.

3. **Filter Kategori (`category_filter`)**:
   - Dropdown / pilihan menampilkan opsi:
     - **`all`** (**Semua Kategori** — default): Menampilkan seluruh transaksi pemasukan dan pengeluaran.
     - **Kelompok Pemasukan**:
       - `type:income` (*Semua Kategori Pemasukan*)
       - Daftar masing-masing kategori pemasukan (`id` kategori terkait)
     - **Kelompok Pengeluaran**:
       - `type:expense` (*Semua Kategori Pengeluaran*)
       - Daftar masing-masing kategori pengeluaran (`id` kategori terkait)
   - Jika suatu kategori spesifik dipilih (misal: `"Makan & Minum"` yang berjenis Pengeluaran), maka pada periode tersebut:
     - $\text{Total Pemasukan} = 0$
     - $\text{Total Pengeluaran} = \text{jumlah pengeluaran pada kategori Makan \& Minum}$
     - $\text{Laba Bersih} = 0 - \text{Total Pengeluaran}$

### 9.3. Cara Menghitung dan Merender Grafik "Tren Alur Kas"

Grafik **Tren Alur Kas** membandingkan dua seri data: **Pemasukan** (warna `#16A34A`) dan **Pengeluaran** (warna `#DC2626`) sepanjang periode yang dipilih:

**Jenis grafik (diputuskan master 31 Oktober 2026): default otomatis menyesuaikan (Batang untuk Harian/Mingguan, Garis untuk Bulanan), TETAPI halaman Laporan wajib menyediakan tombol pemilih jenis grafik sehingga master bisa menampilkan semua versi grafik: Garis, Batang Berdampingan, dan Otomatis.** Pilihan jenis grafik adalah state tampilan saja, bukan perubahan data; data dan perhitungannya tetap sama untuk semua jenis.

1. **Penentuan Titik Sumbu X (Buckets) Berdasarkan Filter Periode**:
   - **Periode Harian (`daily`)**:
     - Menampilkan perbandingan Pemasukan vs Pengeluaran pada tanggal terpilih (1 titik tanggal `DD MMM YYYY` dengan 2 batang berdampingan: Pemasukan & Pengeluaran).
   - **Periode Mingguan (`weekly`)**:
     - Sumbu X selalu memiliki **7 titik tanggal** berurutan (`Senin` s.d. `Minggu`).
     - Tanggal yang tidak memiliki transaksi tetap dimasukkan ke deret sumbu X dengan nilai Pemasukan $= 0$ dan Pengeluaran $= 0$ agar garis/batang waktu tidak melompat.
   - **Periode Bulanan (`monthly`)**:
     - Sumbu X memiliki **$N$ titik tanggal** sesuai jumlah hari dalam bulan tersebut ($28, 29, 30,$ atau $31$ hari).
     - Setiap tanggal $d \in \{1, \dots, N\}$ memiliki pasangan nilai $(\text{Income}_d, \text{Expense}_d)$, dengan nilai default $0$ pada tanggal tanpa transaksi.
   - **Periode Custom (`custom`)**:
     - Jika selisih hari antara `start_date` dan `end_date` adalah $\le 31$ hari: Sumbu X dikelompokkan **per hari** (setiap tanggal adalah 1 titik).
     - Jika selisih hari $> 31$ hari dan $\le 180$ hari: Sumbu X dikelompokkan **per minggu**.
     - Jika selisih hari $> 180$ hari: Sumbu X dikelompokkan **per bulan** (`MMM YYYY`) agar label grafik tetap terbaca dengan rapi di layar HP.

2. **Penanganan Keadaan Kosong (Empty State) pada Laporan**:
   - Sebelum merender kanvas Chart.js, sistem memeriksa total jumlah baris transaksi (`transaction_count`) yang cocok dengan kombinasi filter (`period`, `wallet_id`, `category_filter`).
   - Jika $\text{transaction\_count} = 0$:
     - Kartu ringkasan tetap menampilkan `Total Pemasukan: Rp 0`, `Total Pengeluaran: Rp 0`, dan `Laba Bersih: Rp 0`.
     - Area grafik **tidak menampilkan kanvas grafik kosong**, melainkan menampilkan komponen `<x-empty-state>` dengan pesan:
       > **"Belum ada data transaksi pada periode dan filter yang dipilih."**

---

## 10. Batasan Scope: Fitur yang SENGAJA Tidak Masuk v1

Sesuai PRD Bagian 9, desain database, route, dan antarmuka di dokumen ini **tidak menyertakan** fitur-fitur berikut agar v1 tetap fokus, bersih, dan bebas dari fitur setengah jalan:

### 10.1. Ditunda ke v2 (Setelah v1 Jalan Penuh dan Lulus Uji)
- Input suara (konversi ucapan barang dan nominal menjadi transaksi).
- Lampiran foto nota pada transaksi manual.
- Scan Nota (pembacaan foto struk otomatis).
- Transfer antar dompet.
- Transaksi Rutin / Ulangi otomatis.
- Fungsionalitas Pantauan Anggaran (di v1 hanya tampil sebagai kartu statis *"Segera Hadir"* di Beranda yang tidak bisa diklik).
- Fungsionalitas Target Tabungan (di v1 hanya tampil sebagai kartu statis *"Segera Hadir"* di Beranda yang tidak bisa diklik).
- Export laporan ke PDF dan Excel, serta fitur Bagikan Laporan.
- **Bot Telegram** (catat transaksi lewat chat Telegram): **Dipertahankan untuk v2**, tidak dibangun di v1.

### 10.2. Fase Lanjutan (Setelah v2 — Bukan v1 dan Bukan v2)
- **Reality Check** (Insight plus Kalkulator Realita: Keuangan, Waktu & Hidup, Kebiasaan): Dipertahankan untuk fase lanjutan setelah v2, tidak dibangun di v1 maupun v2.

---

## 11. Daftar Keputusan yang Masih Perlu Persetujuan Master

Sesuai aturan proyek untuk tidak menebak diam-diam hal yang belum ditetapkan secara eksplisit di PRD, mohon keputusan/persetujuan Master untuk **7 poin teknis** berikut sebelum dokumen ini dikunci menjadi final:

1. **Batas Salah PIN dan Durasi Kunci Sementara (Bagian 5.3)**:
   - Di dokumen ini disarankan: maksimal **5 kali** percobaan salah berturut-turut, lalu dikunci sementara selama **5 menit**. Apakah angka 5 kali dan 5 menit ini sudah sesuai keinginan Master, atau ingin diubah?
2. **Masa Aktif Sesi Buka Kunci PIN (Bagian 5.2)**:
   - PRD menetapkan PIN diminta setiap membuka aplikasi. Selain mengunci otomatis saat browser ditutup (`SESSION_EXPIRE_ON_CLOSE=true`) dan menyediakan tombol manual **"Kunci Aplikasi"**, apakah Master ingin menambahkan batas waktu diam (*idle timeout*, misalnya otomatis terkunci kembali jika tab dibiarkan tanpa aktivitas selama **15 menit** atau **30 menit**)?
3. **Input Jam pada Form Transaksi vs Jam Pencatatan (Bagian 3.4)**:
   - Di PRD 4.3 disebutkan: *"Tanggal transaksi: default hari ini, bisa diubah (wajib)"* dan *"Daftar transaksi menampilkan: kategori, catatan, tanggal dan jam, serta nominal"*.
   - Apakah pada form **Catat Transaksi** Master hanya ingin memilih **Tanggal saja** (sedangkan jam yang ditampilkan diambil otomatis dari jam saat transaksi disimpan `created_at`), atau Master ingin di form juga ada input untuk mengubah **Jam dan Menit** secara manual?
4. **Jumlah "Transaksi Terbaru" di Halaman Beranda (Bagian 6 & 8)**:
   - Berapa jumlah baris transaksi terbaru yang ideal untuk ditampilkan di halaman Beranda sebelum pengguna menekan tombol **"Lihat Semua"**? (Rekomendasi: **5** atau **10** transaksi terakhir).
5. **Ubah Nama & Hapus pada Dompet dan Kategori (Bagian 4 & 6)**:
   - Di PRD disebutkan fitur tambah dompet dan tambah kategori, serta larangan menghapus dompet/kategori yang masih dipakai transaksi. Apakah di halaman **Kelola Dompet** dan **Kelola Kategori** v1 Master juga mengizinkan fitur **Ubah Nama (Edit)** dan **Hapus** (khusus untuk dompet/kategori yang masih memiliki $0$ transaksi), atau hanya boleh **Tambah** saja?
6. **Jenis Grafik Tren Alur Kas di Halaman Laporan (Bagian 9.3)**:
   - Untuk menampilkan perbandingan Pemasukan (hijau) vs Pengeluaran (merah) pada **Tren Alur Kas**, apakah Master lebih menyukai **Grafik Garis (*Line Chart*)**, **Grafik Batang Berdampingan (*Grouped Bar Chart*)**, atau otomatis menyesuaikan (Batang untuk Harian/Mingguan, Garis untuk Bulanan)?
7. **Mesin Database Lokal di Laragon (Bagian 1)**:
   - Apakah Master setuju menggunakan **MySQL / MariaDB** bawaan Laragon sebagai database utama di v1, atau lebih memilih **SQLite** (satu file `database.sqlite`)?

---

## 12. Amandemen — Keputusan Master 31 Oktober 2026

Bab 11 di atas SUDAH DIJAWAB master pada 31 Oktober 2026. Jawaban ini mengikat dan sudah dimasukkan ke bagian-bagian terkait di atas (kecuali nomor 4 dan 5 yang dicatat di sini dan berlaku untuk implementasi):

1. **Kunci PIN: SETUJU 5 kali salah, kunci 5 menit.** (Bagian 5.3 diperbarui.)
2. **Idle timeout: PERLU, 15 menit.** Sesi buka kunci otomatis terkunci setelah 15 menit tanpa aktivitas, di samping kunci saat browser ditutup dan tombol Kunci Aplikasi. (Bagian 5.2 diperbarui.)
3. **Input jam: PERLU.** Form Catat Transaksi menyediakan input jam dan menit; defaultnya otomatis mengikuti waktu saat ini, master boleh mengubahnya bila ingin menyesuaikan. Jam dipakai untuk jaga-jaga, meski jarang dipakai. Daftar transaksi menampilkan tanggal + jam dari kolom `transaction_date` + `transaction_time`, bukan dari `created_at`. (Bagian 3.4 diperbarui: kolom `transaction_time` ditambahkan.)
4. **Transaksi Terbaru di Beranda: 5 baris saja.** Sebelum tombol "Lihat Semua", Beranda menampilkan 5 transaksi terbaru.
5. **Edit dan hapus dompet/kategori: BOLEH.** Ubah nama (edit) diizinkan; hapus diizinkan khusus untuk dompet/kategori yang masih memiliki 0 transaksi. Aturan hapus di Bagian 4 tetap berlaku: dompet/kategori yang masih dipakai transaksi ditolak dihapus, dan dompet terakhir tidak boleh dihapus.
6. **Grafik: default otomatis menyesuaikan, PLUS tombol pemilih jenis grafik.** Halaman Laporan menyediakan tombol untuk menampilkan semua versi grafik: Garis, Batang Berdampingan, dan Otomatis. (Bagian 9.3 diperbarui.)
7. **Database: SETUJU MySQL/MariaDB.** SQLite tidak dipakai di v1. Database utama v1 adalah MySQL/MariaDB bawaan Laragon.
8. **Versi stack: pakai Laravel terbaru dan versi terakhir yang kompatibel.** Diputuskan master 31 Oktober 2026 sebagai tambahan atas Bab 11. Laravel 13.x terbaru yang stabil saat implementasi dimulai (per 27 Oktober 2026: 13.39.0, PHP 8.3–8.5), bukan 11.x/12.x dari draf awal Argon. Komponen lain (Tailwind, Vite, Alpine, Chart.js) memakai versi terakhir yang kompatibel dengan Laravel 13 terpilih, dikunci sebagai satu set saat instalasi. Laragon di laptop master harus dicek dulu apakah PHP-nya sudah 8.3 atau lebih tinggi, karena Laravel 13 tidak mendukung PHP 8.2.

**Status setelah amandemen ini:** dokumen ini DISETUJUI FINAL oleh master pada 31 Oktober 2026. Langkah berikutnya adalah Tahap 3: prototype, lalu TASKS.md, lalu implementasi per tugas dengan Cline.

---

## 13. Amandemen Layout — Keputusan Master 1 November 2026

Disetujui master 1 November 2026 ("sudah ok") atas prototype Wing yang menerapkan layout berbeda per perangkat, dan menjadi acuan wajib untuk prototype Argon berikutnya:

- **Mobile (HP):** tampilan seperti aplikasi satu kolom, navigasi bawah (Beranda, Transaksi, tombol + Catat, Dompet & Kategori, Laporan), kontainer sempit terpusat.
- **Desktop (layar lebar, patokan 1024px ke atas):** tampilannya BERBEDA, mirip dashboard yang bagus: sidebar kiri tetap (Beranda, Transaksi, Catat Transaksi, Dompet & Kategori, Laporan, Kunci Aplikasi), navigasi bawah disembunyikan, area konten lebar dengan grid multi-kolom (misalnya kartu saldo membentang penuh, lalu kartu Dompet dan Transaksi Terbaru berdampingan; halaman Laporan memakai grid filter + grafik berdampingan).
- Fitur, data, token warna, dan scope v1 SAMA di kedua versi; yang berbeda hanya tata letak per perangkat. Jangan membuat dua aplikasi terpisah.
- **Ikon wajib:** setiap kategori, setiap menu navigasi, dan tombol Edit serta Hapus wajib punya ikon yang terlihat (pelajaran dari review prototype Wing: prototype tanpa ikon terasa hambar dan tombol tanpa umpan balik klik membingungkan).
- **Umpan balik klik wajib di prototype:** setiap tombol yang bisa diklik harus memberi tanda visual saat dipilih (misalnya kategori terpilih berubah warna dan tertulis kategori terpilihnya), bukan hanya tampil diam.