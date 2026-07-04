<x-app-layout>
    <div class="flex-between" style="margin-bottom: 2rem;">
        <div>
            <h1>Master Data Produk</h1>
            <p>Kelola master produk, SKU pabrik, batas minimum stok, dan harga beli persediaan.</p>
        </div>
        <a href="{{ route('product.create') }}" class="btn btn-primary">
            + Tambah Produk Baru
        </a>
    </div>

    {{-- Search & Filter Bar --}}
    <div class="glass-card" style="margin-bottom: 1.5rem; padding: 1rem 1.25rem;">
        <div style="display:flex; align-items:center; gap:1rem; flex-wrap:wrap;">
            {{-- Search Input --}}
            <div style="flex:1; min-width:220px; position:relative;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); pointer-events:none;">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input type="text" id="productSearch" class="form-control" placeholder="Cari nama produk, SKU, atau kategori..." style="padding-left:38px; min-height:40px; font-size:0.88rem;">
            </div>

            {{-- Status Filter Tabs --}}
            <div id="statusFilterGroup" style="display:flex; gap:0.35rem; background:rgba(0,0,0,0.2); padding:0.3rem; border-radius:0.6rem; border:1px solid rgba(255,255,255,0.06);">
                <button type="button" onclick="filterStatus('semua', this)" class="chart-filter-btn chart-filter-active" style="padding:0.4rem 0.85rem; border-radius:0.4rem; font-size:0.78rem; font-weight:600; border:none; cursor:pointer; transition:all 0.2s; background:rgba(56,189,248,0.15); color:#38bdf8;">Semua</button>
                <button type="button" onclick="filterStatus('aman', this)" class="chart-filter-btn" style="padding:0.4rem 0.85rem; border-radius:0.4rem; font-size:0.78rem; font-weight:600; border:none; cursor:pointer; transition:all 0.2s; background:transparent; color:#64748b;">🟢 Aman</button>
                <button type="button" onclick="filterStatus('menipis', this)" class="chart-filter-btn" style="padding:0.4rem 0.85rem; border-radius:0.4rem; font-size:0.78rem; font-weight:600; border:none; cursor:pointer; transition:all 0.2s; background:transparent; color:#64748b;">🟡 Menipis</button>
                <button type="button" onclick="filterStatus('kritis', this)" class="chart-filter-btn" style="padding:0.4rem 0.85rem; border-radius:0.4rem; font-size:0.78rem; font-weight:600; border:none; cursor:pointer; transition:all 0.2s; background:transparent; color:#64748b;">🔴 Kritis</button>
            </div>
        </div>
    </div>

    <div class="glass-card">
        <div class="table-responsive">
            <table class="table-premium">
                <thead>
                    <tr>
                        <th>Kode SKU</th>
                        <th>Nama Produk</th>
                        <th>Kategori</th>
                        <th>Harga Beli</th>
                        <th>Stok Minimum</th>
                        <th>UOM (Satuan)</th>
                        <th>Stok Saat Ini</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="productTableBody">
                    @include('product._table_body')
                </tbody>
            </table>
        </div>
    </div>

    <style>
        .chart-filter-active {
            background: rgba(56,189,248,0.15) !important;
            color: #38bdf8 !important;
        }
        #productTableBody tr {
            transition: opacity 0.2s ease;
        }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('productSearch');
        const tbody = document.getElementById('productTableBody');
        let currentStatus = 'semua';
        let debounceTimer;

        function applyFilters() {
            const search = searchInput.value.toLowerCase().trim();
            const rows = tbody.querySelectorAll('tr[data-status]');

            rows.forEach(row => {
                const status = row.getAttribute('data-status');
                const nama = row.getAttribute('data-nama') || '';
                const sku = row.getAttribute('data-sku') || '';
                const kategori = row.getAttribute('data-kategori') || '';

                let statusMatch = currentStatus === 'semua' || status === currentStatus;
                let searchMatch = !search || nama.includes(search) || sku.includes(search) || kategori.includes(search);

                row.style.display = (statusMatch && searchMatch) ? '' : 'none';
            });

            // Show empty row if all are hidden
            const emptyRow = tbody.querySelector('tr:not([data-status])');
            const visibleRows = tbody.querySelectorAll('tr[data-status][style=""]').length + tbody.querySelectorAll('tr[data-status]:not([style])').length;
            let hiddenCount = 0;
            rows.forEach(r => { if (r.style.display === 'none') hiddenCount++; });

            if (rows.length > 0 && hiddenCount === rows.length) {
                if (!document.getElementById('noResultRow')) {
                    const tr = document.createElement('tr');
                    tr.id = 'noResultRow';
                    tr.innerHTML = '<td colspan="9" style="text-align:center; padding:2rem; color:var(--text-muted);">Tidak ada produk yang sesuai filter.</td>';
                    tbody.appendChild(tr);
                }
            } else {
                const noResult = document.getElementById('noResultRow');
                if (noResult) noResult.remove();
            }
        }

        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(applyFilters, 200);
        });

        window.filterStatus = function(status, btn) {
            currentStatus = status;

            document.querySelectorAll('#statusFilterGroup .chart-filter-btn').forEach(b => {
                b.style.background = 'transparent';
                b.style.color = '#64748b';
            });
            btn.style.background = 'rgba(56,189,248,0.15)';
            btn.style.color = '#38bdf8';

            applyFilters();
        };
    });
    </script>
</x-app-layout>
