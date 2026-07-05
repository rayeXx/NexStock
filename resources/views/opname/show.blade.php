<x-app-layout>
    <div style="margin-bottom: 2rem;">
        <a href="{{ route('opname.index') }}" style="color: var(--accent-blue); text-decoration: none; font-weight: 500; font-size: 0.9rem;">
            &larr; Kembali ke Riwayat Opname
        </a>
        <div style="display: flex; align-items: center; gap: 0.75rem; margin-top: 0.5rem; flex-wrap: wrap;">
            <h1 style="margin: 0;">Detail Sesi Stock Opname #{{ $opname->id }}</h1>
            @if(($opname->status ?? 'Approved') === 'Pending Approval')
                <span class="badge badge-yellow" style="font-size: 0.85rem; padding: 0.35rem 0.75rem;">🟡 Pending Approval</span>
            @else
                <span class="badge badge-green" style="font-size: 0.85rem; padding: 0.35rem 0.75rem;">🟢 Approved / Sah</span>
            @endif
        </div>
        <p style="margin-top: 0.5rem; margin-bottom: 0.5rem;">Tanggal Audit: <strong>{{ $opname->tanggal_opname->format('d F Y') }}</strong> — Dilakukan oleh: <strong>{{ $opname->creator->name }}</strong></p>
        @if(($opname->status ?? 'Approved') === 'Approved' && $opname->approver)
            <p style="font-size: 0.85rem; color: var(--text-muted); margin: 0 0 1rem 0;">
                Disahkan oleh: <strong>{{ $opname->approver->name }}</strong> pada <strong>{{ $opname->approved_at->format('d F Y, H:i') }}</strong>
            </p>
        @endif
    </div>

    @php
        $totalSelisih = $opname->details->sum('selisih');
    @endphp

    <div class="grid-3" style="margin-bottom: 1.5rem;">
        <div class="glass-card metric-box">
            <span class="metric-label">Total Batch Diaudit</span>
            <span class="metric-value">{{ $opname->details->count() }}</span>
        </div>
        <div class="glass-card metric-box">
            <span class="metric-label">Total Selisih Stok</span>
            <span class="metric-value {{ $totalSelisih == 0 ? 'accent-green' : 'accent-blue' }}">
                {{ $totalSelisih > 0 ? '+' : '' }}{{ $totalSelisih }}
            </span>
        </div>
        <div class="glass-card metric-box">
            <span class="metric-label">Akurasi Data</span>
            @php
                $akuratCount = $opname->details->where('selisih', 0)->count();
                $akurasi = $opname->details->count() > 0 ? round(($akuratCount / $opname->details->count()) * 100, 1) : 100;
            @endphp
            <span class="metric-value {{ $akurasi >= 98 ? 'accent-green' : 'accent-blue' }}">{{ $akurasi }}%</span>
        </div>
    </div>

    @if(($opname->status ?? 'Approved') === 'Pending Approval' && in_array(auth()->user()->role, ['owner', 'admin_gudang']))
        <div class="glass-card" style="margin-bottom: 1.5rem; border-color: var(--accent-blue); background: rgba(56, 189, 248, 0.08); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; padding: 1.5rem;">
            <div>
                <h4 style="margin: 0; color: var(--accent-blue); font-size: 1.05rem; font-weight: 700;">Verifikasi Hasil Opname</h4>
                <p style="margin: 0.25rem 0 0 0; font-size: 0.82rem; color: var(--text-muted);">Sahkan hasil pemeriksaan fisik ini untuk memperbarui stok sistem secara otomatis.</p>
            </div>
            <form action="{{ route('opname.approve', $opname->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menyetujui dan mensahkan hasil opname ini? Tindakan ini akan memperbarui stok sisa batch dan kapasitas rak yang bersangkutan.')">
                @csrf
                <button type="submit" class="btn btn-primary" style="padding: 0.6rem 1.5rem; font-weight: 600;">
                    ✓ Sahkan Hasil Audit
                </button>
            </form>
        </div>
    @endif

    <div class="glass-card">
        <div class="card-title">Rincian Hasil Audit per Batch</div>
        <div class="table-responsive">
            <table class="table-premium">
                <thead>
                    <tr>
                        <th>Batch No.</th>
                        <th>Produk</th>
                        <th>Stok Sistem (Sebelum)</th>
                        <th>Stok Fisik (Audit)</th>
                        <th>Selisih</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($opname->details as $detail)
                        <tr>
                            <td><code>{{ $detail->batch_number }}</code></td>
                            <td><strong>{{ $detail->product->nama_produk }}</strong></td>
                            <td>{{ $detail->qty_sistem }}</td>
                            <td>{{ $detail->qty_fisik }}</td>
                            <td>
                                @if($detail->selisih == 0)
                                    <span style="color: var(--accent-green); font-weight: 700;">0 ✓</span>
                                @elseif($detail->selisih > 0)
                                    <span style="color: var(--accent-yellow); font-weight: 700;">+{{ $detail->selisih }} (Surplus)</span>
                                @else
                                    <span style="color: var(--accent-red); font-weight: 700;">{{ $detail->selisih }} (Hilang)</span>
                                @endif
                            </td>
                            <td>{{ $detail->catatan ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
