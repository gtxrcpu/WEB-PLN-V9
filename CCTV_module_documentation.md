# Dokumentasi Teknis & Panduan Pengguna: Pilot Project Modul CCTV

## Pendahuluan
Modul CCTV ini merupakan inisiatif *pilot project* eksklusif yang dirancang khusus untuk _role_ **Superadmin**. Modul ini menyediakan antarmuka terpusat bagi Superadmin untuk mendata perangkat CCTV dan mengelola status / kondisinya dengan indikator yang sederhana namun penting: **Baik** atau **Jelek**. Skema ini memudahkan administrator memantau kapabilitas aset surveillance dan memutuskan prioritas maintenance.

---

## 1. Dokumentasi Teknis

### A. Skema Database (Database Schema)
Data modul dikelola melalui tabel `cctvs` yang diinisialisasi melalui file *migration* (contoh: `2026_03_xxxx_create_cctvs_table.php`).
Struktur tabel:
- `id` (bigint): Primary Key.
- `unit_id` (foreign_key, nullable): Referensi opsional ke Unit/Wilayah operasional jika integrasi antar unit diaktifkan kelaknya.
- `name` (string): Nama atau label perangkat (Contoh: "CCTV Gerbang Barat").
- `location_code` (string): Kode lokasi sebagai identifier letak (Contoh: "GB-01").
- `status` (enum): Status fungsionalitas CCTV, bernilai `'Baik'` atau `'Jelek'`. Default: `'Baik'`.
- `notes` (text, nullable): Catatan teknis atau maintenance.
- `timestamps`: Track waktu dibuat dan diperbarui.

### B. Endpoint Route & REST API
Sistem mengalokasikan route eksklusif di dalam `routes/web.php` terproteksi dengan middleware `auth` dan `role:superadmin`.

Endpoint standar *Resource*:
- `GET /admin/cctvs` (Daftar seluruh CCTV, dilengkapi fitur search & filter)
- `GET /admin/cctvs/create` (Formulir pengisian data CCTV baru)
- `POST /admin/cctvs` (Logic simpan data CCTV baru)
- `GET /admin/cctvs/{cctv}/edit` (Formulir pengubahan data CCTV exist)
- `PUT /admin/cctvs/{cctv}` (Logic simpan ubahan data CCTV)
- `DELETE /admin/cctvs/{cctv}` (Hapus data CCTV komplit)

**Endpoint API Spesifik Modifikasi Status:**
- `POST /admin/cctvs/{cctv}/status`
> Endpoint ini diamankan (membutuhkan _token CSRF_ + Session Superadmin aktif) bertugas memproses AJAX _request_ secara asinkron tanpa *reload* seluruh halaman, dan akan me-return format respons tipe JSON: `{"success": true, "message": "...", "data": {...}}`.

### C. Pengujian Sistem (Unit & Feature Testing)
Untuk menjaga stabilitas dari _rollout_ rute baru eksklusif ini, telah dirilis file testing:
`tests/Feature/CctvModuleTest.php`

Fitur yang diujikan menjamin:
- **Authorization Enforcment:** Superadmin dapat mengakses seluruh resource CCTV, sebaliknya role `admin` (atau role lainnya) mendapatkan respons *403 Forbidden* saat mencobanya.
- **Data Insertion & Storage:** Validasi entri form masuk sempurna ke struktur *database* `cctvs`.
- **API Status Updates:** Validasi fungsional via _toggle button_ dari tampilan _User-Interface_ menggunakan JSON assert ke payload database yang di-*mock* menggunakan mekanisme *RefreshDatabase*.

---

## 2. Buku Panduan Pengguna (User Manual)

### A. Menambahkan Perangkat CCTV Baru
1. Login menggunakan akun dengan role **Superadmin**.
2. Akses halaman melalui browser ke rute menu: `(Host Anda)/admin/cctvs`.
3. Klik tombol biru **"Tambah CCTV"** di sudut kanan atas menu utama.
4. Isi informasi pada form yang disediakan:
   - **Nama/Label:** (Wajib) Isi dengan nama identifikasi kamera. (e.g. "CCTV Parkir Karyawan")
   - **Kode/Lokasi:** (Wajib) Lokasi penempatan fisik.
   - **Status:** Tentukan kondisi pertama alat (*Baik* atau *Jelek/Bermasalah*).
   - **Catatan:** Tambahkan rekaman log tentang alat ini secara opsional.
5. Klik **"Simpan Perangkat"**.

### B. Memantau Status dan Pencarian Cepat
1. Di halaman **Manajemen CCTV Terpadu**, Anda akan melihat grid berisi kartu CCTV.
2. Gunakan baris filter di bagian atas layar untuk mempermudah.
   - Ketik wilayah atau nama untuk menemukan spesifik kamera.
   - Pilih *dropdown* status untuk langsung menampilkan daftar kamera yang saat ini statusnya "Jelek" agar dapat dijadwalkan perbaikan secepatnya.

### C. Mengubah Status Kamera (Quick Action)
Ada fasilitas status langsung (sejenis tombol *Switch* interaktif) di setiap perwakilan kartu beranda perangkat, yang sangat fungsional:
1. Klik pada _label berwaran biru atau merah_ pada setiap CCTV card (yang berisi teks "Kondisi Baik" atau "Kondisi Jelek").
2. Konfirmasi tindakan melalui *prompt alert*.
3. Sistem secara ghoib (via API asinkron) akan segera mengubah isi dari *database* secara langsung dan halaman refresh memperbaharui tampilan ke mode/warna kebalikan.

### D. Mengedit dan Menghapus Catatan Detail
Jika terdapat kesalahan pada penulisan lokasi/catatan yang panjang, pada tiap _card_ dapat ditemui 2 mode tambahan di bagian _footer_ bawah:
- Tombol **Edit CCTV:** Membawa Anda kepada sesi formulir penuh untuk merombak seluruh data informasi dari nama s/d catatan panjang.
- Tombol **Hapus:** Mem-buang/destroy daftar CCTV dari skema. (*Aksi ini permanen, maka dari itu sebuah konfirmasi pengamanan via pop-up akan disediakan sistem sebelum operasi menghapus dilangsungkan*).

---

🔥 **Panduan Persiapan Expand / Rollout Bertahap** 
Saat dirasa _Pilot project_ diakses Superadmin ini dirasa sepenuhnya matang, _rollout_ bertahap dapat dipicu melalui pencabutan parameter `superadmin` terbatas dari rute route. Ubah filter izin dari `['auth', 'role:superadmin']` di dalam  `routes/web.php` dan buatlah policy akses tersendiri ke `role:admin` atau Leader Unit berdasarkan scope `unit_id` masing-masing yang sudah tersertakan dari basis _blueprint database_-nya.
