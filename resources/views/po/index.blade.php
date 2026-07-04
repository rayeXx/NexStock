<x-app-layout>
    <div class="flex-between" style="margin-bottom: 2rem;">
        <div>
            <h1>Manajemen Purchase Order</h1>
            <p>Kelola dokumen pengadaan stok dari pemasok dan pantau status persetujuan Owner.</p>
        </div>
        @if(auth()->user()->role === 'admin_gudang')
            <a href="{{ route('po.create') }}" class="btn btn-primary">
                + Buat Draft PO Baru
            </a>
        @endif
    </div>

    <div class="glass-card">
        <div class="table-responsive">
            <table class="table-premium">
                <thead>
                    <tr>
                        <th>No. PO</th>
                        <th>Supplier</th>
                        <th>Dibuat Oleh</th>
                        <th>Total Nilai</th>
                        <th>Status</th>
                        <th>Tgl. Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchaseOrders as $po)
                        <tr>
                            <td><code>{{ $po->po_number }}</code></td>
                            <td>{{ $po->supplier->nama_supplier }}</td>
                            <td>{{ $po->creator->name }}</td>
                            <td>Rp {{ number_format($po->total_harga, 0, ',', '.') }}</td>
                            <td>
                                @if($po->status === 'Draft')
                                    <span class="badge badge-blue">Draft</span>
                                @elseif($po->status === 'Ordered')
                                    <span class="badge badge-yellow pulse-indicator">Ordered</span>
                                @elseif($po->status === 'Partially Received')
                                    <span class="badge badge-yellow">🟡 Partially Received</span>
                                @elseif($po->status === 'Completed')
                                    <span class="badge badge-green">🟢 Completed</span>
                                @elseif($po->status === 'Cancelled')
                                    <span class="badge badge-red">🔴 Cancelled</span>
                                @else
                                    <span class="badge badge-red">{{ $po->status }}</span>
                                @endif
                            </td>
                            <td>{{ $po->created_at->format('d M Y') }}</td>
                            <td>
                                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                    <a href="{{ route('po.show', $po->id) }}" class="btn btn-secondary" style="padding: 5px 10px; min-height:36px; min-width:36px; font-size: 0.8rem;">
                                        Detail
                                    </a>
                                    {{-- Order to Supplier (Admin, Draft only) --}}
                                    @if($po->status === 'Draft' && auth()->user()->role === 'admin_gudang')
                                        <form action="{{ route('po.order', $po->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Pesan PO ini ke Supplier?');">
                                            @csrf
                                            <button type="submit" class="btn btn-primary" style="padding: 5px 10px; min-height:36px; min-width:36px; font-size: 0.8rem;">
                                                Pesan
                                            </button>
                                        </form>
                                    @endif
                                    {{-- Delete Draft --}}
                                    @if($po->status === 'Draft' && auth()->user()->role === 'admin_gudang')
                                        <form action="{{ route('po.destroy', $po->id) }}" method="POST" onsubmit="return confirm('Hapus draft PO ini?');" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger" style="padding: 5px 10px; min-height:36px; min-width:36px; font-size: 0.8rem;">
                                                Hapus
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 2rem; color: var(--text-muted);">Belum ada Purchase Order. Buat Draft PO baru untuk memulai pengadaan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
