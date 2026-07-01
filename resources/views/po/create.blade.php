<x-app-layout>
    <div style="margin-bottom: 2rem;">
        <a href="{{ route('po.index') }}" style="color: var(--accent-blue); text-decoration: none; font-weight: 500; font-size: 0.9rem;">
            &larr; Kembali ke Daftar PO
        </a>
        <h1 style="margin-top: 0.5rem;">Buat Draft Purchase Order Baru</h1>
        <p>Susun daftar kebutuhan pengadaan stok dari supplier pilihan sebelum diajukan ke Owner.</p>
    </div>

    <div class="glass-card" style="max-width: 800px;">
        <form action="{{ route('po.store') }}" method="POST" id="poForm">
            @csrf

            <div class="form-group">
                <label class="form-label" for="supplier_id">Pilih Supplier *</label>
                <select name="supplier_id" id="supplier_id" class="form-control" required>
                    <option value="" disabled selected>-- Pilih Mitra Supplier --</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                            {{ $supplier->nama_supplier }} ({{ $supplier->kontak }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="border-top: 1px solid var(--border-color); margin: 1.5rem 0; padding-top: 1.5rem;">
                <div class="flex-between" style="margin-bottom: 1rem;">
                    <h3 style="font-size: 1.05rem; font-weight: 600;">Daftar Item Pemesanan</h3>
                    <button type="button" id="addItemBtn" class="btn btn-secondary" style="padding: 6px 14px; min-height: 36px; font-size: 0.85rem;">
                        + Tambah Item
                    </button>
                </div>

                <div id="itemsContainer">
                    <div class="po-item-row" style="display: grid; grid-template-columns: 1fr auto; gap: 1rem; margin-bottom: 1rem; padding: 1rem; background: rgba(255,255,255,0.03); border-radius: 0.5rem; border: 1px solid var(--border-color);">
                        <div class="grid-2" style="gap: 0.75rem;">
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label">Produk *</label>
                                <select name="items[0][produk_id]" class="form-control" required>
                                    <option value="" disabled selected>-- Pilih Produk --</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->kode_produk }}">{{ $product->nama_produk }} ({{ $product->kode_produk }}) - {{ $product->uom }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label">Qty Dipesan *</label>
                                <input type="number" name="items[0][qty_pesan]" class="form-control" min="1" placeholder="Contoh: 100" required>
                            </div>
                        </div>
                        <div style="display: flex; align-items: flex-end; padding-bottom: 0;">
                            <button type="button" class="btn btn-danger remove-item" style="padding: 6px 12px; min-height: 44px; opacity: 0.4; cursor: not-allowed;" disabled>
                                &times;
                            </button>
                        </div>
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
        const products = @json($products);
        let itemCount = 1;

        document.getElementById('addItemBtn').addEventListener('click', function () {
            const container = document.getElementById('itemsContainer');
            const index = itemCount++;

            const productOptions = products.map(p =>
                `<option value="${p.kode_produk}">${p.nama_produk} (${p.kode_produk}) - ${p.uom}</option>`
            ).join('');

            const newRow = document.createElement('div');
            newRow.className = 'po-item-row';
            newRow.style.cssText = 'display: grid; grid-template-columns: 1fr auto; gap: 1rem; margin-bottom: 1rem; padding: 1rem; background: rgba(255,255,255,0.03); border-radius: 0.5rem; border: 1px solid rgba(255,255,255,0.08);';
            newRow.innerHTML = `
                <div class="grid-2" style="gap: 0.75rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Produk *</label>
                        <select name="items[${index}][produk_id]" class="form-control" required>
                            <option value="" disabled selected>-- Pilih Produk --</option>
                            ${productOptions}
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Qty Dipesan *</label>
                        <input type="number" name="items[${index}][qty_pesan]" class="form-control" min="1" placeholder="Contoh: 100" required>
                    </div>
                </div>
                <div style="display: flex; align-items: flex-end;">
                    <button type="button" class="btn btn-danger remove-item" style="padding: 6px 12px; min-height: 44px;">&times;</button>
                </div>`;
            container.appendChild(newRow);
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
            rows.forEach((row, index) => {
                const btn = row.querySelector('.remove-item');
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
