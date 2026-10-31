# AGENTS.md — Aturan Kerja Cline di Proyek Ini

File ini dibaca Cline setiap sesi. Aturan di sini mengikat.
Jika ada konflik antara perintah sesaat dan file ini, file ini
yang menang, kecuali master explicitly mengubahnya.

## 1. Prinsip Utama

- Rapih, konsisten, tidak AI slop. Boleh lambat, asal benar.
- Satu tugas per langkah. Jangan mengerjakan dua tugas sekaligus.
- Master membaca diff setiap tugas sebelum commit. Jangan pernah
  mem-bypass review diff dengan auto-approve edit.
- Jangan mengubah scope diam-diam. Perubahan scope lewat PRD.md
  dan persetujuan master dulu.

## 2. Bahasa

- Dokumen: Bahasa Indonesia.
- Identifier kode (variabel, fungsi, class, tabel, kolom, route):
  Bahasa Inggris.
- Label UI: Bahasa Indonesia.

## 3. Alur Kerja Bertahap (Gated)

1. PRD.md disetujui master.
2. DESIGN.md ditulis dan disetujui.
3. Prototype dibuat, tampilannya di-review master.
4. TASKS.md dipecah menjadi tugas kecil yang masing-masing
   bisa dites sendiri.
5. Implementasi per tugas: kerjakan HANYA tugas itu, jalankan,
   tes, lalu commit Git per tugas dengan pesan yang jelas.
6. Pengujian manual dulu (TESTING.md), otomatis belakangan.
7. Dokumentasi diperbarui jika scope berubah.
8. Deploy: lokal -> staging -> server lain. Tidak pernah
   langsung ke produksi.

## 4. Aturan Anti-Slop

- Jangan membuat fitur yang tidak ada di PRD.md / TASKS.md.
- Jangan mengubah desain (warna, layout, struktur) tanpa
  persetujuan, hanya karena "terlihat lebih bagus".
- Jangan menumpuk perbaikan di atas kode yang berantakan.
  Jika hasil jelek: revert, perkecil tugas, ulangi.
- Jika gagal dua kali pada tugas yang sama: berhenti dan
  laporkan ke master, jangan mencoba cara ketiga diam-diam.
- Saldo selalu dihitung dari transaksi, jangan disimpan sebagai
  angka terpisah yang bisa meleset.
- Nominal selalu integer rupiah, harus lebih dari nol.
- Validasi di server, bukan hanya di browser.

## 5. Keamanan dan Rahasia

- Jangan pernah membaca, menyalin, atau menampilkan isi file
  yang ada di .clineignore, terutama .env dan kunci apa pun.
- PIN disimpan sebagai hash, tidak pernah sebagai angka polos.
- Jangan mengirim kunci, token, atau password lewat chat.
  Master menempelkannya sendiri di field yang dituju.
- Aplikasi ini single-user. Jangan menambahkan pendaftaran,
  multi-user, atau koneksi pihak ketiga tanpa persetujuan master.
- Jangan pernah menyentuh VPS produksi Inlislite atau data
  produksi apa pun dari proyek ini. Proyek ini lokal dulu.

## 6. Git

- Satu commit per tugas, pesan commit Bahasa Indonesia yang
  jelas, menyebut tugasnya.
- Jangan commit file rahasia, file .env, atau data pribadi master.
- Sebelum commit, pastikan aplikasi masih jalan dan tes tugas
  itu lulus.

## 7. Jika Ragu

- Berhenti dan tanyakan ke master, jangan menebak.
- Keputusan yang ditebak diam-diam (misalnya menggabung
  "Lain-lain" dan "Lainnya", atau memilih jumlah digit PIN)
  adalah pelanggaran aturan ini. Tulis opsinya, minta master
  memilih, lalu catat keputusannya di DESIGN.md atau PRD.md.
