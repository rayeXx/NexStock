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

    <div class="glass-card" style="max-width: 800px;">
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
                        <tr>
                            <td><code>#{{ $supplier->id }}</code></td>
                            <td><strong>{{ $supplier->nama_supplier }}</strong></td>
                            <td>{{ $supplier->kontak }}</td>
                            <td>{{ $supplier->purchaseOrders->count() }} PO</td>
                            <td>
                                <div style="display: flex; gap: 0.5rem;">
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
</x-app-layout>
