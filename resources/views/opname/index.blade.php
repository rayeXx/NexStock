<x-app-layout>
    <div class="flex-between" style="margin-bottom: 2rem;">
        <div>
            <h1>Riwayat Stock Opname</h1>
            <p>Rekaman sesi audit rekonsiliasi stok fisik gudang vs data sistem.</p>
        </div>
        <a href="{{ route('opname.create') }}" class="btn btn-primary">
            + Mulai Sesi Opname Baru
        </a>
    </div>

    <div class="glass-card">
        <div class="table-responsive">
            <table class="table-premium">
                <thead>
                    <tr>
                        <th>ID Opname</th>
                        <th>Tgl. Audit</th>
                        <th>Dilakukan Oleh</th>
                        <th>Jml. Batch Diaudit</th>
                        <th>Total Selisih</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($opnames as $opname)
                        @php
                            $totalSelisih = $opname->details->sum('selisih');
                        @endphp
                        <tr>
                            <td><code>#{{ $opname->id }}</code></td>
                            <td>{{ $opname->tanggal_opname->format('d F Y') }}</td>
                            <td>{{ $opname->creator->name }}</td>
                            <td>{{ $opname->details->count() }} Batch</td>
                            <td>
                                @if($totalSelisih == 0)
                                    <span class="badge badge-green">0 (Akurat 100%)</span>
                                @elseif($totalSelisih > 0)
                                    <span class="badge badge-yellow">+{{ $totalSelisih }} (Surplus)</span>
                                @else
                                    <span class="badge badge-red">{{ $totalSelisih }} (Kekurangan)</span>
                                @endif
                            </td>
                            <td>
                                @if(($opname->status ?? 'Approved') === 'Pending Approval')
                                    <span class="badge badge-yellow">🟡 Pending Approval</span>
                                @else
                                    <span class="badge badge-green">🟢 Approved / Sah</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('opname.show', $opname->id) }}" class="btn btn-secondary" style="padding: 5px 12px; min-height: 36px; font-size: 0.85rem;">
                                    Lihat Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 2rem; color: var(--text-muted);">Belum ada sesi stock opname yang dilakukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
