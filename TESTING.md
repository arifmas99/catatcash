# TESTING.md — Checklist Pengujian (KERANGKA, BELUM DIISI)

## Status

- BELUM DIMULAI. Checklist ini dilengkapi bersama TASKS.md,
  setelah desain disetujui.
- Pengujian v1 dilakukan manual dulu oleh master, karena ini
  proyek belajar pertama. Pengujian otomatis adalah pelengkap
  belakangan, bukan pengganti tes manual.

## Checklist yang Direncanakan (Dari Kriteria Selesai PRD Bab 10)

- [ ] PIN: tanpa PIN yang benar, beranda dan data tidak terbuka.
- [ ] PIN: PIN salah berulang kali mengunci sementara.
- [ ] Transaksi: tambah pemasukan mengubah saldo dengan benar.
- [ ] Transaksi: tambah pengeluaran mengubah saldo dengan benar.
- [ ] Transaksi: edit transaksi mengubah saldo dengan benar.
- [ ] Transaksi: hapus transaksi mengubah saldo dengan benar.
- [ ] Transaksi: nominal nol atau negatif ditolak.
- [ ] Transaksi: tanggal default hari ini, bisa diubah.
- [ ] Dompet: tambah dompet baru berhasil dan muncul di form.
- [ ] Dompet: saldo per dompet benar, total sama dengan jumlahnya.
- [ ] Kategori: kategori pemasukan tidak muncul di pengeluaran,
      dan sebaliknya.
- [ ] Kategori: tambah kategori baru langsung bisa dipakai.
- [ ] Beranda: angka di beranda sama dengan perhitungan transaksi.
- [ ] Laporan: filter Harian, Mingguan, Bulanan, Custom benar.
- [ ] Laporan: filter dompet dan kategori mengubah total benar.
- [ ] Laporan: grafik tren sesuai periode yang dipilih.
- [ ] Laporan: periode tanpa data menampilkan pesan kosong.
- [ ] Data kosong: aplikasi baru tanpa transaksi tidak error.
- [ ] Input salah: form menampilkan pesan yang jelas, bukan error mentah.
- [ ] Alur penuh: PIN -> tambah transaksi -> beranda -> laporan,
      dari awal sampai akhir, tanpa error.

## Cara Mencatat Hasil Tes

- Setiap baris di atas dicentang hanya setelah master sendiri
  melihat hasilnya di layar dan menyatakan lulus.
- Tes yang gagal dicatat: apa yang gagal, tanggalnya, dan
  tugas perbaikan yang dibuat untuknya. Jangan menghapus
  jejak tes yang gagal.
