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

    {{-- Live Search --}}
    <div class="glass-card" style="margin-bottom: 1.5rem; padding: 1rem 1.25rem;">
        <div style="position:relative; max-width:400px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); pointer-events:none;">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text" id="outboundSearch" class="form-control" placeholder="Cari nomor outbound, tujuan, atau nama produk..." style="padding-left:38px; min-height:40px; font-size:0.88rem;">
        </div>
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
                <tbody id="outboundTableBody">
                    @forelse($outbounds as $outbound)
                        <tr data-search="{{ strtolower($outbound->outbound_number . ' ' . $outbound->tujuan . ' ' . $outbound->details->pluck('product.nama_produk')->implode(' ') . ' ' . $outbound->details->pluck('produk_id')->implode(' ')) }}">
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

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const search = document.getElementById('outboundSearch');
        const tbody = document.getElementById('outboundTableBody');
        let timer;

        search.addEventListener('input', function() {
            clearTimeout(timer);
            timer = setTimeout(() => {
                const val = this.value.toLowerCase().trim();
                const rows = tbody.querySelectorAll('tr[data-search]');
                let visibleCount = 0;

                rows.forEach(row => {
                    const match = !val || row.getAttribute('data-search').includes(val);
                    row.style.display = match ? '' : 'none';
                    if (match) visibleCount++;
                });

                let noResult = document.getElementById('noResultOutbound');
                if (visibleCount === 0 && rows.length > 0) {
                    if (!noResult) {
                        const tr = document.createElement('tr');
                        tr.id = 'noResultOutbound';
                        tr.innerHTML = '<td colspan="5" style="text-align:center; padding:2rem; color:var(--text-muted);">Tidak ada data yang sesuai pencarian.</td>';
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
