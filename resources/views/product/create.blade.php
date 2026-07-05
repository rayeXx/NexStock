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
                <select name="kategori_id" id="kategori_id" class="form-control" required onchange="toggleLainnya(this)">
                    <option value="" disabled {{ old('kategori_id') ? '' : 'selected' }}>Pilih Kategori</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('kategori_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->nama_kategori }}
                        </option>
                    @endforeach
                    <option value="lainnya" {{ old('kategori_id') == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                </select>
            </div>

            {{-- Dynamic fields for "Lainnya" --}}
            <div id="kategoriLainnyaFields" style="display: {{ old('kategori_id') == 'lainnya' ? 'block' : 'none' }}; transition: all 0.3s ease;">
                <div class="form-group" style="background:rgba(129,140,248,0.05); border:1px solid rgba(129,140,248,0.15); border-radius:0.75rem; padding:1rem 1.25rem; margin-bottom:1rem;">
                    <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.75rem;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#818cf8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                        <span style="font-size:0.82rem; font-weight:600; color:#818cf8;">Kategori Baru</span>
                    </div>
                    <div class="form-group" style="margin-bottom:0.75rem;">
                        <label class="form-label" for="nama_kategori_baru">Nama Kategori Baru *</label>
                        <input type="text" name="nama_kategori_baru" id="nama_kategori_baru" class="form-control" placeholder="Contoh: Peralatan Kebersihan" value="{{ old('nama_kategori_baru') }}">
                        @error('nama_kategori_baru')
                            <p style="color: var(--accent-red); font-size: 0.78rem; margin-top: 0.25rem;">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label" for="catatan">Catatan (Opsional)</label>
                        <input type="text" name="catatan" id="catatan" class="form-control" placeholder="Contoh: Produk non-konsumsi" value="{{ old('catatan') }}">
                    </div>
                </div>
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label" for="harga_beli">Harga Beli (Rp) *</label>
                    <input type="number" name="harga_beli" id="harga_beli" class="form-control" min="0" placeholder="Contoh: 7500" value="{{ old('harga_beli') }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="stok_minimum">Batas Minimum Stok *</label>
                    <input type="number" name="stok_minimum" id="stok_minimum" class="form-control" min="0" placeholder="Contoh: 20" value="{{ old('stok_minimum', 10) }}" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; margin-bottom: 1.25rem;">
                <div class="form-group">
                    <label class="form-label" for="satuan_beli">Satuan Beli *</label>
                    <select name="satuan_beli" id="satuan_beli" class="form-control" required>
                        <option value="Dus" {{ old('satuan_beli', 'Dus') == 'Dus' ? 'selected' : '' }}>Dus</option>
                        <option value="Pack" {{ old('satuan_beli') == 'Pack' ? 'selected' : '' }}>Pack</option>
                        <option value="Pcs" {{ old('satuan_beli') == 'Pcs' ? 'selected' : '' }}>Pcs</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="satuan_jual">Satuan Jual *</label>
                    <select name="satuan_jual" id="satuan_jual" class="form-control" required>
                        <option value="Pcs" {{ old('satuan_jual', 'Pcs') == 'Pcs' ? 'selected' : '' }}>Pcs</option>
                        <option value="Pack" {{ old('satuan_jual') == 'Pack' ? 'selected' : '' }}>Pack</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="rasio_konversi">Rasio Konversi *</label>
                    <input type="number" name="rasio_konversi" id="rasio_konversi" class="form-control" min="1" placeholder="Contoh: 12" value="{{ old('rasio_konversi', 1) }}" required>
                </div>
            </div>
            <p style="font-size: 0.75rem; margin-top: -0.75rem; margin-bottom: 1.5rem; color: var(--text-muted);">
                Rasio Konversi adalah jumlah Satuan Jual di dalam 1 Satuan Beli (contoh: jika beli dalam Dus berisi 12 Pcs, isi 12).
            </p>

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

    <script>
    function toggleLainnya(select) {
        const fields = document.getElementById('kategoriLainnyaFields');
        const namaInput = document.getElementById('nama_kategori_baru');
        if (select.value === 'lainnya') {
            fields.style.display = 'block';
            namaInput.setAttribute('required', 'required');
        } else {
            fields.style.display = 'none';
            namaInput.removeAttribute('required');
            namaInput.value = '';
            document.getElementById('catatan').value = '';
        }
    }
    // Trigger on page load for old() values
    document.addEventListener('DOMContentLoaded', function() {
        toggleLainnya(document.getElementById('kategori_id'));
    });
    </script>
</x-app-layout>
