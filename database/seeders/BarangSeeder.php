<?php

namespace Database\Seeders;

use App\Models\Barang;
use Illuminate\Database\Seeder;

class BarangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            [
                'kode_barang' => 'BRG-001',
                'nama_barang' => 'Laptop ASUS ROG Zephyrus G14',
                'kategori'    => 'Elektronik',
                'harga'       => 28500000,
                'stok'        => 12,
                'satuan'      => 'Unit',
                'deskripsi'   => 'Laptop gaming tipis berperforma tinggi dengan AMD Ryzen 9 dan RTX 4060.',
                'status'      => 'Tersedia',
            ],
            [
                'kode_barang' => 'BRG-002',
                'nama_barang' => 'Monitor Gaming LG UltraGear 27"',
                'kategori'    => 'Elektronik',
                'harga'       => 4750000,
                'stok'        => 25,
                'satuan'      => 'Unit',
                'deskripsi'   => 'Monitor gaming Nano IPS QHD 165Hz dengan response time 1ms.',
                'status'      => 'Tersedia',
            ],
            [
                'kode_barang' => 'BRG-003',
                'nama_barang' => 'Keyboard Mechanical Keychron K2 V2',
                'kategori'    => 'Aksesoris',
                'harga'       => 1450000,
                'stok'        => 4,
                'satuan'      => 'Unit',
                'deskripsi'   => 'Keyboard mechanical wireless 75% layout dengan Gateron Brown switch.',
                'status'      => 'Stok Menipis',
            ],
            [
                'kode_barang' => 'BRG-004',
                'nama_barang' => 'Mouse Wireless Logitech MX Master 3S',
                'kategori'    => 'Aksesoris',
                'harga'       => 1650000,
                'stok'        => 18,
                'satuan'      => 'Unit',
                'deskripsi'   => 'Mouse ergonomis performa tinggi dengan sensor 8K DPI dan Quiet Clicks.',
                'status'      => 'Tersedia',
            ],
        ];

        foreach ($items as $item) {
            Barang::updateOrCreate(
                ['kode_barang' => $item['kode_barang']],
                $item
            );
        }
    }
}
