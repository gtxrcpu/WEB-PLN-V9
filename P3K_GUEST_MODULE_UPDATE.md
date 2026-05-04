# P3K Guest Module Update - Sama Seperti APAR

## Perubahan yang Dilakukan

Modul P3K untuk guest telah diupdate agar sama persis dengan modul APAR, dengan fitur:
- Tampilan QR code untuk setiap P3K
- 3 button kartu kendali: Pemeriksaan, Pemakaian, dan Stock
- Filter unit dengan dropdown
- Real-time data filtering
- Search functionality

## Files yang Dimodifikasi

### 1. Controller (`app/Http/Controllers/GuestController.php`)

**Method `p3k()`:**
```php
public function p3k(Request $request)
{
    $unitId = $request->get('unit_id');
    
    // Get all units for dropdown
    $units = Unit::where('is_active', true)
        ->orderBy('name')
        ->get();
    
    // Build query with unit filter
    $query = P3k::with(['unit', 'kartuP3kPemeriksaans', 'kartuP3kPemakaians', 'kartuP3kStocks']);
    
    if ($unitId) {
        $query->where('unit_id', $unitId);
    }
    
    $p3ks = $query->orderBy('serial_no')->paginate(20);
    
    // Get selected unit info
    $selectedUnit = $unitId ? Unit::find($unitId) : null;
    
    return view('guest.p3k.index', compact('p3ks', 'units', 'selectedUnit'));
}
```

**Method `p3kRiwayat()`:**
```php
public function p3kRiwayat(P3k $p3k, Request $request)
{
    $jenis = $request->get('jenis', 'pemeriksaan'); // pemeriksaan, pemakaian, stock
    
    // Load relationships for the equipment
    $p3k->load(['unit']);

    // Get inspection history based on jenis
    $riwayatInspeksi = collect();
    
    switch ($jenis) {
        case 'pemeriksaan':
            $riwayatInspeksi = $p3k->kartuP3kPemeriksaans()
                ->orderBy('tgl_periksa', 'desc')
                ->get()
                ->map(function ($kartu) {
                    $kartu->makeHidden(['ttd_petugas', 'ttd_penyelia', 'approved_by', 'approved_at', 'rejected_by', 'rejected_at', 'rejection_reason']);
                    return $kartu;
                });
            break;
            
        case 'pemakaian':
            $riwayatInspeksi = $p3k->kartuP3kPemakaians()
                ->orderBy('tgl_pemakaian', 'desc')
                ->get()
                ->map(function ($kartu) {
                    $kartu->makeHidden(['ttd_petugas', 'ttd_penyelia', 'approved_by', 'approved_at', 'rejected_by', 'rejected_at', 'rejection_reason']);
                    return $kartu;
                });
            break;
            
        case 'stock':
            $riwayatInspeksi = $p3k->kartuP3kStocks()
                ->orderBy('tgl_periksa', 'desc')
                ->get()
                ->map(function ($kartu) {
                    $kartu->makeHidden(['ttd_petugas', 'ttd_penyelia', 'approved_by', 'approved_at', 'rejected_by', 'rejected_at', 'rejection_reason']);
                    return $kartu;
                });
            break;
    }

    return view('guest.p3k.riwayat', compact('p3k', 'riwayatInspeksi', 'jenis'));
}
```

### 2. View (`resources/views/guest/p3k/index.blade.php`)

**Struktur Baru:**
- Header dengan stats (Total P3K, Kondisi Baik, Rusak)
- Unit filter dropdown menggunakan component `<x-guest.unit-filter>`
- Search box terintegrasi
- Grid layout dengan card untuk setiap P3K
- QR code ditampilkan di setiap card
- 3 button kartu kendali:
  - **Pemeriksaan** (hijau) - untuk kartu pemeriksaan kotak P3K
  - **Pemakaian** (biru) - untuk kartu pemakaian P3K
  - **Stock** (ungu) - untuk kartu stock P3K
- Badge counter menunjukkan jumlah kartu untuk setiap jenis

**Fitur Card P3K:**
```blade
{{-- Action Buttons - Kartu Kendali --}}
<div class="flex flex-col gap-2">
    <a href="{{ route('guest.p3k.riwayat', ['p3k' => $p3k, 'jenis' => 'pemeriksaan']) }}"
       class="...">
        <svg>...</svg>
        <span>Pemeriksaan</span>
        @php $count = $p3k->kartuP3kPemeriksaans->count(); @endphp
        @if($count > 0)
            <span class="...">{{ $count }}</span>
        @endif
    </a>

    <a href="{{ route('guest.p3k.riwayat', ['p3k' => $p3k, 'jenis' => 'pemakaian']) }}"
       class="...">
        <svg>...</svg>
        <span>Pemakaian</span>
        @php $count = $p3k->kartuP3kPemakaians->count(); @endphp
        @if($count > 0)
            <span class="...">{{ $count }}</span>
        @endif
    </a>

    <a href="{{ route('guest.p3k.riwayat', ['p3k' => $p3k, 'jenis' => 'stock']) }}"
       class="...">
        <svg>...</svg>
        <span>Stock</span>
        @php $count = $p3k->kartuP3kStocks->count(); @endphp
        @if($count > 0)
            <span class="...">{{ $count }}</span>
        @endif
    </a>
</div>
```

### 3. Routes (`routes/web.php`)

Route sudah ada dan tidak perlu diubah:
```php
Route::get('/p3k/{p3k}/riwayat', [\App\Http\Controllers\GuestController::class, 'p3kRiwayat'])->name('p3k.riwayat');
```

Route ini sekarang menerima parameter `jenis` via query string:
- `/guest/p3k/{id}/riwayat?jenis=pemeriksaan`
- `/guest/p3k/{id}/riwayat?jenis=pemakaian`
- `/guest/p3k/{id}/riwayat?jenis=stock`

## Relasi Model yang Digunakan

Model P3K harus memiliki relasi ke 3 jenis kartu:

```php
// app/Models/P3k.php

public function kartuP3kPemeriksaans()
{
    return $this->hasMany(KartuP3kPemeriksaan::class);
}

public function kartuP3kPemakaians()
{
    return $this->hasMany(KartuP3kPemakaian::class);
}

public function kartuP3kStocks()
{
    return $this->hasMany(KartuP3kStock::class);
}
```

## Fitur yang Sama dengan APAR

### 1. QR Code Display
- Setiap P3K menampilkan QR code
- QR code dapat di-scan untuk akses cepat
- Styling sama dengan APAR (rounded, shadow, ring)

### 2. Unit Filter
- Dropdown untuk memilih unit
- Real-time filtering saat unit dipilih
- Badge info menunjukkan unit yang dipilih
- Button "Tampilkan Semua" untuk clear filter

### 3. Search Functionality
- Search by serial number
- Search by location code
- Real-time filtering

### 4. Stats Cards
- Total P3K
- Kondisi Baik
- Rusak

### 5. Card Layout
- Grid responsive (1 col mobile, 2 col tablet, 3 col desktop)
- Status badge (Baik/Rusak)
- Info tipe dan unit
- QR code section
- Action buttons

## Perbedaan dengan APAR

### Button Kartu Kendali
**APAR:** 1 button "Lihat Riwayat"
**P3K:** 3 button berbeda:
1. **Pemeriksaan** (hijau) - `kartuP3kPemeriksaans`
2. **Pemakaian** (biru) - `kartuP3kPemakaians`
3. **Stock** (ungu) - `kartuP3kStocks`

### Warna Tema
**APAR:** Blue/Cyan gradient
**P3K:** Emerald/Green gradient

### Stats
**APAR:** Total, Baik, Isi Ulang, Rusak (4 cards)
**P3K:** Total, Baik, Rusak (3 cards)

## Testing Checklist

- [ ] Akses `/guest/p3k` menampilkan list P3K dengan QR code
- [ ] Filter unit berfungsi dengan benar
- [ ] Search berfungsi untuk serial number dan location
- [ ] Stats cards menampilkan angka yang benar
- [ ] QR code dapat di-scan
- [ ] Button "Pemeriksaan" membuka riwayat pemeriksaan
- [ ] Button "Pemakaian" membuka riwayat pemakaian
- [ ] Button "Stock" membuka riwayat stock
- [ ] Badge counter menunjukkan jumlah kartu yang benar
- [ ] Pagination berfungsi
- [ ] Responsive di mobile, tablet, dan desktop
- [ ] Selected unit info ditampilkan dengan benar
- [ ] Button "Tampilkan Semua" clear filter unit

## Next Steps

1. **Update P3K Riwayat View** - Buat view untuk menampilkan riwayat berdasarkan jenis
2. **Test dengan Data Real** - Pastikan semua relasi model berfungsi
3. **Update Old P3K Routes** - Hapus route lama yang tidak digunakan (pilih-jenis, pilih-lokasi, by-lokasi)
4. **Documentation** - Update user guide untuk guest access P3K

## Migration dari Struktur Lama

Struktur lama P3K menggunakan:
- `/guest/p3k` → Pilih jenis kartu
- `/guest/p3k/pilih-lokasi?jenis=xxx` → Pilih lokasi
- `/guest/p3k/by-lokasi?jenis=xxx&lokasi=xxx` → List P3K

Struktur baru:
- `/guest/p3k` → Langsung list semua P3K dengan QR
- `/guest/p3k?unit_id=xxx` → Filter by unit
- `/guest/p3k/{id}/riwayat?jenis=pemeriksaan` → Riwayat pemeriksaan
- `/guest/p3k/{id}/riwayat?jenis=pemakaian` → Riwayat pemakaian
- `/guest/p3k/{id}/riwayat?jenis=stock` → Riwayat stock

## Backup

File lama disimpan di:
- `resources/views/guest/p3k/index-old.blade.php`

Jika perlu rollback, jalankan:
```bash
mv resources/views/guest/p3k/index.blade.php resources/views/guest/p3k/index-new.blade.php
mv resources/views/guest/p3k/index-old.blade.php resources/views/guest/p3k/index.blade.php
```