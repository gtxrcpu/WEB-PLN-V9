# Panduan Fitur Auto-Filter Universal

## Pendahuluan
Fitur Auto-Filter kini telah diimplementasikan di seluruh aplikasi (semua modul peralatan K3) untuk meningkatkan efisiensi pengguna. Dengan fitur ini, Anda tidak perlu lagi menekan tombol "Submit" atau "Filter" secara manual saat mencari atau menyaring data.

## Modul yang Diperbarui
Pembaruan ini berlaku untuk 7 modul utama berikut:
1. **APAR** (Alat Pemadam Api Ringan)
2. **APAT** (Alat Pemadam Api Tradisional)
3. **APAB** (Alat Pemadam Api Berat)
4. **Fire Alarm**
5. **Box Hydrant**
6. **Rumah Pompa**
7. **P3K**

## Panduan Penggunaan Berdasarkan Role

### 1. Role Petugas (Inspector) P3K, APAR, APAT, APAB, dll.
Petugas di lapangan yang mengakses riwayat inspeksi melalui aplikasi sekarang dapat membedakan data inspeksinya dengan jauh lebih cepat:
*   **Penyaringan Langsung (Dropdown)**: Cukup pilih nilai dari dropdown (seperti memilih status "Approved" atau "Pending"). Halaman akan secara otomatis memuat ulang data di belakang layar.
*   **Pencarian Teks (Input)**: Saat Anda mengetikkan nama pembuat atau approver di kotak teks filter, sistem akan menunggu selama setengah detik (500ms) setelah Anda berhenti mengetik sebelum memproses data. Ini dipastikan agar Anda tidak membuang kuota data untuk setiap huruf yang diketik.
*   **Indikator Proses**: Saat sistem sedang mengambil data baru, layar akan menampilkan animasi loading (Indikator loading) di tengah halaman yang memberitahukan "Memproses Filter Data...". Anda tidak perlu lagi bertanya-tanya apakah sistem terhenti atau memuat data.
*   **URL yang Bisa Dibagikan**: Petugas dapat menyalin URL di bagian atas peramban dan membagikannya ke petugas lain. Jika Anda memfilter status "Pending", tautan yang Anda berikan akan otomatis menampilkan halaman dengan data "Pending" saat petugas lain membukanya.

### 2. Role Leader
*   Pemeriksaan persetujuan (approval) akan lebih cepat karena leader dapat mengganti-ganti status pemeriksaan di halaman mana saja tanpa me-refresh manual.
*   Jika terjadi gangguan jaringan (Network Error) pada saat memfilter persetujuan yang bertumpuk, sistem akan memberikan notifikasi otomatis dan mencoba memuat ulang halaman kembali dengan parameter yang sama.

### 3. Role Admin & Superadmin
*   Sama seperti role lainnya, admin yang bertugas di dashboard dapat memantau riwayat peralatan apa saja tanpa klik yang tidak perlu.
*   Tidak ada tombol filter yang menutupi layar, sehingga desain UI lebih bersih dan modern.

## Rincian Teknis untuk Developer
*   **Javascript**: Menggunakan Vanilla JavaScript `fetch` API (`XMLHttpRequest`) yang diinjeksi pada layout utama `app.blade.php`.
*   **Fallback**: Jika JavaScript gagal (misal koneksi buruk yang ditolak oleh fetch), maka fitur akan fallback ke pemuatan halaman penuh seperti filter konvensional Laravel, namun form Submit disembunyikan secara visual.
*   **Performance**: Menggunakan mekanisme *Debounce* untuk semua input bertipe `text` untuk mencegah server overload akibat pengiriman ketikan tunggal berulang kali. `X-Requested-With` header telah dimodifikasi agar Laravel dapat mengenali request dengan benar.

*Pembaruan diaktifkan pada: {{ \Carbon\Carbon::now()->format('d M Y') }}*
