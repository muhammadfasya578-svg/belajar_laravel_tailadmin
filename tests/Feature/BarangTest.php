<?php

use App\Models\Barang;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(Database\Seeders\BarangSeeder::class);
});

test('halaman kelola barang dapat diakses dan menampilkan 4 data', function () {
    $response = $this->get('/barang');

    $response->assertStatus(200);
    $response->assertSee('Kelola Barang');
    $response->assertSee('BRG-001');
    $response->assertSee('BRG-002');
    $response->assertSee('BRG-003');
    $response->assertSee('BRG-004');
});

test('dapat menambahkan barang baru', function () {
    $response = $this->post('/barang', [
        'kode_barang' => 'BRG-005',
        'nama_barang' => 'Headset SteelSeries Arctis Nova 7',
        'kategori'    => 'Aksesoris',
        'harga'       => 2750000,
        'stok'        => 10,
        'satuan'      => 'Unit',
        'status'      => 'Tersedia',
        'deskripsi'   => 'Wireless multi-platform gaming headset',
    ]);

    $response->assertRedirect('/barang');
    $this->assertDatabaseHas('barangs', [
        'kode_barang' => 'BRG-005',
        'nama_barang' => 'Headset SteelSeries Arctis Nova 7',
    ]);
});

test('dapat memperbarui data barang', function () {
    $barang = Barang::where('kode_barang', 'BRG-001')->first();

    $response = $this->put("/barang/{$barang->id}", [
        'kode_barang' => 'BRG-001',
        'nama_barang' => 'Laptop ASUS ROG Zephyrus G14 (2026 Edition)',
        'kategori'    => 'Elektronik',
        'harga'       => 29999000,
        'stok'        => 15,
        'satuan'      => 'Unit',
        'status'      => 'Tersedia',
    ]);

    $response->assertRedirect('/barang');
    $this->assertDatabaseHas('barangs', [
        'kode_barang' => 'BRG-001',
        'nama_barang' => 'Laptop ASUS ROG Zephyrus G14 (2026 Edition)',
    ]);
});

test('dapat menghapus data barang', function () {
    $barang = Barang::where('kode_barang', 'BRG-004')->firstOrFail();

    $response = $this->delete("/barang/{$barang->id}");
    $response->assertRedirect('/barang');
    $this->assertDatabaseMissing('barangs', [
        'id' => $barang->id,
    ]);
});
