@extends('layouts.app')

@section('content')
<div x-data="{
    openCreateModal: {{ (isset($errors) && $errors->any() && !old('_method')) ? 'true' : 'false' }},
    openEditModal: {{ (isset($errors) && $errors->any() && old('_method') === 'PUT') ? 'true' : 'false' }},
    openDeleteModal: false,
    activeItem: {
        id: '{{ old('id', '') }}',
        kode_barang: '{{ old('kode_barang', '') }}',
        nama_barang: '{{ old('nama_barang', '') }}',
        kategori: '{{ old('kategori', '') }}',
        harga: '{{ old('harga', '') }}',
        stok: '{{ old('stok', '') }}',
        satuan: '{{ old('satuan', 'Unit') }}',
        status: '{{ old('status', 'Tersedia') }}',
        deskripsi: '{{ old('deskripsi', '') }}'
    },
    editItem(item) {
        this.activeItem = { ...item };
        this.openEditModal = true;
    },
    confirmDelete(item) {
        this.activeItem = { ...item };
        this.openDeleteModal = true;
    }
}" class="space-y-6">

    {{-- Breadcrumb --}}
    <x-common.page-breadcrumb pageTitle="Kelola Barang" />

    {{-- Alert Flash Message --}}
    @if (session('success'))
        <div class="flex items-center justify-between rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-800 dark:border-green-500/30 dark:bg-green-500/10 dark:text-green-300">
            <div class="flex items-center gap-3">
                <x-svg.check-circle class="size-5 shrink-0 text-green-500 dark:text-green-400" />
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-white">
                <x-svg.x class="size-4" />
            </button>
        </div>
    @endif

    @if (isset($errors) && $errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300">
            <div class="flex items-center gap-2 font-medium">
                <x-svg.alert class="size-5 shrink-0 text-red-500 dark:text-red-400" />
                <span>Terdapat kesalahan pada input:</span>
            </div>
            <ul class="mt-2 list-inside list-disc space-y-1 ps-2 text-xs">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Statistics Metric Cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 md:gap-6">
        <!-- Total Barang -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 md:p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-50 text-brand-500 dark:bg-brand-500/10 dark:text-brand-400">
                <x-svg.box class="size-6" />
            </div>
            <div class="mt-4">
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Jenis Barang</span>
                <h4 class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90">{{ $totalBarang }} <span class="text-sm font-normal text-gray-500">Item</span></h4>
            </div>
        </div>

        <!-- Total Stok -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 md:p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50 text-blue-500 dark:bg-blue-500/10 dark:text-blue-400">
                <x-svg.database class="size-6" />
            </div>
            <div class="mt-4">
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Stok Fisik</span>
                <h4 class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90">{{ number_format($totalStok, 0, ',', '.') }} <span class="text-sm font-normal text-gray-500">Unit</span></h4>
            </div>
        </div>

        <!-- Total Nilai Aset -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 md:p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-50 text-emerald-500 dark:bg-emerald-500/10 dark:text-emerald-400">
                <x-svg.task class="size-6" />
            </div>
            <div class="mt-4">
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Nilai Inventaris</span>
                <h4 class="mt-1 text-xl font-bold text-gray-800 dark:text-white/90">Rp {{ number_format($totalNilai, 0, ',', '.') }}</h4>
            </div>
        </div>

        <!-- Stok Menipis -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 md:p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-50 text-amber-500 dark:bg-amber-500/10 dark:text-amber-400">
                <x-svg.octagon-alert class="size-6" />
            </div>
            <div class="mt-4">
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Perlu Restok (≤ 5)</span>
                <h4 class="mt-1 text-2xl font-bold {{ $lowStockCount > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-gray-800 dark:text-white/90' }}">
                    {{ $lowStockCount }} <span class="text-sm font-normal text-gray-500">Item</span>
                </h4>
            </div>
        </div>
    </div>

    {{-- Main Card --}}
    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        {{-- Card Header & Filter --}}
        <div class="p-5 sm:p-6 border-b border-gray-100 dark:border-gray-800">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Daftar Data Barang</h3>
                    <p class="mt-1 text-xs sm:text-sm text-gray-500 dark:text-gray-400">Kelola informasi barang, stok, harga, dan ketersediaan barang inventaris.</p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <button type="button" @click="openCreateModal = true" class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 focus:outline-hidden">
                        <x-svg.plus class="size-4" />
                        <span>Tambah Barang</span>
                    </button>
                </div>
            </div>

            {{-- Filter and Search Form --}}
            <form method="GET" action="{{ route('barang.index') }}" class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-12">
                <div class="lg:col-span-5 relative">
                    <span class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3.5 text-gray-400">
                        <x-svg.search class="size-4" />
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, kode barang, atau kategori..." class="h-10 w-full rounded-lg border border-gray-300 bg-white ps-10 pe-4 text-xs sm:text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-hidden focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder-gray-500" />
                </div>

                <div class="lg:col-span-3">
                    <select name="kategori" class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-xs sm:text-sm text-gray-800 focus:border-brand-500 focus:outline-hidden focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <option value="">Semua Kategori</option>
                        @foreach ($kategoriList as $kat)
                            <option value="{{ $kat }}" {{ request('kategori') == $kat ? 'selected' : '' }}>{{ $kat }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <select name="status" class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-xs sm:text-sm text-gray-800 focus:border-brand-500 focus:outline-hidden focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <option value="">Semua Status</option>
                        <option value="Tersedia" {{ request('status') == 'Tersedia' ? 'selected' : '' }}>Tersedia</option>
                        <option value="Stok Menipis" {{ request('status') == 'Stok Menipis' ? 'selected' : '' }}>Stok Menipis</option>
                        <option value="Habis" {{ request('status') == 'Habis' ? 'selected' : '' }}>Habis</option>
                    </select>
                </div>

                <div class="flex items-center gap-2 lg:col-span-2">
                    <button type="submit" class="inline-flex h-10 w-full items-center justify-center rounded-lg bg-gray-100 px-4 text-xs sm:text-sm font-medium text-gray-700 transition hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                        Filter
                    </button>
                    @if(request()->hasAny(['search', 'kategori', 'status']))
                        <a href="{{ route('barang.index') }}" class="inline-flex h-10 items-center justify-center rounded-lg border border-gray-200 px-3 text-xs sm:text-sm text-gray-500 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-800" title="Reset Filter">
                            <x-svg.x class="size-4" />
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Table Content --}}
        <div class="max-w-full overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 dark:divide-white/5">
                <thead class="bg-gray-50/75 dark:bg-white/[0.02]">
                    <tr>
                        <th class="px-5 py-3.5 text-start text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Kode Barang</th>
                        <th class="px-5 py-3.5 text-start text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Nama Barang</th>
                        <th class="px-5 py-3.5 text-start text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Kategori</th>
                        <th class="px-5 py-3.5 text-start text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Harga Satuan</th>
                        <th class="px-5 py-3.5 text-start text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Stok</th>
                        <th class="px-5 py-3.5 text-start text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
                        <th class="px-5 py-3.5 text-end text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @forelse ($barangs as $barang)
                        <tr class="transition hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
                            {{-- Kode Barang --}}
                            <td class="whitespace-nowrap px-5 py-4 text-start">
                                <span class="inline-flex items-center rounded-md bg-gray-100 px-2 py-1 text-xs font-mono font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                    {{ $barang->kode_barang }}
                                </span>
                            </td>

                            {{-- Nama Barang & Deskripsi --}}
                            <td class="px-5 py-4 text-start">
                                <div class="max-w-xs sm:max-w-md">
                                    <span class="block text-sm font-medium text-gray-800 dark:text-white/90">{{ $barang->nama_barang }}</span>
                                    @if ($barang->deskripsi)
                                        <span class="block text-xs text-gray-500 dark:text-gray-400 truncate mt-0.5">{{ $barang->deskripsi }}</span>
                                    @endif
                                </div>
                            </td>

                            {{-- Kategori --}}
                            <td class="whitespace-nowrap px-5 py-4 text-start">
                                <span class="inline-flex items-center rounded-full bg-brand-50 px-2.5 py-0.5 text-xs font-medium text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                                    {{ $barang->kategori }}
                                </span>
                            </td>

                            {{-- Harga Satuan --}}
                            <td class="whitespace-nowrap px-5 py-4 text-start text-sm font-medium text-gray-800 dark:text-white/90">
                                {{ $barang->formatted_harga }}
                            </td>

                            {{-- Stok & Satuan --}}
                            <td class="whitespace-nowrap px-5 py-4 text-start">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-semibold {{ $barang->stok <= 5 ? ($barang->stok == 0 ? 'text-red-500' : 'text-amber-500') : 'text-gray-800 dark:text-white/90' }}">
                                        {{ $barang->stok }}
                                    </span>
                                    <span class="text-xs text-gray-400">{{ $barang->satuan }}</span>
                                </div>
                            </td>

                            {{-- Status Badge --}}
                            <td class="whitespace-nowrap px-5 py-4 text-start">
                                @if ($barang->status === 'Tersedia')
                                    <x-ui.badge variant="light" color="success" size="sm">Tersedia</x-ui.badge>
                                @elseif ($barang->status === 'Stok Menipis')
                                    <x-ui.badge variant="light" color="warning" size="sm">Stok Menipis</x-ui.badge>
                                @else
                                    <x-ui.badge variant="light" color="error" size="sm">Habis</x-ui.badge>
                                @endif
                            </td>

                            {{-- Aksi --}}
                            <td class="whitespace-nowrap px-5 py-4 text-end text-sm">
                                <div class="inline-flex items-center gap-2">
                                    {{-- Edit Button --}}
                                    <button type="button" @click="editItem({
                                        id: {{ $barang->id }},
                                        kode_barang: '{{ addslashes($barang->kode_barang) }}',
                                        nama_barang: '{{ addslashes($barang->nama_barang) }}',
                                        kategori: '{{ addslashes($barang->kategori) }}',
                                        harga: '{{ (int)$barang->harga }}',
                                        stok: {{ $barang->stok }},
                                        satuan: '{{ addslashes($barang->satuan) }}',
                                        status: '{{ addslashes($barang->status) }}',
                                        deskripsi: '{{ addslashes($barang->deskripsi ?? '') }}'
                                    })" class="inline-flex size-8 items-center justify-center rounded-lg text-gray-500 transition hover:bg-gray-100 hover:text-brand-500 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-brand-400" title="Edit Data">
                                        <x-svg.pencil class="size-4" />
                                    </button>

                                    {{-- Delete Button --}}
                                    <button type="button" @click="confirmDelete({
                                        id: {{ $barang->id }},
                                        kode_barang: '{{ addslashes($barang->kode_barang) }}',
                                        nama_barang: '{{ addslashes($barang->nama_barang) }}'
                                    })" class="inline-flex size-8 items-center justify-center rounded-lg text-gray-500 transition hover:bg-red-50 hover:text-red-500 dark:text-gray-400 dark:hover:bg-red-500/10 dark:hover:text-red-400" title="Hapus Data">
                                        <x-svg.trash-bin class="size-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="flex size-14 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800 text-gray-400">
                                        <x-svg.box class="size-7" />
                                    </div>
                                    <p class="mt-3 text-sm font-medium text-gray-700 dark:text-gray-300">Tidak ada data barang yang ditemukan</p>
                                    <p class="mt-1 text-xs text-gray-500">Silakan ubah filter pencarian atau tambahkan barang baru.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($barangs->hasPages())
            <div class="p-4 border-t border-gray-100 dark:border-gray-800">
                {{ $barangs->links() }}
            </div>
        @endif
    </div>

    {{-- MODAL TAMBAH BARANG (CREATE) --}}
    <div x-cloak x-show="openCreateModal" class="fixed inset-0 z-100 flex items-center justify-center overflow-y-auto p-4" aria-modal="true" role="dialog">
        <!-- Backdrop -->
        <div x-show="openCreateModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click="openCreateModal = false" class="fixed inset-0 bg-black/50 backdrop-blur-xs"></div>

        <!-- Modal Dialog -->
        <div x-show="openCreateModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="relative z-10 w-full max-w-lg rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xl dark:border-gray-800 dark:bg-[#1E2635]">
            <div class="flex items-center justify-between border-b border-gray-100 pb-4 dark:border-gray-800">
                <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Tambah Barang Baru</h3>
                <button type="button" @click="openCreateModal = false" class="rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-white">
                    <x-svg.x class="size-5" />
                </button>
            </div>

            <form action="{{ route('barang.store') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Kode Barang <span class="text-red-500">*</span></label>
                        <input type="text" name="kode_barang" value="{{ old('kode_barang') }}" placeholder="Contoh: BRG-005" required class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-hidden focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Kategori <span class="text-red-500">*</span></label>
                        <input type="text" name="kategori" list="kategori-options" value="{{ old('kategori') }}" placeholder="Elektronik, Aksesoris..." required class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-hidden focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                        <datalist id="kategori-options">
                            @foreach ($kategoriList as $k)
                                <option value="{{ $k }}"></option>
                            @endforeach
                        </datalist>
                    </div>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Nama Barang <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_barang" value="{{ old('nama_barang') }}" placeholder="Masukkan nama barang lengkap" required class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-hidden focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Harga (Rp) <span class="text-red-500">*</span></label>
                        <input type="number" name="harga" value="{{ old('harga') }}" min="0" placeholder="0" required class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-hidden focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Stok <span class="text-red-500">*</span></label>
                        <input type="number" name="stok" value="{{ old('stok', 0) }}" min="0" required class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-hidden focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Satuan <span class="text-red-500">*</span></label>
                        <select name="satuan" class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 focus:border-brand-500 focus:outline-hidden focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            <option value="Unit" {{ old('satuan') == 'Unit' ? 'selected' : '' }}>Unit</option>
                            <option value="Pcs" {{ old('satuan') == 'Pcs' ? 'selected' : '' }}>Pcs</option>
                            <option value="Box" {{ old('satuan') == 'Box' ? 'selected' : '' }}>Box</option>
                            <option value="Set" {{ old('satuan') == 'Set' ? 'selected' : '' }}>Set</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Status Ketersediaan</label>
                    <select name="status" class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 focus:border-brand-500 focus:outline-hidden focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <option value="Tersedia" {{ old('status') == 'Tersedia' ? 'selected' : '' }}>Tersedia</option>
                        <option value="Stok Menipis" {{ old('status') == 'Stok Menipis' ? 'selected' : '' }}>Stok Menipis</option>
                        <option value="Habis" {{ old('status') == 'Habis' ? 'selected' : '' }}>Habis</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Deskripsi / Catatan</label>
                    <textarea name="deskripsi" rows="3" placeholder="Informasi spesifikasi atau catatan tambahan barang..." class="w-full rounded-lg border border-gray-300 bg-white p-3 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-hidden focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">{{ old('deskripsi') }}</textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-800">
                    <button type="button" @click="openCreateModal = false" class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                        Batal
                    </button>
                    <button type="submit" class="rounded-xl bg-brand-500 px-4 py-2 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
                        Simpan Barang
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL EDIT BARANG (UPDATE) --}}
    <div x-cloak x-show="openEditModal" class="fixed inset-0 z-100 flex items-center justify-center overflow-y-auto p-4" aria-modal="true" role="dialog">
        <!-- Backdrop -->
        <div x-show="openEditModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click="openEditModal = false" class="fixed inset-0 bg-black/50 backdrop-blur-xs"></div>

        <!-- Modal Dialog -->
        <div x-show="openEditModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="relative z-10 w-full max-w-lg rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xl dark:border-gray-800 dark:bg-[#1E2635]">
            <div class="flex items-center justify-between border-b border-gray-100 pb-4 dark:border-gray-800">
                <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Edit Data Barang</h3>
                <button type="button" @click="openEditModal = false" class="rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-white">
                    <x-svg.x class="size-5" />
                </button>
            </div>

            <form :action="'{{ url('barang') }}/' + activeItem.id" method="POST" class="mt-4 space-y-4">
                @csrf
                @method('PUT')
                <input type="hidden" name="id" :value="activeItem.id">

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Kode Barang <span class="text-red-500">*</span></label>
                        <input type="text" name="kode_barang" x-model="activeItem.kode_barang" required class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 focus:border-brand-500 focus:outline-hidden focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Kategori <span class="text-red-500">*</span></label>
                        <input type="text" name="kategori" list="kategori-options" x-model="activeItem.kategori" required class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 focus:border-brand-500 focus:outline-hidden focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                    </div>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Nama Barang <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_barang" x-model="activeItem.nama_barang" required class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 focus:border-brand-500 focus:outline-hidden focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Harga (Rp) <span class="text-red-500">*</span></label>
                        <input type="number" name="harga" x-model="activeItem.harga" min="0" required class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 focus:border-brand-500 focus:outline-hidden focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Stok <span class="text-red-500">*</span></label>
                        <input type="number" name="stok" x-model="activeItem.stok" min="0" required class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 focus:border-brand-500 focus:outline-hidden focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Satuan <span class="text-red-500">*</span></label>
                        <select name="satuan" x-model="activeItem.satuan" class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 focus:border-brand-500 focus:outline-hidden focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            <option value="Unit">Unit</option>
                            <option value="Pcs">Pcs</option>
                            <option value="Box">Box</option>
                            <option value="Set">Set</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Status Ketersediaan</label>
                    <select name="status" x-model="activeItem.status" class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 focus:border-brand-500 focus:outline-hidden focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <option value="Tersedia">Tersedia</option>
                        <option value="Stok Menipis">Stok Menipis</option>
                        <option value="Habis">Habis</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Deskripsi / Catatan</label>
                    <textarea name="deskripsi" rows="3" x-model="activeItem.deskripsi" class="w-full rounded-lg border border-gray-300 bg-white p-3 text-sm text-gray-800 focus:border-brand-500 focus:outline-hidden focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-800">
                    <button type="button" @click="openEditModal = false" class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                        Batal
                    </button>
                    <button type="submit" class="rounded-xl bg-brand-500 px-4 py-2 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
                        Perbarui Barang
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL KONFIRMASI HAPUS (DELETE) --}}
    <div x-cloak x-show="openDeleteModal" class="fixed inset-0 z-100 flex items-center justify-center overflow-y-auto p-4" aria-modal="true" role="dialog">
        <!-- Backdrop -->
        <div x-show="openDeleteModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click="openDeleteModal = false" class="fixed inset-0 bg-black/50 backdrop-blur-xs"></div>

        <!-- Modal Dialog -->
        <div x-show="openDeleteModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="relative z-10 w-full max-w-md rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xl dark:border-gray-800 dark:bg-[#1E2635]">
            <div class="flex items-center gap-3">
                <div class="flex size-11 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-600 dark:bg-red-500/10 dark:text-red-400">
                    <x-svg.trash-bin class="size-5" />
                </div>
                <div>
                    <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Hapus Data Barang?</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Tindakan ini tidak dapat dibatalkan.</p>
                </div>
            </div>

            <p class="mt-4 text-sm text-gray-600 dark:text-gray-300">
                Apakah Anda yakin ingin menghapus barang <strong class="text-gray-900 dark:text-white" x-text="activeItem.nama_barang"></strong> (<span class="font-mono text-xs" x-text="activeItem.kode_barang"></span>)?
            </p>

            <form :action="'{{ url('barang') }}/' + activeItem.id" method="POST" class="mt-6 flex items-center justify-end gap-3">
                @csrf
                @method('DELETE')
                <button type="button" @click="openDeleteModal = false" class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                    Batal
                </button>
                <button type="submit" class="rounded-xl bg-red-600 px-4 py-2 text-sm font-medium text-white shadow-theme-xs hover:bg-red-700">
                    Ya, Hapus Barang
                </button>
            </form>
        </div>
    </div>

</div>
@endsection
