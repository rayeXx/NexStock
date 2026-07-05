<x-app-layout>
    <div class="flex-between" style="margin-bottom: 2rem;">
        <div>
            <h1>Master Data Supplier</h1>
            <p>Kelola daftar mitra pemasok/supplier barang dan informasi kontak resmi.</p>
        </div>
        <a href="{{ route('supplier.create') }}" class="btn btn-primary">
            + Tambah Supplier Baru
        </a>
    </div>

    {{-- Search Bar --}}
    <div class="glass-card" style="margin-bottom: 1.5rem; padding: 1rem 1.25rem;">
        <div style="position:relative; max-width:400px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); pointer-events:none;">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text" id="supplierSearch" class="form-control" placeholder="Cari nama supplier atau kontak..." style="padding-left:38px; min-height:40px; font-size:0.88rem;">
        </div>
    </div>

    <div class="glass-card">
        <div class="table-responsive">
            <table class="table-premium">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Supplier</th>
                        <th>Kontak Telepon / PIC</th>
                        <th>Total Pengadaan PO</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suppliers as $supplier)
                        <tr data-search="{{ strtolower($supplier->nama_supplier . ' ' . $supplier->kontak) }}">
                            <td><code>#{{ $supplier->id }}</code></td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                    <div style="width: 32px; height: 32px; border-radius: 6px; background: rgba(56, 189, 248, 0.1); border: 1px solid rgba(56, 189, 248, 0.2); display: flex; align-items: center; justify-content: center; color: var(--accent-blue); font-weight: 600; font-size: 0.85rem;">
                                        {{ strtoupper(substr($supplier->nama_supplier, 0, 2)) }}
                                    </div>
                                    <strong>{{ $supplier->nama_supplier }}</strong>
                                </div>
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.5rem; color: var(--text-muted);">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                                    </svg>
                                    <span>{{ $supplier->kontak }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-blue">{{ $supplier->purchaseOrders->count() }} PO</span>
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.5rem;">
                                    <a href="{{ route('supplier.show', $supplier->id) }}" class="btn btn-secondary" style="padding: 6px 12px; min-height:36px; font-size: 0.85rem; background: rgba(56,189,248,0.12); border-color: rgba(56,189,248,0.3); color: var(--accent-blue);" title="Lihat KPI Performa Supplier">
                                        📊 KPI
                                    </a>
                                    <a href="{{ route('supplier.edit', $supplier->id) }}" class="btn btn-secondary" style="padding: 6px 12px; min-height:36px; min-width:36px; font-size: 0.85rem;">
                                        Edit
                                    </a>
                                    <form action="{{ route('supplier.destroy', $supplier->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus supplier ini?');" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger" style="padding: 6px 12px; min-height:36px; min-width:36px; font-size: 0.85rem;">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 2rem; color: var(--text-muted);">Belum ada data supplier. Klik "+ Tambah Supplier Baru" untuk menambahkan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('supplierSearch');
        const tbody = document.querySelector('.table-premium tbody');
        let timer;

        searchInput.addEventListener('input', function() {
            clearTimeout(timer);
            timer = setTimeout(() => {
                const val = this.value.toLowerCase().trim();
                const rows = tbody.querySelectorAll('tr[data-search]');
                let visibleCount = 0;

                rows.forEach(row => {
                    const data = row.getAttribute('data-search');
                    const match = !val || data.includes(val);
                    row.style.display = match ? '' : 'none';
                    if (match) visibleCount++;
                });

                let noResult = document.getElementById('noResultSupplier');
                if (visibleCount === 0 && rows.length > 0) {
                    if (!noResult) {
                        const tr = document.createElement('tr');
                        tr.id = 'noResultSupplier';
                        tr.innerHTML = '<td colspan="5" style="text-align:center; padding:2rem; color:var(--text-muted);">Tidak ada supplier yang sesuai pencarian.</td>';
                        tbody.appendChild(tr);
                    }
                } else if (noResult) {
                    noResult.remove();
                }
            }, 200);
        });
    });
    </script>
</x-app-layout>
