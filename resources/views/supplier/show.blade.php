<x-app-layout>
    <div style="margin-bottom: 2rem;">
        <a href="{{ route('supplier.index') }}" style="color: var(--accent-blue); text-decoration: none; font-weight: 500; font-size: 0.9rem;">
            &larr; Kembali ke Daftar Supplier
        </a>
        <h1 style="margin-top: 0.5rem;">Evaluasi Performa Supplier</h1>
        <p>Analitik kinerja pengiriman dan kualitas barang dari mitra supplier ini.</p>
    </div>

    {{-- Supplier Header Card --}}
    <div class="glass-card" style="margin-bottom: 1.5rem;">
        <div style="display: flex; align-items: center; gap: 1.5rem; flex-wrap: wrap;">
            <div style="width: 56px; height: 56px; border-radius: 12px; background: linear-gradient(135deg, rgba(56,189,248,0.2), rgba(99,102,241,0.2)); border: 1px solid rgba(56,189,248,0.3); display: flex; align-items: center; justify-content: center; color: var(--accent-blue); font-weight: 700; font-size: 1.3rem; flex-shrink: 0;">
                {{ strtoupper(substr($supplier->nama_supplier, 0, 2)) }}
            </div>
            <div style="flex: 1;">
                <h2 style="margin: 0 0 0.25rem; font-size: 1.4rem;">{{ $supplier->nama_supplier }}</h2>
                <div style="display: flex; align-items: center; gap: 0.5rem; color: var(--text-muted); font-size: 0.9rem;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    <span>{{ $supplier->kontak }}</span>
                </div>
            </div>
            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                <a href="{{ route('supplier.edit', $supplier->id) }}" class="btn btn-secondary" style="font-size: 0.85rem; padding: 6px 14px;">Edit Supplier</a>
            </div>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">

        {{-- Lead Time Delay --}}
        @php
            $ltDelay     = $avgLeadTimeDelay;
            $ltColor     = 'var(--accent-green)';
            $ltBadge     = 'badge-green';
            $ltIcon      = '✅';
            $ltLabel     = 'Tepat Waktu';
            if ($ltDelay !== null) {
                if ($ltDelay > 7) {
                    $ltColor = 'var(--accent-red)'; $ltBadge = 'badge-red'; $ltIcon = '🔴'; $ltLabel = 'Sering Terlambat';
                } elseif ($ltDelay > 0) {
                    $ltColor = 'var(--accent-yellow)'; $ltBadge = 'badge-yellow'; $ltIcon = '⚠️'; $ltLabel = 'Sedikit Terlambat';
                }
            }
        @endphp
        <div class="glass-card" style="border-color: {{ $ltColor }}33; text-align: center; padding: 1.5rem 1rem;">
            <div style="font-size: 2rem; margin-bottom: 0.5rem;">⏱️</div>
            <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.75rem;">Rata-rata Keterlambatan</div>
            @if($ltDelay !== null)
                <div style="font-size: 2rem; font-weight: 700; color: {{ $ltColor }}; line-height: 1;">
                    {{ $ltDelay > 0 ? '+' : '' }}{{ $ltDelay }}
                </div>
                <div style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.75rem;">hari dari target</div>
                <span class="badge {{ $ltBadge }}" style="font-size: 0.75rem;">{{ $ltIcon }} {{ $ltLabel }}</span>
            @else
                <div style="font-size: 1.5rem; color: var(--text-muted); font-weight: 600;">N/A</div>
                <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">Belum ada PO dengan target kirim</div>
            @endif
        </div>

        {{-- Defect Rate --}}
        @php
            $drColor = 'var(--accent-green)';
            $drBadge = 'badge-green';
            $drIcon  = '✅';
            $drLabel = 'Kualitas Baik';
            if ($defectRate !== null) {
                if ($defectRate > 5) {
                    $drColor = 'var(--accent-red)'; $drBadge = 'badge-red'; $drIcon = '🔴'; $drLabel = 'Kualitas Buruk';
                } elseif ($defectRate > 1) {
                    $drColor = 'var(--accent-yellow)'; $drBadge = 'badge-yellow'; $drIcon = '⚠️'; $drLabel = 'Perlu Dipantau';
                }
            }
        @endphp
        <div class="glass-card" style="border-color: {{ $drColor }}33; text-align: center; padding: 1.5rem 1rem;">
            <div style="font-size: 2rem; margin-bottom: 0.5rem;">🔍</div>
            <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.75rem;">Defect Rate Barang</div>
            @if($defectRate !== null)
                <div style="font-size: 2rem; font-weight: 700; color: {{ $drColor }}; line-height: 1;">
                    {{ $defectRate }}%
                </div>
                <div style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.75rem;">dari total penerimaan</div>
                <span class="badge {{ $drBadge }}" style="font-size: 0.75rem;">{{ $drIcon }} {{ $drLabel }}</span>
            @else
                <div style="font-size: 1.5rem; color: var(--text-muted); font-weight: 600;">N/A</div>
                <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">Belum ada riwayat penerimaan</div>
            @endif
        </div>

        {{-- Total PO --}}
        <div class="glass-card" style="text-align: center; padding: 1.5rem 1rem;">
            <div style="font-size: 2rem; margin-bottom: 0.5rem;">📋</div>
            <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.75rem;">Total Purchase Order</div>
            <div style="font-size: 2rem; font-weight: 700; color: var(--accent-blue); line-height: 1;">{{ $totalPO }}</div>
            <div style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.75rem;">dokumen PO</div>
            <span class="badge badge-blue" style="font-size: 0.75rem;">📦 Riwayat Transaksi</span>
        </div>

        {{-- Total Nilai --}}
        <div class="glass-card" style="text-align: center; padding: 1.5rem 1rem;">
            <div style="font-size: 2rem; margin-bottom: 0.5rem;">💰</div>
            <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.75rem;">Total Nilai Pengadaan</div>
            <div style="font-size: 1.4rem; font-weight: 700; color: var(--accent-green); line-height: 1.2;">
                Rp {{ number_format($totalNilai, 0, ',', '.') }}
            </div>
            <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.25rem;">seluruh riwayat PO</div>
        </div>
    </div>

    {{-- Insight Box --}}
    @if($avgLeadTimeDelay !== null || $defectRate !== null)
    <div class="glass-card" style="margin-bottom: 1.5rem; border-color: rgba(56,189,248,0.25); background: rgba(56,189,248,0.05);">
        <div style="display: flex; align-items: flex-start; gap: 1rem;">
            <div style="font-size: 1.5rem; flex-shrink: 0;">💡</div>
            <div>
                <div style="font-weight: 600; margin-bottom: 0.5rem; color: var(--accent-blue);">Ringkasan Evaluasi Performa</div>
                <ul style="margin: 0; padding-left: 1.25rem; color: var(--text-muted); font-size: 0.88rem; line-height: 1.7;">
                    @if($avgLeadTimeDelay !== null)
                        @if($avgLeadTimeDelay <= 0)
                            <li>✅ Supplier ini <strong>konsisten tepat waktu</strong> — rata-rata pengiriman lebih cepat {{ abs($avgLeadTimeDelay) }} hari dari target.</li>
                        @elseif($avgLeadTimeDelay <= 7)
                            <li>⚠️ Supplier ini <strong>sedikit melebihi target</strong> — rata-rata terlambat {{ $avgLeadTimeDelay }} hari. Pertimbangkan buffer waktu saat membuat PO.</li>
                        @else
                            <li>🔴 Supplier ini <strong>sering terlambat signifikan</strong> — rata-rata {{ $avgLeadTimeDelay }} hari dari target. Evaluasi ulang kelayakan supplier ini.</li>
                        @endif
                    @endif
                    @if($defectRate !== null)
                        @if($defectRate <= 1)
                            <li>✅ Tingkat kecacatan barang <strong>sangat rendah ({{ $defectRate }}%)</strong> — kualitas produk terjaga baik.</li>
                        @elseif($defectRate <= 5)
                            <li>⚠️ Tingkat kecacatan <strong>{{ $defectRate }}%</strong> — perlu pemantauan saat penerimaan barang.</li>
                        @else
                            <li>🔴 Tingkat kecacatan <strong>{{ $defectRate }}%</strong> — sangat tinggi. Lakukan inspeksi ketat pada setiap penerimaan dari supplier ini.</li>
                        @endif
                    @endif
                    @if(count($leadTimeDelays) > 0)
                        <li>Data dihitung dari <strong>{{ count($leadTimeDelays) }} PO</strong> yang memiliki target tanggal pengiriman.</li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
    @endif

    {{-- Riwayat PO --}}
    <div class="glass-card">
        <div class="card-title">
            <span>Riwayat Purchase Order</span>
            <span class="badge badge-blue">{{ $totalPO }} PO</span>
        </div>
        @if($purchaseOrders->count() > 0)
        <div class="table-responsive">
            <table class="table-premium" style="font-size: 0.87rem;">
                <thead>
                    <tr>
                        <th>No. PO</th>
                        <th>Tgl. Dibuat</th>
                        <th>Target Kirim</th>
                        <th>Status</th>
                        <th>Total Nilai</th>
                        <th>Keterlambatan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchaseOrders as $po)
                        @php
                            $delay     = null;
                            $firstHist = $po->receivingHistory->sortBy('received_at')->first();
                            if ($po->target_tanggal_kirim && $firstHist && $firstHist->received_at) {
                                $delay = \Carbon\Carbon::parse($po->target_tanggal_kirim)->startOfDay()
                                            ->diffInDays(\Carbon\Carbon::instance($firstHist->received_at)->startOfDay(), false);
                            }
                        @endphp
                        <tr>
                            <td><code>{{ $po->po_number }}</code></td>
                            <td>{{ $po->created_at->format('d M Y') }}</td>
                            <td>
                                @if($po->target_tanggal_kirim)
                                    <span style="color: var(--accent-yellow);">{{ $po->target_tanggal_kirim->format('d M Y') }}</span>
                                @else
                                    <span style="color: var(--text-muted);">—</span>
                                @endif
                            </td>
                            <td>
                                @if($po->status === 'Draft')
                                    <span class="badge badge-blue">Draft</span>
                                @elseif($po->status === 'Ordered')
                                    <span class="badge badge-yellow">Ordered</span>
                                @elseif($po->status === 'Partially Received')
                                    <span class="badge badge-yellow">Partial</span>
                                @elseif($po->status === 'Completed')
                                    <span class="badge badge-green">Completed</span>
                                @else
                                    <span class="badge">{{ $po->status }}</span>
                                @endif
                            </td>
                            <td style="color: var(--accent-blue); font-weight: 600;">Rp {{ number_format($po->total_harga, 0, ',', '.') }}</td>
                            <td>
                                @if($delay !== null)
                                    @if($delay <= 0)
                                        <span class="badge badge-green">✅ {{ abs($delay) }}h lebih cepat</span>
                                    @elseif($delay <= 7)
                                        <span class="badge badge-yellow">⚠️ +{{ $delay }} hari</span>
                                    @else
                                        <span class="badge badge-red">🔴 +{{ $delay }} hari</span>
                                    @endif
                                @else
                                    <span style="color: var(--text-muted); font-size: 0.8rem;">Belum diterima / no target</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('po.show', $po->id) }}" class="btn btn-secondary" style="padding: 4px 10px; font-size: 0.78rem;">Detail PO</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
            <div style="text-align: center; padding: 2rem; color: var(--text-muted);">Belum ada Purchase Order dari supplier ini.</div>
        @endif
    </div>
</x-app-layout>
