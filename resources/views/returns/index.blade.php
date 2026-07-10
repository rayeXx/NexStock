<x-app-layout>
    <div class="flex-between" style="margin-bottom: 2rem;">
        <div>
            <h1>Laporan Retur Barang</h1>
            <p>Histori penerimaan barang rusak dari PO yang masuk ke dalam status retur ke Supplier.</p>
        </div>
    </div>

    <div class="glass-card">
        <div class="table-responsive">
            <table class="table-premium">
                <thead>
                    <tr>
                        <th>PO Number</th>
                        <th>Produk</th>
                        <th>Qty Rusak</th>
                        <th>Alasan Kerusakan</th>
                        <th>Status Retur</th>
                        <th>Tanggal Retur</th>
                        <th>Diterima Oleh</th>
                        <th>Catatan Retur</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($returns as $ret)
                        <tr>
                            <td>
                                <a href="{{ route('po.show', $ret->po_id) }}" style="color: var(--accent-blue); text-decoration: underline;">
                                    {{ $ret->purchaseOrder->po_number }}
                                </a>
                            </td>
                            <td><strong>{{ $ret->product->nama_produk }}</strong></td>
                            <td><strong style="color: var(--accent-red);">{{ $ret->qty_rusak }}</strong></td>
                            <td>{{ $ret->alasan_kerusakan ?? '-' }}</td>
                            <td>
                                @if($ret->status_retur === 'Menunggu Retur')
                                    <span class="badge badge-yellow pulse-indicator">Menunggu Retur</span>
                                @else
                                    <span class="badge badge-green">Sudah Diretur</span>
                                @endif
                            </td>
                            <td>{{ $ret->tanggal_retur ? \Carbon\Carbon::parse($ret->tanggal_retur)->format('d M Y') : '-' }}</td>
                            <td>{{ $ret->receiver->name }}</td>
                            <td>{{ $ret->catatan_retur ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 2rem; color: var(--text-muted);">Belum ada riwayat retur barang.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
