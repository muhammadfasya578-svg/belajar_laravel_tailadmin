<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_barang',
        'nama_barang',
        'kategori',
        'harga',
        'stok',
        'satuan',
        'deskripsi',
        'status',
    ];

    protected $casts = [
        'harga' => 'decimal:2',
        'stok' => 'integer',
    ];

    public function getFormattedHargaAttribute(): string
    {
        return 'Rp ' . number_format((float) $this->harga, 0, ',', '.');
    }
}
