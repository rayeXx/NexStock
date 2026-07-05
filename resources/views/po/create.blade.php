<x-app-layout>
    <div style="margin-bottom: 2rem;">
        <a href="{{ route('po.index') }}" style="color: var(--accent-blue); text-decoration: none; font-weight: 500; font-size: 0.9rem;">
            &larr; Kembali ke Daftar PO
        </a>
        <h1 style="margin-top: 0.5rem;">Buat Draft Purchase Order Baru</h1>
        <p>Susun daftar kebutuhan pengadaan stok dari supplier pilihan sebelum diajukan ke Owner.</p>
    </div>

    <div class="glass-card" style="max-width: 860px;">
        <form action="{{ route('po.store') }}" method="POST" id="poForm">
            @csrf

            <div class="grid-2" style="gap: 1rem;">
                {{-- Supplier --}}
                <div class="form-group">
                    <label class="form-label" for="supplier_id">Pilih Supplier *</label>
                    <select name="supplier_id" id="supplier_id" class="form-control select2" required>
                        <option value="" disabled selected>-- Pilih Mitra Supplier --</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->nama_supplier }} ({{ $supplier->kontak }})
                            </option>
                        @endforeach
                    </select>
                </div>
                {{-- Target Tanggal Kirim --}}
                <div class="form-group">
                    <label class="form-label" for="target_tanggal_kirim">
                        Target Tgl. Pengiriman
                        <span style="font-size: 0.75rem; font-weight: 400; color: var(--text-muted);">(untuk KPI keterlambatan)</span>
                    </label>
                    <input type="date" name="target_tanggal_kirim" id="target_tanggal_kirim"
                           class="form-control" value="{{ old('target_tanggal_kirim') }}"
                           min="{{ date('Y-m-d') }}">
                </div>
            </div>

            <div style="border-top: 1px solid var(--border-color); margin: 1.5rem 0; padding-top: 1.5rem;">
                <div class="flex-between" style="margin-bottom: 1rem;">
                    <h3 style="font-size: 1.05rem; font-weight: 600;">Daftar Item Pemesanan</h3>
                    <button type="button" id="addItemBtn" class="btn btn-secondary" style="padding: 6px 14px; min-height: 36px; font-size: 0.85rem;">
                        + Tambah Item
                    </button>
                </div>

                <div id="itemsContainer">
                    <div class="po-item-row" style="margin-bottom: 1rem; padding: 1rem; background: rgba(255,255,255,0.03); border-radius: 0.5rem; border: 1px solid var(--border-color);">
                        <div style="display: grid; grid-template-columns: 1fr 120px 160px auto; gap: 0.75rem; align-items: end;">
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label">Produk *</label>
                                <select name="items[0][produk_id]" class="form-control select2 produk-select" required>
                                    <option value="" disabled selected>-- Pilih Produk --</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->kode_produk }}"
                                                data-harga="{{ (float)$product->harga_beli }}">
                                            {{ $product->nama_produk }} ({{ $product->kode_produk }}) - {{ $product->uom }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label">Qty *</label>
                                <input type="number" name="items[0][qty_pesan]" class="form-control qty-input" min="1" placeholder="100" required>
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label">
                                    Harga Satuan (Rp)
                                    <span style="font-size: 0.7rem; color: var(--accent-blue); font-weight: 400;" title="Auto-isi dari harga master; bisa diubah sesuai harga PO aktual">✏️ override</span>
                                </label>
                                <input type="number" name="items[0][harga_satuan]" class="form-control harga-input" min="0" step="1" placeholder="Auto dari master">
                            </div>
                            <div style="display: flex; align-items: flex-end; padding-bottom: 0;">
                                <button type="button" class="btn btn-danger remove-item" style="padding: 6px 12px; min-height: 44px; opacity: 0.4; cursor: not-allowed;" disabled>
                                    &times;
                                </button>
                            </div>
                        </div>
                        <div class="subtotal-preview" style="margin-top: 0.5rem; font-size: 0.78rem; color: var(--text-muted); text-align: right;"></div>
                    </div>
                </div>
            </div>

            <div style="margin-top: 1.5rem; display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">
                    Simpan sebagai Draft PO
                </button>
                <a href="{{ route('po.index') }}" class="btn btn-secondary" style="flex: 1;">
                    Batal
                </a>
            </div>
        </form>
    </div>

    <script>
        const products = @json($products->values());
        let itemCount = 1;

        function buildProductOptions() {
            return products.map(p =>
                `<option value="${p.kode_produk}" data-harga="${parseFloat(p.harga_beli) || 0}">${p.nama_produk} (${p.kode_produk}) - ${p.uom}</option>`
            ).join('');
        }

        function formatRupiah(val) {
            if (!val || isNaN(val)) return '';
            return 'Rp ' + parseInt(val).toLocaleString('id-ID');
        }

        function attachRowListeners(row) {
            const productSelect = row.querySelector('.produk-select');
            const hargaInput    = row.querySelector('.harga-input');
            const qtyInput      = row.querySelector('.qty-input');
            const subtotalDiv   = row.querySelector('.subtotal-preview');

            // Prefill harga from product selection
            function onProductChange() {
                const selectedOption = productSelect.options[productSelect.selectedIndex];
                const harga = parseFloat(selectedOption?.dataset?.harga) || 0;
                if (harga > 0 && !hargaInput.value) {
                    hargaInput.value = harga;
                }
                updateSubtotal();
            }

            function updateSubtotal() {
                const harga = parseFloat(hargaInput.value) || 0;
                const qty   = parseInt(qtyInput.value) || 0;
                if (harga > 0 && qty > 0) {
                    subtotalDiv.innerHTML = `<span style="color: var(--accent-blue); font-weight: 500;">Subtotal: ${formatRupiah(harga * qty)}</span>`;
                } else {
                    subtotalDiv.innerHTML = '';
                }
            }

            // Handle both native change and Select2
            productSelect.addEventListener('change', onProductChange);
            hargaInput.addEventListener('input', updateSubtotal);
            qtyInput.addEventListener('input', updateSubtotal);

            if (typeof jQuery !== 'undefined' && typeof jQuery.fn.select2 !== 'undefined') {
                $(productSelect).on('select2:select', onProductChange);
            }
        }

        // Attach to the first (existing) row
        document.querySelectorAll('.po-item-row').forEach(row => attachRowListeners(row));

        document.getElementById('addItemBtn').addEventListener('click', function () {
            const container = document.getElementById('itemsContainer');
            const index = itemCount++;

            const newRow = document.createElement('div');
            newRow.className = 'po-item-row';
            newRow.style.cssText = 'margin-bottom: 1rem; padding: 1rem; background: rgba(255,255,255,0.03); border-radius: 0.5rem; border: 1px solid rgba(255,255,255,0.08);';
            newRow.innerHTML = `
                <div style="display: grid; grid-template-columns: 1fr 120px 160px auto; gap: 0.75rem; align-items: end;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Produk *</label>
                        <select name="items[${index}][produk_id]" class="form-control select2 produk-select" required>
                            <option value="" disabled selected>-- Pilih Produk --</option>
                            ${buildProductOptions()}
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Qty *</label>
                        <input type="number" name="items[${index}][qty_pesan]" class="form-control qty-input" min="1" placeholder="100" required>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Harga Satuan (Rp) <span style="font-size:0.7rem; color:var(--accent-blue); font-weight:400;">✏️ override</span></label>
                        <input type="number" name="items[${index}][harga_satuan]" class="form-control harga-input" min="0" step="1" placeholder="Auto dari master">
                    </div>
                    <div style="display: flex; align-items: flex-end;">
                        <button type="button" class="btn btn-danger remove-item" style="padding: 6px 12px; min-height: 44px;">&times;</button>
                    </div>
                </div>
                <div class="subtotal-preview" style="margin-top: 0.5rem; font-size: 0.78rem; color: var(--text-muted); text-align: right;"></div>`;

            container.appendChild(newRow);
            if (typeof jQuery !== 'undefined' && typeof jQuery.fn.select2 !== 'undefined') {
                $(newRow).find('.select2').select2({ width: '100%' });
            }
            attachRowListeners(newRow);
            updateRemoveButtons();
        });

        document.getElementById('itemsContainer').addEventListener('click', function (e) {
            if (e.target.classList.contains('remove-item') && !e.target.disabled) {
                e.target.closest('.po-item-row').remove();
                updateRemoveButtons();
            }
        });

        function updateRemoveButtons() {
            const rows = document.querySelectorAll('.po-item-row');
            rows.forEach(row => {
                const btn = row.querySelector('.remove-item');
                const only = rows.length === 1;
                btn.disabled = only;
                btn.style.opacity = only ? '0.4' : '1';
                btn.style.cursor  = only ? 'not-allowed' : 'pointer';
            });
        }
    </script>
</x-app-layout>
