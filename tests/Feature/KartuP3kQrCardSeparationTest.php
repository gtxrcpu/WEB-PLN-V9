<?php

namespace Tests\Feature;

use App\Http\Controllers\KartuP3kController;
use App\Models\KartuP3kPemeriksaan;
use App\Models\KartuP3kPemakaian;
use App\Models\KartuP3kStock;
use App\Models\P3k;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Unit Testing: Pemisahan Kode QR Card P3K
 *
 * Memastikan bahwa setiap jenis transaksi P3K menggunakan format
 * kode yang unik dan tidak saling tumpang tindih:
 *
 *   Pemeriksaan : P3K-PMKS-{UNIT}-{NNN}
 *   Pemakaian   : P3K-PMK-{UNIT}-{NNN}
 *   Stock       : P3K-STK-{UNIT}-{NNN}
 */
class KartuP3kQrCardSeparationTest extends TestCase
{
    use RefreshDatabase;

    protected KartuP3kController $controller;
    protected Unit $unit;
    protected User $user;
    protected P3k $p3k;

    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = new KartuP3kController();

        $this->unit = Unit::create([
            'name' => 'Halaman Listrik Timur',
            'code' => 'HLT',
        ]);

        $this->user = User::factory()->create([
            'unit_id'  => $this->unit->id,
            'position' => 'petugas',
        ]);

        $this->p3k = P3k::create([
            'user_id'       => $this->user->id,
            'unit_id'       => $this->unit->id,
            'name'          => 'P3K Test',
            'serial_no'     => 'P3K.001',
            'barcode'       => 'P3K P3K.001',
            'location_code' => 'Ruang A',
            'status'        => 'BAIK',
        ]);
    }

    // =========================================================================
    // 1. FORMAT KODE PER JENIS
    // =========================================================================

    #[Test]
    public function kode_pemeriksaan_menggunakan_prefix_P3K_PMKS(): void
    {
        $kode = $this->controller->generateNomorKartu('pemeriksaan', $this->unit->id);

        $this->assertStringStartsWith('P3K-PMKS-', $kode,
            "Kode pemeriksaan harus dimulai dengan 'P3K-PMKS-', bukan '{$kode}'"
        );
        $this->assertMatchesRegularExpression(
            '/^P3K-PMKS-[A-Z]{1,3}-\d{3}$/',
            $kode,
            "Format kode pemeriksaan harus P3K-PMKS-{UNIT}-{NNN}"
        );
    }

    #[Test]
    public function kode_pemakaian_menggunakan_prefix_P3K_PMK(): void
    {
        $kode = $this->controller->generateNomorKartu('pemakaian', $this->unit->id);

        $this->assertStringStartsWith('P3K-PMK-', $kode,
            "Kode pemakaian harus dimulai dengan 'P3K-PMK-', bukan '{$kode}'"
        );
        $this->assertMatchesRegularExpression(
            '/^P3K-PMK-[A-Z]{1,3}-\d{3}$/',
            $kode,
            "Format kode pemakaian harus P3K-PMK-{UNIT}-{NNN}"
        );
    }

    #[Test]
    public function kode_stock_menggunakan_prefix_P3K_STK(): void
    {
        $kode = $this->controller->generateNomorKartu('stock', $this->unit->id);

        $this->assertStringStartsWith('P3K-STK-', $kode,
            "Kode stock harus dimulai dengan 'P3K-STK-', bukan '{$kode}'"
        );
        $this->assertMatchesRegularExpression(
            '/^P3K-STK-[A-Z]{1,3}-\d{3}$/',
            $kode,
            "Format kode stock harus P3K-STK-{UNIT}-{NNN}"
        );
    }

    // =========================================================================
    // 2. TIDAK ADA TUMPANG TINDIH ANTAR JENIS
    // =========================================================================

    #[Test]
    public function kode_pemeriksaan_tidak_sama_dengan_kode_pemakaian(): void
    {
        $kodePemeriksaan = $this->controller->generateNomorKartu('pemeriksaan', $this->unit->id);
        $kodePemakaian   = $this->controller->generateNomorKartu('pemakaian', $this->unit->id);

        $this->assertNotEquals($kodePemeriksaan, $kodePemakaian,
            "Kode pemeriksaan dan pemakaian tidak boleh sama"
        );
    }

    #[Test]
    public function kode_pemeriksaan_tidak_diawali_prefix_pemakaian(): void
    {
        // Pemakaian pakai P3K-PMK, Pemeriksaan pakai P3K-PMKS
        // P3K-PMKS tidak boleh terdeteksi sebagai P3K-PMK
        $kode = $this->controller->generateNomorKartu('pemeriksaan', $this->unit->id);

        // Kode PMKS dimulai dengan "P3K-PMKS-HLT-001"
        // Kode PMK dimulai dengan "P3K-PMK-HLT-001"
        // Keduanya sama-sama diawali "P3K-PMK" tapi PMKS lebih spesifik (4 char)
        // Pastikan kode pemeriksaan BUKAN format pemakaian (tidak diakhiri pattern PMK-UNIT-NNN tanpa S)
        $this->assertDoesNotMatchRegularExpression(
            '/^P3K-PMK-[A-Z]{1,3}-\d{3}$/',
            $kode,
            "Kode pemeriksaan (P3K-PMKS) tidak boleh cocok dengan pola pemakaian (P3K-PMK-UNIT-NNN)"
        );
    }

    #[Test]
    public function ketiga_jenis_kode_memiliki_prefix_berbeda(): void
    {
        $kodePemeriksaan = $this->controller->generateNomorKartu('pemeriksaan', $this->unit->id);
        $kodePemakaian   = $this->controller->generateNomorKartu('pemakaian', $this->unit->id);
        $kodeStock       = $this->controller->generateNomorKartu('stock', $this->unit->id);

        // Semua kode harus berbeda satu sama lain
        $this->assertNotEquals($kodePemeriksaan, $kodePemakaian);
        $this->assertNotEquals($kodePemeriksaan, $kodeStock);
        $this->assertNotEquals($kodePemakaian, $kodeStock);

        // Validasi prefix masing-masing secara spesifik
        $this->assertStringStartsWith('P3K-PMKS-', $kodePemeriksaan);
        $this->assertStringStartsWith('P3K-PMK-', $kodePemakaian);
        $this->assertStringStartsWith('P3K-STK-', $kodeStock);

        // Pastikan pemakaian bukan PMKS (subset-check)
        $this->assertStringNotContainsString('P3K-PMKS', $kodePemakaian);
        $this->assertStringNotContainsString('P3K-PMKS', $kodeStock);
    }

    // =========================================================================
    // 3. INKREMENT NOMOR URUT
    // =========================================================================

    #[Test]
    public function nomor_urut_pemeriksaan_diinkrement_per_unit(): void
    {
        // Buat record pertama secara manual
        KartuP3kPemeriksaan::create([
            'nomor_kartu'      => 'P3K-PMKS-HLT-001',
            'unit_id'          => $this->unit->id,
            'p3k_id'           => $this->p3k->id,
            'user_id'          => $this->user->id,
            'unit_kerja'       => 'Unit Test',
            'tgl_periksa'      => now()->toDateString(),
            'bulan_tahun'      => now()->format('Y-m'),
            'petugas'          => 'Petugas Test',
            'revisi'           => '00',
            'inspection_items' => [],
        ]);

        $kodeKedua = $this->controller->generateNomorKartu('pemeriksaan', $this->unit->id);

        $this->assertEquals('P3K-PMKS-HLT-002', $kodeKedua,
            "Kode kedua harus P3K-PMKS-HLT-002, bukan '{$kodeKedua}'"
        );
    }

    #[Test]
    public function nomor_urut_pemakaian_diinkrement_per_unit(): void
    {
        KartuP3kPemakaian::create([
            'nomor_kartu' => 'P3K-PMK-HLT-001',
            'unit_id'     => $this->unit->id,
            'p3k_id'      => $this->p3k->id,
            'user_id'     => $this->user->id,
            'bulan'       => now()->format('Y-m'),
            'lokasi'      => 'Test Lokasi',
            'revisi'      => '00',
        ]);

        $kodeKedua = $this->controller->generateNomorKartu('pemakaian', $this->unit->id);

        $this->assertEquals('P3K-PMK-HLT-002', $kodeKedua,
            "Kode kedua harus P3K-PMK-HLT-002, bukan '{$kodeKedua}'"
        );
    }

    #[Test]
    public function nomor_urut_stock_diinkrement_per_unit(): void
    {
        KartuP3kStock::create([
            'nomor_kartu' => 'P3K-STK-HLT-001',
            'unit_id'     => $this->unit->id,
            'p3k_id'      => $this->p3k->id,
            'user_id'     => $this->user->id,
            'stock_items' => [],
            'kesimpulan'  => 'Baik',
            'tgl_periksa' => now()->toDateString(),
            'petugas'     => 'Petugas Test',
        ]);

        $kodeKedua = $this->controller->generateNomorKartu('stock', $this->unit->id);

        $this->assertEquals('P3K-STK-HLT-002', $kodeKedua,
            "Kode kedua harus P3K-STK-HLT-002, bukan '{$kodeKedua}'"
        );
    }

    // =========================================================================
    // 4. ISOLASI PER UNIT
    // =========================================================================

    #[Test]
    public function kode_antar_unit_tidak_saling_mempengaruhi(): void
    {
        $unit2 = Unit::create(['name' => 'Gardu Barat', 'code' => 'GBR']);

        // Buat record untuk unit pertama (HLT)
        KartuP3kPemeriksaan::create([
            'nomor_kartu'      => 'P3K-PMKS-HLT-001',
            'unit_id'          => $this->unit->id,
            'p3k_id'           => $this->p3k->id,
            'user_id'          => $this->user->id,
            'unit_kerja'       => 'Unit HLT',
            'tgl_periksa'      => now()->toDateString(),
            'bulan_tahun'      => now()->format('Y-m'),
            'petugas'          => 'Petugas Test',
            'revisi'           => '00',
            'inspection_items' => [],
        ]);

        // Unit kedua harus mulai dari 001, bukan 002
        $kodeUnit2 = $this->controller->generateNomorKartu('pemeriksaan', $unit2->id);

        $this->assertEquals('P3K-PMKS-GBR-001', $kodeUnit2,
            "Unit berbeda harus memulai nomor urut dari 001 secara independen"
        );
    }

    #[Test]
    public function unit_null_menggunakan_kode_GEN(): void
    {
        $kodePemeriksaan = $this->controller->generateNomorKartu('pemeriksaan', null);
        $kodePemakaian   = $this->controller->generateNomorKartu('pemakaian', null);
        $kodeStock       = $this->controller->generateNomorKartu('stock', null);

        $this->assertStringContainsString('-GEN-', $kodePemeriksaan);
        $this->assertStringContainsString('-GEN-', $kodePemakaian);
        $this->assertStringContainsString('-GEN-', $kodeStock);
    }

    // =========================================================================
    // 5. TIDAK ADA DUPLIKASI ANTAR TABEL
    // =========================================================================

    #[Test]
    public function kode_pemeriksaan_tidak_bisa_duplikat_di_tabel_yang_sama(): void
    {
        KartuP3kPemeriksaan::create([
            'nomor_kartu'      => 'P3K-PMKS-HLT-001',
            'unit_id'          => $this->unit->id,
            'p3k_id'           => $this->p3k->id,
            'user_id'          => $this->user->id,
            'unit_kerja'       => 'Unit Test',
            'tgl_periksa'      => now()->toDateString(),
            'bulan_tahun'      => now()->format('Y-m'),
            'petugas'          => 'Petugas Test',
            'revisi'           => '00',
            'inspection_items' => [],
        ]);

        // Mencoba insert duplikat harus melempar exception database
        $this->expectException(\Illuminate\Database\QueryException::class);

        KartuP3kPemeriksaan::create([
            'nomor_kartu'      => 'P3K-PMKS-HLT-001', // duplikat!
            'unit_id'          => $this->unit->id,
            'p3k_id'           => $this->p3k->id,
            'user_id'          => $this->user->id,
            'unit_kerja'       => 'Unit Test 2',
            'tgl_periksa'      => now()->toDateString(),
            'bulan_tahun'      => now()->format('Y-m'),
            'petugas'          => 'Petugas Test 2',
            'revisi'           => '01',
            'inspection_items' => [],
        ]);
    }

    #[Test]
    public function kode_pemeriksaan_dan_pemakaian_bisa_punya_nomor_urut_sama_karena_prefix_berbeda(): void
    {
        // P3K-PMKS-HLT-001 dan P3K-PMK-HLT-001 boleh ada bersamaan
        // karena berbeda tabel DAN berbeda prefix
        KartuP3kPemeriksaan::create([
            'nomor_kartu'      => 'P3K-PMKS-HLT-001',
            'unit_id'          => $this->unit->id,
            'p3k_id'           => $this->p3k->id,
            'user_id'          => $this->user->id,
            'unit_kerja'       => 'Unit Test',
            'tgl_periksa'      => now()->toDateString(),
            'bulan_tahun'      => now()->format('Y-m'),
            'petugas'          => 'Petugas Test',
            'revisi'           => '00',
            'inspection_items' => [],
        ]);

        KartuP3kPemakaian::create([
            'nomor_kartu' => 'P3K-PMK-HLT-001',
            'unit_id'     => $this->unit->id,
            'p3k_id'      => $this->p3k->id,
            'user_id'     => $this->user->id,
            'bulan'       => now()->format('Y-m'),
            'lokasi'      => 'Test Lokasi',
            'revisi'      => '00',
        ]);

        $this->assertDatabaseHas('kartu_p3k_pemeriksaan', ['nomor_kartu' => 'P3K-PMKS-HLT-001']);
        $this->assertDatabaseHas('kartu_p3k_pemakaian',   ['nomor_kartu' => 'P3K-PMK-HLT-001']);
    }

    // =========================================================================
    // 6. VALIDASI JENIS TIDAK VALID
    // =========================================================================

    #[Test]
    public function generate_nomor_kartu_dengan_jenis_tidak_valid_melempar_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/Jenis kartu tidak dikenal/');

        $this->controller->generateNomorKartu('tidak_valid', $this->unit->id);
    }

    // =========================================================================
    // 7. STORE VIA HTTP (integrasi lengkap)
    // =========================================================================

    #[Test]
    public function store_pemeriksaan_menghasilkan_kode_dengan_prefix_P3K_PMKS(): void
    {
        $this->actingAs($this->user);

        $response = $this->post(route('p3k.kartu.store'), [
            'jenis'       => 'pemeriksaan',
            'p3k_id'      => $this->p3k->id,
            'unit_kerja'  => 'Unit HLT',
            'tgl_periksa' => now()->toDateString(),
            'bulan_tahun' => now()->format('Y-m'),
            'petugas'     => 'Petugas Test',
            'items'       => [
                [
                    'nama_barang'    => 'Plester',
                    'satuan'         => 'buah',
                    'jumlah'         => 5,
                    'fisik_visual'   => 'BAIK',
                    'tgl_kadaluarsa' => null,
                    'catatan'        => null,
                ],
            ],
        ]);

        $response->assertRedirect(route('p3k.list-by-jenis', 'pemeriksaan'));

        $kartu = KartuP3kPemeriksaan::latest()->first();
        $this->assertNotNull($kartu, 'Kartu pemeriksaan harus tersimpan');
        $this->assertStringStartsWith('P3K-PMKS-', $kartu->nomor_kartu,
            "nomor_kartu pemeriksaan harus dimulai dengan P3K-PMKS-, bukan '{$kartu->nomor_kartu}'"
        );
    }

    #[Test]
    public function store_pemakaian_menghasilkan_kode_dengan_prefix_P3K_PMK(): void
    {
        $this->actingAs($this->user);

        $response = $this->post(route('p3k.kartu.store'), [
            'jenis'   => 'pemakaian',
            'p3k_id'  => $this->p3k->id,
            'bulan'   => now()->format('Y-m'),
            'lokasi'  => 'Ruang A',
            'entries' => [
                [
                    'nama'      => 'Budi',
                    'item'      => 'Plester',
                    'jumlah'    => 2,
                    'keperluan' => 'Luka kecil',
                    'tanggal'   => now()->toDateString(),
                ],
            ],
        ]);

        $response->assertRedirect(route('p3k.list-by-jenis', 'pemakaian'));

        $kartu = KartuP3kPemakaian::latest()->first();
        $this->assertNotNull($kartu, 'Kartu pemakaian harus tersimpan');
        $this->assertStringStartsWith('P3K-PMK-', $kartu->nomor_kartu,
            "nomor_kartu pemakaian harus dimulai dengan P3K-PMK-, bukan '{$kartu->nomor_kartu}'"
        );
        // Pastikan bukan prefix pemeriksaan
        $this->assertDoesNotMatchRegularExpression(
            '/^P3K-PMKS-/',
            $kartu->nomor_kartu,
            "nomor_kartu pemakaian tidak boleh menggunakan prefix pemeriksaan P3K-PMKS-"
        );
    }

    #[Test]
    public function store_stock_menghasilkan_kode_dengan_prefix_P3K_STK(): void
    {
        $this->actingAs($this->user);

        $response = $this->post(route('p3k.kartu.store'), [
            'jenis'       => 'stock',
            'p3k_id'      => $this->p3k->id,
            'stock_items' => [['nama' => 'Plester', 'jumlah' => 10, 'satuan' => 'buah']],
            'kesimpulan'  => 'Baik',
            'tgl_periksa' => now()->toDateString(),
            'petugas'     => 'Petugas Test',
        ]);

        $response->assertRedirect(route('p3k.list-by-jenis', 'stock'));

        $kartu = KartuP3kStock::latest()->first();
        $this->assertNotNull($kartu, 'Kartu stock harus tersimpan');
        $this->assertStringStartsWith('P3K-STK-', $kartu->nomor_kartu,
            "nomor_kartu stock harus dimulai dengan P3K-STK-, bukan '{$kartu->nomor_kartu}'"
        );
    }

    #[Test]
    public function tiga_jenis_kartu_yang_disimpan_berurutan_tidak_menghasilkan_kode_sama(): void
    {
        $this->actingAs($this->user);

        // Simpan satu kartu dari setiap jenis
        $this->post(route('p3k.kartu.store'), [
            'jenis'       => 'stock',
            'p3k_id'      => $this->p3k->id,
            'stock_items' => [['nama' => 'Plester', 'jumlah' => 10, 'satuan' => 'buah']],
            'kesimpulan'  => 'Baik',
            'tgl_periksa' => now()->toDateString(),
            'petugas'     => 'Petugas Test',
        ]);

        $this->post(route('p3k.kartu.store'), [
            'jenis'   => 'pemakaian',
            'p3k_id'  => $this->p3k->id,
            'bulan'   => now()->format('Y-m'),
            'lokasi'  => 'Ruang A',
            'entries' => [['nama' => 'Budi', 'item' => 'Plester', 'jumlah' => 1, 'keperluan' => 'Luka', 'tanggal' => now()->toDateString()]],
        ]);

        $this->post(route('p3k.kartu.store'), [
            'jenis'       => 'pemeriksaan',
            'p3k_id'      => $this->p3k->id,
            'unit_kerja'  => 'Unit HLT',
            'tgl_periksa' => now()->toDateString(),
            'bulan_tahun' => now()->format('Y-m'),
            'petugas'     => 'Petugas Test',
            'items'       => [['nama_barang' => 'Plester', 'satuan' => 'buah', 'jumlah' => 5, 'fisik_visual' => 'BAIK', 'tgl_kadaluarsa' => null, 'catatan' => null]],
        ]);

        $kodeStock       = KartuP3kStock::latest()->value('nomor_kartu');
        $kodePemakaian   = KartuP3kPemakaian::latest()->value('nomor_kartu');
        $kodePemeriksaan = KartuP3kPemeriksaan::latest()->value('nomor_kartu');

        // Semua kode harus berbeda
        $this->assertNotEquals($kodeStock, $kodePemakaian);
        $this->assertNotEquals($kodeStock, $kodePemeriksaan);
        $this->assertNotEquals($kodePemakaian, $kodePemeriksaan);

        // Validasi prefix masing-masing
        $this->assertStringStartsWith('P3K-STK-',  $kodeStock);
        $this->assertStringStartsWith('P3K-PMK-',  $kodePemakaian);
        $this->assertStringStartsWith('P3K-PMKS-', $kodePemeriksaan);
    }

    // =========================================================================
    // 8. BACKWARD COMPATIBILITY
    // =========================================================================

    #[Test]
    public function kode_lama_format_PKI_tidak_mempengaruhi_inkrement_pemakaian_baru(): void
    {
        // Simulasikan data lama dengan prefix P3K-PKI (format sebelumnya)
        KartuP3kPemakaian::create([
            'nomor_kartu' => 'P3K-PKI-HLT-001', // format lama
            'unit_id'     => $this->unit->id,
            'p3k_id'      => $this->p3k->id,
            'user_id'     => $this->user->id,
            'bulan'       => now()->format('Y-m'),
            'lokasi'      => 'Test',
            'revisi'      => '00',
        ]);

        // Kode baru harus mulai dari 001 dengan prefix baru P3K-PMK
        $kode = $this->controller->generateNomorKartu('pemakaian', $this->unit->id);

        $this->assertStringStartsWith('P3K-PMK-HLT-', $kode);
        $this->assertEquals('P3K-PMK-HLT-001', $kode,
            "Kode dengan format lama P3K-PKI tidak boleh mempengaruhi inkrement format baru P3K-PMK"
        );
    }
}
