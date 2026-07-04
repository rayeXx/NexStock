<x-app-layout>
    <div class="flex-between" style="margin-bottom: 2rem;">
        <div>
            <h1>Riwayat Pengajuan Restock</h1>
            <p>Daftar pengajuan restock yang telah Anda kirimkan beserta status review-nya.</p>
        </div>
        <a href="{{ route('restock-request.create') }}" class="btn btn-primary">
            + Ajukan Restock Baru
        </a>
    </div>

    <div class="glass-card">
        <div class="table-responsive">
            <table class="table-premium">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Qty Diajukan</th>
                        <th>Alasan</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $req)
                        <tr>
                            <td>
                                <strong>{{ $req->product->nama_produk }}</strong><br>
                                <span style="font-size:0.75rem; color:var(--text-muted);">{{ $req->produk_id }}</span>
                            </td>
                            <td><strong>{{ $req->qty_request }}</strong> {{ $req->product->uom }}</td>
                            <td style="max-width:200px; font-size:0.85rem;">{{ $req->alasan }}</td>
                            <td>{{ $req->created_at->format('d M Y, H:i') }}</td>
                            <td>
                                @if($req->status === 'Menunggu Review')
                                    <span class="badge badge-yellow pulse-indicator">⏳ Menunggu Review</span>
                                @elseif($req->status === 'Approved')
                                    <span class="badge badge-green">✅ Approved</span>
                                @else
                                    <span class="badge badge-red">❌ Rejected</span>
                                @endif
                            </td>
                            <td style="font-size:0.85rem;">
                                @if($req->status === 'Approved' && $req->reviewer)
                                    <span style="color:var(--accent-green);">Disetujui oleh {{ $req->reviewer->name }}</span><br>
                                    <span style="color:var(--text-muted); font-size:0.78rem;">{{ $req->reviewed_at->format('d M Y, H:i') }}</span>
                                @elseif($req->status === 'Rejected' && $req->reviewer)
                                    <span style="color:var(--accent-red);">Ditolak: {{ $req->alasan_reject }}</span><br>
                                    <span style="color:var(--text-muted); font-size:0.78rem;">oleh {{ $req->reviewer->name }} — {{ $req->reviewed_at->format('d M Y, H:i') }}</span>
                                @else
                                    <span style="color:var(--text-muted);">Belum di-review</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align:center; padding:2rem; color:var(--text-muted);">
                                Belum ada pengajuan restock. Klik "+ Ajukan Restock Baru" untuk membuat pengajuan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
