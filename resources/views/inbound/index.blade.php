<x-app-layout>
    <div class="flex-between" style="margin-bottom: 2rem;">
        <div>
            <h1>Riwayat Barang Masuk (Inbound)</h1>
            <p>Seluruh histori penerimaan batch produk dari supplier berbasis validasi Purchase Order.</p>
        </div>
        <a href="{{ route('inbound.create') }}" class="btn btn-primary">
            + Proses Barang Masuk Baru
        </a>
    </div>

    {{-- Live Search --}}
    <div class="glass-card" style="margin-bottom: 1.5rem; padding: 1rem 1.25rem;">
        <div style="position:relative; max-width:400px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); pointer-events:none;">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text" id="inboundSearch" class="form-control" placeholder="Cari nama produk, SKU, atau batch..." style="padding-left:38px; min-height:40px; font-size:0.88rem;">
        </div>
    </div>

    <div class="glass-card">
        <div class="table-responsive">
            <table class="table-premium">
                <thead>
                    <tr>
                        <th>No. Batch</th>
                        <th>Produk</th>
                        <th>Referensi PO</th>
                        <th>Lokasi Rak</th>
                        <th>Tgl. Kedaluwarsa</th>
                        <th>Stok Awal</th>
                        <th>Sisa Stok</th>
                        <th>Diterima Tgl.</th>
                    </tr>
                </thead>
                <tbody id="inboundTableBody">
                    @forelse($inbounds as $batch)
                        <tr data-search="{{ strtolower($batch->product->nama_produk . ' ' . $batch->produk_id . ' ' . $batch->batch_number . ' ' . ($batch->product->category->nama_kategori ?? '')) }}">
                            <td><code>{{ $batch->batch_number }}</code></td>
                            <td>
                                <strong>{{ $batch->product->nama_produk }}</strong><br>
                                <span style="font-size: 0.75rem; color: var(--text-muted);">{{ $batch->produk_id }}</span>
                            </td>
                            <td>
                                @if($batch->purchaseOrder)
                                    <a href="{{ route('po.show', $batch->po_id) }}" style="color: var(--accent-blue);">{{ $batch->purchaseOrder->po_number }}</a>
                                @else
                                    <span style="color: var(--text-muted);">N/A</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-blue">Rak {{ $batch->rak_id }}</span>
                            </td>
                            <td>
                                @php
                                    $daysLeft = now()->diffInDays($batch->expired_date, false);
                                @endphp
                                <span style="color: {{ $daysLeft <= 30 ? 'var(--accent-red)' : ($daysLeft <= 90 ? 'var(--accent-yellow)' : 'inherit') }}">
                                    {{ $batch->expired_date->format('d M Y') }}
                                </span>
                                @if($daysLeft <= 30 && $daysLeft > 0)
                                    <span class="badge badge-red" style="margin-left: 4px;">{{ $daysLeft }}h</span>
                                @elseif($daysLeft <= 0)
                                    <span class="badge badge-red" style="margin-left: 4px;">Expired!</span>
                                @endif
                            </td>
                            <td>{{ $batch->stok_awal_batch }}</td>
                            <td>
                                @if($batch->stok_sisa_batch > 0)
                                    <strong style="color: var(--accent-green);">{{ $batch->stok_sisa_batch }}</strong>
                                @else
                                    <span style="color: var(--text-muted);">Habis</span>
                                @endif
                            </td>
                            <td>{{ $batch->created_at->format('d M Y, H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 2rem; color: var(--text-muted);">Belum ada riwayat barang masuk.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const search = document.getElementById('inboundSearch');
        const tbody = document.getElementById('inboundTableBody');
        let timer;

        search.addEventListener('input', function() {
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

                let noResult = document.getElementById('noResultInbound');
                if (visibleCount === 0 && rows.length > 0) {
                    if (!noResult) {
                        const tr = document.createElement('tr');
                        tr.id = 'noResultInbound';
                        tr.innerHTML = '<td colspan="8" style="text-align:center; padding:2rem; color:var(--text-muted);">Tidak ada data yang sesuai pencarian.</td>';
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
