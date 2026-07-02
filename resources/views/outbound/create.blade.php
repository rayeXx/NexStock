<x-app-layout>
    <div style="margin-bottom: 2rem;">
        <a href="{{ route('outbound.index') }}" style="color: var(--accent-blue); text-decoration: none; font-weight: 500; font-size: 0.9rem;">
            &larr; Kembali ke Riwayat Outbound
        </a>
        <h1 style="margin-top: 0.5rem;">Proses Barang Keluar (FEFO)</h1>
        <p>Sistem akan otomatis memotong stok dimulai dari batch dengan tanggal kedaluwarsa <strong>paling dekat</strong> terlebih dahulu.</p>
    </div>

    <div class="instruction-box" style="max-width: 800px;">
        <h4>Prinsip FEFO: First Expired, First Out (FR-02)</h4>
        <ol>
            <li>Masukkan tujuan pengiriman dan daftar produk yang ingin dikeluarkan.</li>
            <li>Sistem akan memilih batch dengan expired date terdekat secara otomatis.</li>
            <li>Setelah validasi sukses, sistem akan menampilkan instruksi lokasi rak pengambilan fisik.</li>
        </ol>
    </div>

    <div class="glass-card" style="max-width: 800px;">
        <form method="POST" action="{{ route('outbound.store') }}" id="outboundForm">
            @csrf

            <div class="form-group">
                <label class="form-label" for="tujuan">Tujuan Pengiriman / Pelanggan *</label>
                <input type="text" name="tujuan" id="tujuan" class="form-control" placeholder="Contoh: Toko Pak Budi — Jl. Sudirman No. 12" value="{{ old('tujuan') }}" required>
            </div>

            <div style="border-top: 1px solid var(--border-color); margin: 1.5rem 0; padding-top: 1.5rem;">
                <div class="flex-between" style="margin-bottom: 1rem;">
                    <h3 style="font-size: 1.05rem; font-weight: 600;">Daftar Produk yang Dikeluarkan</h3>
                    <button type="button" id="addOutboundItem" class="btn btn-secondary" style="padding: 6px 14px; min-height: 36px; font-size: 0.85rem;">
                        + Tambah Produk
                    </button>
                </div>

                <div id="outboundItems">
                    <div class="outbound-item-row" style="display: grid; grid-template-columns: 1fr auto; gap: 1rem; margin-bottom: 1rem; padding: 1rem; background: rgba(255,255,255,0.03); border-radius: 0.5rem; border: 1px solid var(--border-color);">
                        <div class="grid-2" style="gap: 0.75rem;">
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label">Produk *</label>
                                <select name="items[0][produk_id]" class="form-control select2" required>
                                    <option value="" disabled selected>-- Pilih Produk --</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->kode_produk }}">
                                            {{ $product->nama_produk }} (Stok: {{ $product->total_stok }} {{ $product->uom }})
                                        </option>
                                    @endforeach
                                    @foreach($dummyProducts as $dp)
                                        <option value="{{ $dp->kode_produk }}">
                                            {{ $dp->nama_produk }} (Stok: {{ $dp->__dummy_stok }} {{ $dp->uom }}) [Simulasi]
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label">Jumlah Dikeluarkan *</label>
                                <input type="number" name="items[0][qty_keluar]" class="form-control" min="1" placeholder="Contoh: 50" required>
                            </div>
                        </div>
                        <div style="display: flex; align-items: flex-end;">
                            <button type="button" class="btn btn-danger remove-outbound-item" style="padding: 6px 12px; min-height: 44px; opacity: 0.4; cursor: not-allowed;" disabled>&times;</button>
                        </div>
                    </div>
                </div>
            </div>

            <div style="margin-top: 1.5rem; display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-success" style="flex: 1;">
                    ✓ Validasi Stok & Proses Barang Keluar (FEFO)
                </button>
                <a href="{{ route('outbound.index') }}" class="btn btn-secondary" style="flex: 1;">
                    Batal
                </a>
            </div>
        </form>
    </div>

    <script>
        const products = @json($products->values());
        const dummyProducts = @json($dummyProducts->values());
        let outboundCount = 1;

        function buildProductOptions() {
            let options = products.map(p =>
                `<option value="${p.kode_produk}">${p.nama_produk} (Stok: ${p.total_stok} ${p.uom})</option>`
            ).join('');

            options += dummyProducts.map(p =>
                `<option value="${p.kode_produk}">${p.nama_produk} (Stok: ${p.__dummy_stok} ${p.uom}) [Simulasi]</option>`
            ).join('');

            return options;
        }

        document.getElementById('addOutboundItem').addEventListener('click', function () {
            const container = document.getElementById('outboundItems');
            const index = outboundCount++;

            const productOptions = buildProductOptions();

            const newRow = document.createElement('div');
            newRow.className = 'outbound-item-row';
            newRow.style.cssText = 'display: grid; grid-template-columns: 1fr auto; gap: 1rem; margin-bottom: 1rem; padding: 1rem; background: rgba(255,255,255,0.03); border-radius: 0.5rem; border: 1px solid rgba(255,255,255,0.08);';
            newRow.innerHTML = `
                <div class="grid-2" style="gap: 0.75rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Produk *</label>
                        <select name="items[${index}][produk_id]" class="form-control select2" required>
                            <option value="" disabled selected>-- Pilih Produk --</option>
                            ${productOptions}
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Jumlah Dikeluarkan *</label>
                        <input type="number" name="items[${index}][qty_keluar]" class="form-control" min="1" placeholder="Contoh: 50" required>
                    </div>
                </div>
                <div style="display: flex; align-items: flex-end;">
                    <button type="button" class="btn btn-danger remove-outbound-item" style="padding: 6px 12px; min-height: 44px;">&times;</button>
                </div>`;
            container.appendChild(newRow);
            if (typeof jQuery !== 'undefined' && typeof jQuery.fn.select2 !== 'undefined') {
                $(newRow).find('.select2').select2({ width: '100%' });
            }
            updateOutboundRemoveButtons();
        });

        document.getElementById('outboundItems').addEventListener('click', function (e) {
            if (e.target.classList.contains('remove-outbound-item') && !e.target.disabled) {
                e.target.closest('.outbound-item-row').remove();
                updateOutboundRemoveButtons();
            }
        });

        function updateOutboundRemoveButtons() {
            const rows = document.querySelectorAll('.outbound-item-row');
            rows.forEach(row => {
                const btn = row.querySelector('.remove-outbound-item');
                if (rows.length === 1) {
                    btn.disabled = true;
                    btn.style.opacity = '0.4';
                    btn.style.cursor = 'not-allowed';
                } else {
                    btn.disabled = false;
                    btn.style.opacity = '1';
                    btn.style.cursor = 'pointer';
                }
            });
        }
    </script>
</x-app-layout>
