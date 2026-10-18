<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use Illuminate\Http\Request;

class BarangController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Auto-seed sample data if empty
        if (Barang::count() === 0) {
            \Artisan::call('db:seed', ['--class' => 'BarangSeeder', '--force' => true]);
        }

        $query = Barang::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nama_barang', 'like', "%{$search}%")
                  ->orWhere('kode_barang', 'like', "%{$search}%")
                  ->orWhere('kategori', 'like', "%{$search}%");
            });
        }

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->input('kategori'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $barangs = $query->orderBy('kode_barang', 'asc')->paginate(10)->withQueryString();

        // Summary statistics
        $totalBarang = Barang::count();
        $totalStok = (int) Barang::sum('stok');
        $totalNilai = (float) (Barang::selectRaw('SUM(harga * stok) as total')->value('total') ?? 0);
        $lowStockCount = Barang::where('stok', '<=', 5)->count();
        $kategoriList = Barang::distinct()->whereNotNull('kategori')->pluck('kategori');

        return view('pages.barang.index', [
            'title' => 'Kelola Barang',
            'barangs' => $barangs,
            'totalBarang' => $totalBarang,
            'totalStok' => $totalStok,
            'totalNilai' => $totalNilai,
            'lowStockCount' => $lowStockCount,
            'kategoriList' => $kategoriList,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_barang' => ['required', 'string', 'max:50', 'unique:barangs,kode_barang'],
            'nama_barang' => ['required', 'string', 'max:255'],
            'kategori'    => ['required', 'string', 'max:100'],
            'harga'       => ['required', 'numeric', 'min:0'],
            'stok'        => ['required', 'integer', 'min:0'],
            'satuan'      => ['required', 'string', 'max:50'],
            'deskripsi'   => ['nullable', 'string', 'max:1000'],
            'status'      => ['nullable', 'string', 'max:50'],
        ]);

        if (empty($validated['status'])) {
            if ($validated['stok'] == 0) {
                $validated['status'] = 'Habis';
            } elseif ($validated['stok'] <= 5) {
                $validated['status'] = 'Stok Menipis';
            } else {
                $validated['status'] = 'Tersedia';
            }
        }

        Barang::create($validated);

        return redirect()->route('barang.index')->with('success', 'Barang berhasil ditambahkan!');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Barang $barang)
    {
        $validated = $request->validate([
            'kode_barang' => ['required', 'string', 'max:50', 'unique:barangs,kode_barang,' . $barang->id],
            'nama_barang' => ['required', 'string', 'max:255'],
            'kategori'    => ['required', 'string', 'max:100'],
            'harga'       => ['required', 'numeric', 'min:0'],
            'stok'        => ['required', 'integer', 'min:0'],
            'satuan'      => ['required', 'string', 'max:50'],
            'deskripsi'   => ['nullable', 'string', 'max:1000'],
            'status'      => ['nullable', 'string', 'max:50'],
        ]);

        if (empty($validated['status'])) {
            if ($validated['stok'] == 0) {
                $validated['status'] = 'Habis';
            } elseif ($validated['stok'] <= 5) {
                $validated['status'] = 'Stok Menipis';
            } else {
                $validated['status'] = 'Tersedia';
            }
        }

        $barang->update($validated);

        return redirect()->route('barang.index')->with('success', 'Data barang berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Barang $barang)
    {
        $barang->delete();

        return redirect()->route('barang.index')->with('success', 'Data barang berhasil dihapus!');
    }
}
