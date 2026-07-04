<x-app-layout>
    <div style="margin-bottom: 2rem;">
        <a href="{{ route('restock-request.history') }}" style="color: var(--accent-blue); text-decoration: none; font-weight: 500; font-size: 0.9rem;">
            &larr; Kembali ke Riwayat Pengajuan
        </a>
        <h1 style="margin-top: 0.5rem;">Ajukan Restock Barang</h1>
        <p>Buat pengajuan restock berdasarkan kebutuhan gudang atau rekomendasi Smart Restock.</p>
    </div>

    <div class="glass-card" style="max-width: 600px;">
        <form action="{{ route('restock-request.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label class="form-label" for="produk_id">Produk yang Diajukan *</label>
                <select name="produk_id" id="produk_id" class="form-control select2" required>
                    <option value="" disabled {{ $selectedProduct ? '' : 'selected' }}>Pilih Produk</option>
                    @foreach($products as $product)
                        <option value="{{ $product->kode_produk }}"
                            data-stok="{{ $product->total_stok }}"
                            data-min="{{ $product->stok_minimum }}"
                            {{ ($selectedProduct && $selectedProduct->kode_produk === $product->kode_produk) || old('produk_id') == $product->kode_produk ? 'selected' : '' }}>
                            {{ $product->kode_produk }} — {{ $product->nama_produk }} (Stok: {{ $product->total_stok }})
                        </option>
                    @endforeach
                </select>
                @error('produk_id')
                    <p style="color: var(--accent-red); font-size: 0.78rem; margin-top: 0.25rem;">{{ $message }}</p>
                @enderror
            </div>

            {{-- Product info card --}}
            <div id="productInfo" style="display: none; background:rgba(56,189,248,0.05); border:1px solid rgba(56,189,248,0.15); border-radius:0.75rem; padding:1rem 1.25rem; margin-bottom:1rem;">
                <div style="display:flex; gap:1.5rem; flex-wrap:wrap;">
                    <div>
                        <span style="font-size:0.75rem; color:#64748b;">Stok Saat Ini</span><br>
                        <strong id="infoStok" style="font-size:1.1rem;">-</strong>
                    </div>
                    <div>
                        <span style="font-size:0.75rem; color:#64748b;">Minimum Stok</span><br>
                        <strong id="infoMin" style="font-size:1.1rem;">-</strong>
                    </div>
                    <div>
                        <span style="font-size:0.75rem; color:#64748b;">Status</span><br>
                        <span id="infoStatus" class="badge badge-green">-</span>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="qty_request">Jumlah yang Diajukan *</label>
                <input type="number" name="qty_request" id="qty_request" class="form-control" min="1" placeholder="Contoh: 50" value="{{ old('qty_request') }}" required>
                @error('qty_request')
                    <p style="color: var(--accent-red); font-size: 0.78rem; margin-top: 0.25rem;">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="alasan">Alasan Pengajuan *</label>
                <textarea name="alasan" id="alasan" class="form-control" rows="3" placeholder="Contoh: Stok hampir habis, permintaan meningkat menjelang lebaran..." required>{{ old('alasan') }}</textarea>
                @error('alasan')
                    <p style="color: var(--accent-red); font-size: 0.78rem; margin-top: 0.25rem;">{{ $message }}</p>
                @enderror
            </div>

            <div style="margin-top: 2rem; display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">
                    Kirim Pengajuan
                </button>
                <a href="{{ route('restock-request.history') }}" class="btn btn-secondary" style="flex: 1;">
                    Batal
                </a>
            </div>
        </form>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const select = document.getElementById('produk_id');
        const info = document.getElementById('productInfo');

        function updateInfo() {
            const opt = select.options[select.selectedIndex];
            if (opt && opt.value) {
                const stok = opt.getAttribute('data-stok');
                const min = opt.getAttribute('data-min');
                document.getElementById('infoStok').textContent = stok + ' Unit';
                document.getElementById('infoMin').textContent = min + ' Unit';

                const statusEl = document.getElementById('infoStatus');
                if (parseInt(stok) <= parseInt(min)) {
                    statusEl.textContent = '🔴 Kritis';
                    statusEl.className = 'badge badge-red';
                } else if (parseInt(stok) <= parseInt(min) * 1.5) {
                    statusEl.textContent = '🟡 Menipis';
                    statusEl.className = 'badge badge-yellow';
                } else {
                    statusEl.textContent = '🟢 Aman';
                    statusEl.className = 'badge badge-green';
                }
                info.style.display = 'block';
            } else {
                info.style.display = 'none';
            }
        }

        select.addEventListener('change', updateInfo);
        // Also listen for Select2 change
        if (window.jQuery) {
            $(select).on('change', updateInfo);
        }
        updateInfo();
    });
    </script>
</x-app-layout>
