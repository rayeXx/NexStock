<x-app-layout>
    <div style="margin-bottom: 2rem;">
        <a href="{{ route('outbound.index') }}" style="color: var(--accent-blue); text-decoration: none; font-weight: 500; font-size: 0.9rem;">
            &larr; Kembali ke Riwayat Outbound
        </a>
        <h1 style="margin-top: 0.5rem;">Proses Barang Keluar (Dynamic FEFO)</h1>
        <p>Sistem akan merekomendasikan batch terdekat berdasarkan <strong>FEFO</strong> secara otomatis, dengan opsi <strong>Override</strong> oleh Admin.</p>
    </div>

    @if(session('error'))
        <div style="background: rgba(239,68,68,0.12); border: 1px solid rgba(239,68,68,0.4); border-radius: 0.75rem; padding: 1rem 1.25rem; margin-bottom: 1.5rem; color: #fca5a5; font-size: 0.88rem;">
            {!! session('error') !!}
        </div>
    @endif

    <div class="instruction-box" style="max-width: 800px; margin-bottom: 2rem;">
        <h4>Alur Pengeluaran Barang — Dynamic FEFO</h4>
        <ol>
            <li>Pilih **Customer** (Tujuan Pengiriman) pada dropdown di bawah.</li>
            <li>Pilih **Produk** dan masukkan kuantitas yang ingin dikeluarkan.</li>
            <li>Sistem akan otomatis memilih **Batch FEFO** (terdekat tanggal expired-nya).</li>
            <li>Gunakan pilihan **Alokasi Batch (Override)** untuk mengubah batch jika ingin taktik khusus (diskon, request pelanggan, dll).</li>
        </ol>
    </div>

    <div class="glass-card" style="max-width: 800px;">
        <form method="POST" action="{{ route('outbound.store') }}" id="outboundForm">
            @csrf

            <div class="form-group">
                <label class="form-label" for="tujuan">Customer / Tujuan Pengiriman *</label>
                <select name="tujuan" id="tujuan" class="form-control" required style="background: var(--bg-dark); color: #fff;">
                    <option value="" disabled selected>-- Pilih Customer --</option>
                    <option value="Indomaret Cabang Sudirman">Indomaret Cabang Sudirman</option>
                    <option value="Alfamart Sentosa">Alfamart Sentosa</option>
                    <option value="Superindo Town Square">Superindo Town Square</option>
                    <option value="Toko Kelontong Jaya">Toko Kelontong Jaya</option>
                    <option value="Resto FastFood Nusantara">Resto FastFood Nusantara</option>
                    <option value="Mitra Catering Sejahtera">Mitra Catering Sejahtera</option>
                </select>
            </div>

            <div style="border-top: 1px solid var(--border-color); margin: 1.5rem 0; padding-top: 1.5rem;">
                <div class="flex-between" style="margin-bottom: 1.5rem;">
                    <h3 style="font-size: 1.05rem; font-weight: 600; color: #f1f5f9;">Daftar Item &amp; Alokasi Batch</h3>
                    <button type="button" id="addOutboundItem" class="btn btn-secondary" style="padding: 6px 14px; min-height: 36px; font-size: 0.85rem;">
                        + Tambah Baris Produk
                    </button>
                </div>

                <div id="outboundItems">
                    <div class="outbound-item-row" style="display: grid; grid-template-columns: 1fr auto; gap: 1rem; margin-bottom: 1.5rem; padding: 1.25rem; background: rgba(255,255,255,0.02); border-radius: 0.75rem; border: 1px solid var(--border-color);">
                        <div style="display: flex; flex-direction: column; gap: 0.75rem; width: 100%;">
                            <div class="grid-2" style="gap: 0.75rem;">
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label class="form-label">Produk *</label>
                                    <select name="items[0][produk_id]" class="form-control product-select" required style="background: var(--bg-dark); color: #fff;">
                                        <option value="" disabled selected>-- Pilih Produk --</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->kode_produk }}">
                                                {{ $product->nama_produk }} (Total Stok: {{ $product->available_stok }} {{ $product->uom }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label class="form-label">Jumlah Dikeluarkan *</label>
                                    <input type="number" name="items[0][qty_keluar]" class="form-control qty-input" min="1" placeholder="Pilih produk dahulu" required style="background: var(--bg-dark); color: #fff;">
                                </div>
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label" style="color: var(--accent-blue);">Alokasi Batch (FEFO / Override) *</label>
                                <select name="items[0][batch_number]" class="form-control batch-select" required style="background: var(--bg-dark); color: #fff; font-family: monospace;">
                                    <option value="" disabled selected>-- Pilih Produk Dahulu --</option>
                                </select>
                            </div>
                        </div>
                        <div style="display: flex; align-items: center;">
                            <button type="button" class="btn btn-danger remove-outbound-item" style="padding: 6px 12px; min-height: 44px; opacity: 0.4; cursor: not-allowed;" disabled>&times;</button>
                        </div>
                    </div>
                </div>
            </div>

            <div style="margin-top: 2rem; display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-success" style="flex: 2; font-weight: 600;">
                    Simpan Rencana Outbound
                </button>
                <a href="{{ route('outbound.index') }}" class="btn btn-secondary" style="flex: 1;">
                    Batal
                </a>
            </div>
        </form>
    </div>

    <script>
        const batchesByProduct = @json($batches);
        const products = @json($products->values());
        let outboundCount = 1;

        function buildProductOptions() {
            return products.map(p =>
                `<option value="${p.kode_produk}">${p.nama_produk} (Total Stok: ${p.available_stok} ${p.uom})</option>`
            ).join('');
        }

        function populateBatches(rowElement, productCode) {
            const batchSelect = rowElement.querySelector('.batch-select');
            const qtyInput = rowElement.querySelector('.qty-input');
            batchSelect.innerHTML = '<option value="" disabled selected>-- Pilih Batch --</option>';

            const batches = batchesByProduct[productCode] || [];
            if (batches.length === 0) {
                batchSelect.innerHTML = '<option value="" disabled>Stok Habis</option>';
                qtyInput.max = 0;
                qtyInput.value = '';
                qtyInput.placeholder = 'Stok Habis';
                return;
            }

            batches.forEach((b, index) => {
                const opt = document.createElement('option');
                opt.value = b.batch_number;
                // Highlight the first one as (Rekomendasi FEFO)
                const labelFEFO = index === 0 ? ' [Rekomendasi FEFO]' : '';
                opt.textContent = `${b.batch_number} | Exp: ${b.expired_date} | Rak: ${b.rak_id} (Stok: ${b.stok_sisa_batch})${labelFEFO}`;
                opt.dataset.max = b.stok_sisa_batch;
                if (index === 0) {
                    opt.selected = true;
                    qtyInput.max = b.stok_sisa_batch;
                    qtyInput.placeholder = `Maks: ${b.stok_sisa_batch}`;
                }
                batchSelect.appendChild(opt);
            });
        }

        // Handle dynamically changing product and batch selections
        document.getElementById('outboundItems').addEventListener('change', function (e) {
            if (e.target.classList.contains('product-select')) {
                const row = e.target.closest('.outbound-item-row');
                populateBatches(row, e.target.value);
            }
            if (e.target.classList.contains('batch-select')) {
                const row = e.target.closest('.outbound-item-row');
                const selectedOpt = e.target.options[e.target.selectedIndex];
                const maxVal = selectedOpt.dataset.max || 0;
                const qtyInput = row.querySelector('.qty-input');
                qtyInput.max = maxVal;
                qtyInput.placeholder = `Maks: ${maxVal}`;
                if (qtyInput.value && parseInt(qtyInput.value) > parseInt(maxVal)) {
                    qtyInput.value = maxVal;
                }
            }
        });

        document.getElementById('addOutboundItem').addEventListener('click', function () {
            const container = document.getElementById('outboundItems');
            const index = outboundCount++;

            const productOptions = buildProductOptions();

            const newRow = document.createElement('div');
            newRow.className = 'outbound-item-row';
            newRow.style.cssText = 'display: grid; grid-template-columns: 1fr auto; gap: 1rem; margin-bottom: 1.5rem; padding: 1.25rem; background: rgba(255,255,255,0.02); border-radius: 0.75rem; border: 1px solid rgba(255,255,255,0.08);';
            newRow.innerHTML = `
                <div style="display: flex; flex-direction: column; gap: 0.75rem; width: 100%;">
                    <div class="grid-2" style="gap: 0.75rem;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Produk *</label>
                            <select name="items[${index}][produk_id]" class="form-control product-select" required style="background: var(--bg-dark); color: #fff;">
                                <option value="" disabled selected>-- Pilih Produk --</option>
                                ${productOptions}
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Jumlah Dikeluarkan *</label>
                            <input type="number" name="items[${index}][qty_keluar]" class="form-control qty-input" min="1" placeholder="Pilih produk dahulu" required style="background: var(--bg-dark); color: #fff;">
                        </div>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="color: var(--accent-blue);">Alokasi Batch (FEFO / Override) *</label>
                        <select name="items[${index}][batch_number]" class="form-control batch-select" required style="background: var(--bg-dark); color: #fff; font-family: monospace;">
                            <option value="" disabled selected>-- Pilih Produk Dahulu --</option>
                        </select>
                    </div>
                </div>
                <div style="display: flex; align-items: center;">
                    <button type="button" class="btn btn-danger remove-outbound-item" style="padding: 6px 12px; min-height: 44px;">&times;</button>
                </div>`;
            container.appendChild(newRow);
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
