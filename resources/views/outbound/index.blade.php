<x-app-layout>
    <div class="flex-between" style="margin-bottom: 2rem;">
        <div>
            <h1>Riwayat Barang Keluar (Outbound)</h1>
            <p>Histori pengurangan stok berbasis FEFO — produk dengan tanggal kedaluwarsa terdekat dikeluarkan terlebih dahulu.</p>
        </div>
        <a href="{{ route('outbound.create') }}" class="btn btn-primary">
            + Proses Barang Keluar
        </a>
    </div>

    <div class="glass-card">
        <div class="table-responsive">
            <table class="table-premium">
                <thead>
                    <tr>
                        <th>No. Outbound</th>
                        <th>Tujuan Pengiriman</th>
                        <th>Tgl. Keluar</th>
                        <th>Total Item</th>
                        <th>Rincian Produk</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($outbounds as $outbound)
                        <tr>
                            <td><code>{{ $outbound->outbound_number }}</code></td>
                            <td><strong>{{ $outbound->tujuan }}</strong></td>
                            <td>{{ $outbound->tanggal_keluar->format('d M Y') }}</td>
                            <td>{{ $outbound->details->count() }} jenis produk</td>
                            <td>
                                @foreach($outbound->details as $detail)
                                    <div style="font-size: 0.8rem; color: var(--text-muted);">
                                        {{ $detail->product->nama_produk }} — <strong>{{ $detail->qty_keluar }} {{ $detail->product->uom }}</strong>
                                        (Batch: <code>{{ $detail->batch_number }}</code>)
                                    </div>
                                @endforeach
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 2rem; color: var(--text-muted);">Belum ada riwayat barang keluar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
