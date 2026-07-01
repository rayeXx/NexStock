<x-app-layout>
    <div style="margin-bottom: 2rem;">
        <a href="{{ route('opname.index') }}" style="color: var(--accent-blue); text-decoration: none; font-weight: 500; font-size: 0.9rem;">
            &larr; Kembali ke Riwayat Opname
        </a>
        <h1 style="margin-top: 0.5rem;">Detail Sesi Stock Opname #{{ $opname->id }}</h1>
        <p>Tanggal Audit: <strong>{{ $opname->tanggal_opname->format('d F Y') }}</strong> — Dilakukan oleh: <strong>{{ $opname->creator->name }}</strong></p>
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
