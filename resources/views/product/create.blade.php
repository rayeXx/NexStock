<x-app-layout>
    <div style="margin-bottom: 2rem;">
        <a href="{{ route('product.index') }}" style="color: var(--accent-blue); text-decoration: none; font-weight: 500; font-size: 0.9rem;">
            &larr; Kembali ke Master Produk
        </a>
        <h1 style="margin-top: 0.5rem;">Tambah Produk Baru</h1>
        <p>Definisikan barang baru di master logistik untuk didaftarkan ke sistem.</p>
    </div>

    <div class="glass-card" style="max-width: 600px;">
        <form action="{{ route('product.store') }}" method="POST">
            @csrf
            
            <div class="form-group">
                <label class="form-label" for="kode_produk">Kode SKU Produk *</label>
                <input type="text" name="kode_produk" id="kode_produk" class="form-control" placeholder="Contoh: SKU-ID-1002" value="{{ old('kode_produk') }}" required>
                <p style="font-size: 0.75rem; margin-top: 0.25rem;">Gunakan kode unik SKU dari pabrik barang.</p>
            </div>

            <div class="form-group">
                <label class="form-label" for="nama_produk">Nama Produk *</label>
                <input type="text" name="nama_produk" id="nama_produk" class="form-control" placeholder="Contoh: Biskuit Oreo 137g" value="{{ old('nama_produk') }}" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="kategori_id">Kategori Produk *</label>
                <select name="kategori_id" id="kategori_id" class="form-control" required>
                    <option value="" disabled selected>Pilih Kategori</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('kategori_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->nama_kategori }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label" for="harga_beli">Harga Beli Satuan (Rp) *</label>
                    <input type="number" name="harga_beli" id="harga_beli" class="form-control" min="0" placeholder="Contoh: 7500" value="{{ old('harga_beli') }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="uom">Satuan Kemasan (UOM) *</label>
                    <select name="uom" id="uom" class="form-control" required>
                        <option value="Pcs" {{ old('uom') == 'Pcs' ? 'selected' : '' }}>Pcs</option>
                        <option value="Dus" {{ old('uom') == 'Dus' ? 'selected' : '' }}>Dus</option>
                        <option value="Pack" {{ old('uom') == 'Pack' ? 'selected' : '' }}>Pack</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="stok_minimum">Batas Minimum Stok *</label>
                <input type="number" name="stok_minimum" id="stok_minimum" class="form-control" min="0" placeholder="Contoh: 20" value="{{ old('stok_minimum', 10) }}" required>
                <p style="font-size: 0.75rem; margin-top: 0.25rem;">Sistem akan memicu peringatan restock jika stok berada di bawah batas ini.</p>
            </div>

            <div style="margin-top: 2rem; display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">
                    Simpan Produk
                </button>
                <a href="{{ route('product.index') }}" class="btn btn-secondary" style="flex: 1;">
                    Batal
                </a>
            </div>
        </form>
    </div>
</x-app-layout>
