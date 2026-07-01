<x-app-layout>
    <div style="margin-bottom: 2rem;">
        <a href="{{ route('product.index') }}" style="color: var(--accent-blue); text-decoration: none; font-weight: 500; font-size: 0.9rem;">
            &larr; Kembali ke Master Produk
        </a>
        <h1 style="margin-top: 0.5rem;">Edit Produk</h1>
        <p>Perbarui rincian data produk dan konfigurasi logistik terkait.</p>
    </div>

    <div class="glass-card" style="max-width: 600px;">
        <form action="{{ route('product.update', $product->kode_produk) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="form-group">
                <label class="form-label" for="kode_produk">Kode SKU Produk (SKU Pabrik)</label>
                <input type="text" id="kode_produk" class="form-control" value="{{ $product->kode_produk }}" disabled style="opacity: 0.6; cursor: not-allowed;">
                <p style="font-size: 0.75rem; margin-top: 0.25rem; color: var(--accent-yellow);">SKU produk bersifat unik dan tidak dapat diubah setelah didaftarkan.</p>
            </div>

            <div class="form-group">
                <label class="form-label" for="nama_produk">Nama Produk *</label>
                <input type="text" name="nama_produk" id="nama_produk" class="form-control" value="{{ old('nama_produk', $product->nama_produk) }}" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="kategori_id">Kategori Produk *</label>
                <select name="kategori_id" id="kategori_id" class="form-control" required>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('kategori_id', $product->kategori_id) == $category->id ? 'selected' : '' }}>
                            {{ $category->nama_kategori }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label" for="harga_beli">Harga Beli Satuan (Rp) *</label>
                    <input type="number" name="harga_beli" id="harga_beli" class="form-control" min="0" value="{{ old('harga_beli', (double)$product->harga_beli) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="uom">Satuan Kemasan (UOM) *</label>
                    <select name="uom" id="uom" class="form-control" required>
                        <option value="Pcs" {{ old('uom', $product->uom) == 'Pcs' ? 'selected' : '' }}>Pcs</option>
                        <option value="Dus" {{ old('uom', $product->uom) == 'Dus' ? 'selected' : '' }}>Dus</option>
                        <option value="Pack" {{ old('uom', $product->uom) == 'Pack' ? 'selected' : '' }}>Pack</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="stok_minimum">Batas Minimum Stok *</label>
                <input type="number" name="stok_minimum" id="stok_minimum" class="form-control" min="0" value="{{ old('stok_minimum', $product->stok_minimum) }}" required>
            </div>

            <div style="margin-top: 2rem; display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">
                    Perbarui Produk
                </button>
                <a href="{{ route('product.index') }}" class="btn btn-secondary" style="flex: 1;">
                    Batal
                </a>
            </div>
        </form>
    </div>
</x-app-layout>
