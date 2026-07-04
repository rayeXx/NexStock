<x-app-layout>
    <div style="margin-bottom: 2rem;">
        <a href="{{ route('po.index') }}" style="color: var(--accent-blue); text-decoration: none; font-weight: 500; font-size: 0.9rem;">
            &larr; Kembali ke Daftar PO
        </a>
        <h1 style="margin-top: 0.5rem;">Detail Purchase Order</h1>
        <p>Rincian dokumen pengadaan dan daftar item barang yang dipesan.</p>
    </div>

    <div style="display: grid; grid-template-columns: 1fr; gap: 1.5rem;">
        {{-- PO Header Info --}}
        <div class="glass-card">
            <div class="card-title">
                <span>Informasi PO: <code>{{ $po->po_number }}</code></span>
                @if($po->status === 'Draft')
                    <span class="badge badge-blue">Draft</span>
                @elseif($po->status === 'Pending Approval')
                    <span class="badge badge-yellow pulse-indicator">Menunggu Persetujuan Owner</span>
                @elseif($po->status === 'Approved')
                    <span class="badge badge-green">Disetujui (Approved)</span>
                @elseif($po->status === 'Partial')
                    <span class="badge badge-yellow">🟡 Partial</span>
                @elseif($po->status === 'Completed')
                    <span class="badge badge-green">🟢 Completed</span>
                @elseif($po->status === 'Cancelled')
                    <span class="badge badge-red">🔴 Cancelled</span>
                @else
                    <span class="badge badge-red">Ditolak (Rejected)</span>
                @endif
            </div>
            <div class="grid-2">
                <div>
                    <p style="margin-bottom: 0.25rem;"><strong>Supplier:</strong> {{ $po->supplier->nama_supplier }}</p>
                    <p style="margin-bottom: 0.25rem;"><strong>Kontak Supplier:</strong> {{ $po->supplier->kontak }}</p>
                    <p style="margin-bottom: 0;"><strong>Dibuat oleh:</strong> {{ $po->creator->name }} ({{ strtoupper(str_replace('_', ' ', $po->creator->role)) }})</p>
                </div>
                <div>
                    <p style="margin-bottom: 0.25rem;"><strong>Tgl. Dibuat:</strong> {{ $po->created_at->format('d F Y, H:i') }}</p>
                    <p style="margin-bottom: 0;"><strong>Total Nilai Pengadaan:</strong>
                        <span style="color: var(--accent-blue); font-size: 1.1rem; font-weight: 700;">Rp {{ number_format($po->total_harga, 0, ',', '.') }}</span>
                    </p>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div style="margin-top: 1.5rem; display: flex; gap: 0.75rem; flex-wrap: wrap; border-top: 1px solid var(--border-color); padding-top: 1.25rem;">
                @if($po->status === 'Draft' && (auth()->user()->role === 'admin_gudang' || auth()->user()->role === 'owner'))
                    <form action="{{ route('po.submit', $po->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-primary">Ajukan ke Owner untuk Persetujuan</button>
                    </form>
                @endif

                @if($po->status === 'Pending Approval' && auth()->user()->role === 'owner')
                    <form action="{{ route('po.approve', $po->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success">✓ Setujui Purchase Order Ini</button>
                    </form>
                    <form action="{{ route('po.reject', $po->id) }}" method="POST" onsubmit="return confirm('Tolak PO ini?');">
                        @csrf
                        <button type="submit" class="btn btn-danger">✕ Tolak Purchase Order Ini</button>
                    </form>
                @endif

                @if(in_array($po->status, ['Approved', 'Partial']))
                    <a href="{{ route('inbound.create', ['po_id' => $po->id]) }}" class="btn btn-success">
                        → Proses Penerimaan Barang Masuk
                    </a>
                @endif
            </div>
        </div>

        {{-- PO Items Detail Table --}}
        <div class="glass-card">
            <div class="card-title">Rincian Item Barang yang Dipesan</div>
            <div class="table-responsive">
                <table class="table-premium">
                    <thead>
                        <tr>
                            <th>Kode SKU</th>
                            <th>Nama Produk</th>
                            <th>Satuan</th>
                            <th>Qty Dipesan</th>
                            <th>Qty Diterima</th>
                            <th>Qty Sisa</th>
                            <th>Status Penerimaan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($po->details as $detail)
                            @php
                                $sisaQty = $detail->qty_pesan - $detail->qty_diterima;
                            @endphp
                            <tr>
                                <td><code>{{ $detail->produk_id }}</code></td>
                                <td><strong>{{ $detail->product->nama_produk }}</strong></td>
                                <td>{{ $detail->product->uom }}</td>
                                <td>{{ $detail->qty_pesan }}</td>
                                <td>
                                    @if($detail->qty_diterima > 0)
                                        <strong style="color: var(--accent-green);">{{ $detail->qty_diterima }}</strong>
                                    @else
                                        <span style="color: var(--text-muted);">0</span>
                                    @endif
                                </td>
                                <td>
                                    @if($sisaQty > 0)
                                        <span style="color: var(--accent-yellow);">{{ $sisaQty }}</span>
                                    @else
                                        <strong style="color: var(--accent-green);">Lunas</strong>
                                    @endif
                                </td>
                                <td>
                                    @if($detail->qty_diterima >= $detail->qty_pesan)
                                        <span class="badge badge-green">🟢 Selesai</span>
                                    @elseif($detail->qty_diterima > 0)
                                        <span class="badge badge-yellow">🟡 Partial</span>
                                    @else
                                        <span class="badge" style="background:rgba(148,163,184,0.15); color:#94a3b8;">⚪ Pending</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Receiving History --}}
        @if($po->receivingHistory && $po->receivingHistory->count() > 0)
        <div class="glass-card">
            <div class="card-title">
                <span>Riwayat Penerimaan Barang</span>
                <span class="badge badge-blue">{{ $po->receivingHistory->count() }} Penerimaan</span>
            </div>
            <div class="table-responsive">
                <table class="table-premium">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Produk</th>
                            <th>Qty Diterima</th>
                            <th>No. Batch</th>
                            <th>Diterima Oleh</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($po->receivingHistory->sortByDesc('received_at') as $history)
                            <tr>
                                <td>{{ $history->received_at->format('d M Y, H:i') }}</td>
                                <td>
                                    <strong>{{ $history->product->nama_produk }}</strong><br>
                                    <span style="font-size:0.75rem; color:var(--text-muted);">{{ $history->produk_id }}</span>
                                </td>
                                <td><strong style="color: var(--accent-green);">{{ $history->qty_received }} {{ $history->product->uom }}</strong></td>
                                <td><code>{{ $history->batch_number }}</code></td>
                                <td>{{ $history->receiver->name }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
</x-app-layout>
