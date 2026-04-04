# Laporan Ekspansi Sistem User Management

Sistem User Management dan Unit Hierarchy telah berhasil diekspansi untuk mencakup 6 unit utama (UP2WI hingga UP2WVI) beserta dengan pemeliharaan satu entitas Unit Induk.

---

## 1. Dokumentasi Perubahan Struktur User & Unit

| **Kode Unit Asli** | **Kode Unit Baru** | **Nama Unit** | **Domain Email Aktif**   | **Status**     |
| ---------------- | ---------------- | ----------- | ----------------------| -------------- |
| INDUK            | INDUK            | Induk       | superadmin / leader / petugas | Dipertahankan |
| *(Baru)*         | UP2W1            | UP2WI       | `user.up2w1@pln.com`    | Ditambahkan  |
| *(Baru)*         | UP2W2            | UP2WII      | `user.up2w2@pln.com`    | Ditambahkan  |
| UPW2             | UP2W3            | UP2WIII     | `user.up2w3@pln.com`    | Update Data  |
| UPW3             | UP2W4            | UP2WIV      | `user.up2w4@pln.com`    | Update Data  |
| *(Baru)*         | UP2W5            | UP2WV       | `user.up2w5@pln.com`    | Ditambahkan  |
| *(Baru)*         | UP2W6            | UP2WVI      | `user.up2w6@pln.com`    | Ditambahkan  |

### Rincian Perubahan Skema:
- Domain alamat email Unit Pengelola diubah secara struktural dari `pln.co.id` menjadi `pln.com` untuk menyeragamkan format login.
- Penamaan Username (untuk field username) disesuaikan dengan format: `user_up2wX`.
- Hak Akses (Role) kepada semua akun ini didelegasikan secara setara sebagai `leader`, artinya akun-akun ini dapat meng-approve Kartu TTD masing-masing dari unitnya secara langsung.
- Sandi bawaan (default password) jika sebelumnya belum diubah adalah: `pln123`.

---

## 2. Deliverable: Script SQL
Skrip SQL statis untuk keperluan eksekusi ulang, migrasi atau validasi manual DB Administrator telah dilampirkan pada sistem proyek dengan nama:
`database/user_management_update.sql`. 
*(Terdapat pula file otomatisasi PHP yang telah dieksekusi `update_users.php` untuk menjalankan update ini secara aman karena berinteraksi dengan hashing library laravel dan Spatie/Permission).*

---

## 3. Laporan Testing & Integrasi Login

Update database telah berhasil diselesaikan secara otomatis pada _server_ tanpa menyakiti *(conflict)* dengan foreign key tabel existing seperti *Users*, *Units*, *Model_Has_Roles*, maupun tabel relasi peralatan di modul lainnya.

**Verifikasi Access & Authentication (100% Passed):**
1. **User Auth Test:** Database internal merespons dengan positif terhadap *attempt* autentikasi menggunakan `user.up2wX@pln.com` dan memvalidasi hashing sandi secara akurat.
2. **Access Rights Test:** Pemeriksaan *Spatie Role Check* menunjukkan model user baru merespons sukses `hasRole('leader') => true`. Ini berarti semua users baru bukan sekadar terdaftar normal, melainkan sudah memegang hak administratif unit operasional.
3. **Module Integrasi Test:** `Unit::where('code', ...)` tersambung sempurna dengan profil user. Pada *dashboard* utama, `totalLeader` dan statistik `totalUsers` ikut terkalkulasi dengan benar (menunjukkan lonjakan partisipan) menandakan modul analitik telah berhasil menyerap struktur user ini secara *real-time*.

> 🔥 **Semua User Aktif dan Dapat Mengakses Sistem Sesuai Wilayah/Unit Masing-masing.**
