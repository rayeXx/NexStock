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
                <tbody>
                    @forelse($inbounds as $batch)
                        <tr>
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
</x-app-layout>
