<x-app-layout>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h1>Dashboard MIS & Smart Features</h1>
            <p>Rangkuman operasional logistik, indikator performa pemasok, dan prediksi restock barang.</p>
        </div>
        <div style="background: rgba(56, 189, 248, 0.1); padding: 0.75rem 1.25rem; border-radius: 0.5rem; border: 1px solid rgba(56, 189, 248, 0.2);">
            <span style="font-size: 0.9rem; color: var(--accent-blue);"><strong>Hari Ini:</strong> {{ date('d F Y') }}</span>
        </div>
    </div>

    <!-- Error/Anomaly Alert Box if errors are detected -->
    @if(count($errorsDetected) > 0)
        <div class="glass-card" style="border-color: rgba(244, 63, 94, 0.4); background: rgba(244, 63, 94, 0.05);">
            <div class="card-title" style="color: var(--accent-red);">
                <span>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 0.5rem;">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    Deteksi Masalah & Anomali Sistem (System Error Detection)
                </span>
                <span class="badge badge-red pulse-indicator">{{ count($errorsDetected) }} Terdeteksi</span>
            </div>
            <ul style="margin-left: 1.5rem; color: #fecdd3; font-size: 0.9rem;">
                @foreach($errorsDetected as $err)
                    <li style="margin-bottom: 0.35rem;">{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Upper Stats counters row -->
    @if($userRole === 'owner')
    {{-- OWNER KPI: Nilai Aset + PO Stats --}}
    <div class="grid-3" style="margin-bottom: 1.5rem;">
        <div class="glass-card metric-box">
            <span class="metric-label">Kapitalisasi Nilai Aset</span>
            <span class="metric-value accent-blue">Rp {{ number_format($totalAsetValue, 0, ',', '.') }}</span>
            <p>Total nilai modal dari persediaan siap jual di gudang saat ini.</p>
        </div>
        <div class="glass-card metric-box">
            <span class="metric-label">Kapasitas Gudang & Rak</span>
            <span class="metric-value accent-green">{{ $stats['total_stok'] }} Unit</span>
            <p>Terkapitalisasi di {{ $stats['total_rak'] }} lokasi rak pergudangan dinamis.</p>
        </div>
        <div class="glass-card metric-box">
            <span class="metric-label">Karantina Barang Rusak</span>
            <span class="metric-value" style="color: var(--accent-yellow);">{{ $stats['karantina_count'] }} Kasus</span>
            <p>Laporan barang rusak berstatus pending dan sedang menunggu review.</p>
        </div>
    </div>
    <div class="grid-3" style="margin-bottom: 1.5rem;">
        <div class="glass-card metric-box">
            <span class="metric-label">PO Pending Approval</span>
            <span class="metric-value" style="color: #94a3b8;">{{ $poStats['pending'] }}</span>
            <p>Menunggu persetujuan Owner.</p>
        </div>
        <div class="glass-card metric-box">
            <span class="metric-label">PO Partial</span>
            <span class="metric-value" style="color: var(--accent-yellow);">{{ $poStats['partial'] }}</span>
            <p>Penerimaan barang belum lengkap.</p>
        </div>
        <div class="glass-card metric-box">
            <span class="metric-label">PO Completed</span>
            <span class="metric-value accent-green">{{ $poStats['completed'] }}</span>
            <p>Seluruh barang telah diterima.</p>
        </div>
    </div>
    @else
    {{-- ADMIN / STAFF KPI: Operational Stats --}}
    <div class="grid-4" style="margin-bottom: 1.5rem;">
        <div class="glass-card metric-box">
            <span class="metric-label">Total Produk</span>
            <span class="metric-value accent-blue">{{ $stats['total_produk'] }}</span>
            <p>Jumlah SKU terdaftar di master produk.</p>
        </div>
        <div class="glass-card metric-box">
            <span class="metric-label">Barang Hampir Habis</span>
            <span class="metric-value" style="color: var(--accent-red);">{{ $stats['barang_hampir_habis'] }}</span>
            <p>Produk dengan stok mendekati batas minimum.</p>
        </div>
        <div class="glass-card metric-box">
            <span class="metric-label">Barang Masuk Hari Ini</span>
            <span class="metric-value accent-green">{{ $stats['inbound_hari_ini'] }} Unit</span>
            <p>Total unit diterima hari ini.</p>
        </div>
        <div class="glass-card metric-box">
            <span class="metric-label">Barang Keluar Hari Ini</span>
            <span class="metric-value" style="color: var(--accent-yellow);">{{ $stats['outbound_hari_ini'] }} Unit</span>
            <p>Total unit dikeluarkan hari ini.</p>
        </div>
    </div>
    @endif

    {{-- ============================================================ --}}
    {{-- OWNER ONLY: Sales Analytics Section                          --}}
    {{-- ============================================================ --}}
    @if($userRole === 'owner')
    <div style="margin-bottom: 2rem;">

        {{-- ── Section Header ── --}}
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.25rem; flex-wrap:wrap; gap:0.75rem;">
            <div style="display:flex; align-items:center; gap:0.75rem;">
                <div style="width:4px; height:28px; background:linear-gradient(180deg,#38bdf8,#818cf8); border-radius:4px;"></div>
                <div>
                    <h2 style="font-size:1.1rem; font-weight:700; color:#f1f5f9; margin:0;">Analitik Penjualan & Performa Produk</h2>
                    <p style="font-size:0.78rem; color:#64748b; margin:0;">Grafik barang keluar, produk terlaris, slow-moving, dan rekomendasi AI.</p>
                </div>
            </div>
            <span style="font-size:0.75rem; color:#475569; background:rgba(255,255,255,0.03); padding:0.35rem 0.75rem; border-radius:2rem; border:1px solid rgba(255,255,255,0.06);">
                Owner View · Live Data
            </span>
        </div>

        {{-- ── Sales Chart Card ── --}}
        <div class="glass-card" style="margin-bottom:1.25rem; padding:1.5rem;">

            {{-- Chart Header --}}
            <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1rem; margin-bottom:1.5rem;">
                <div>
                    <h3 style="font-size:0.95rem; font-weight:700; color:#e2e8f0; margin:0 0 0.2rem;">Grafik Penjualan (Barang Keluar)</h3>
                    <p style="font-size:0.78rem; color:#64748b; margin:0;">Total unit barang yang dikeluarkan dari gudang per periode.</p>
                </div>
                {{-- Filter Buttons --}}
                <div id="chartFilterGroup" style="display:flex; gap:0.4rem; background:rgba(0,0,0,0.2); padding:0.3rem; border-radius:0.6rem; border:1px solid rgba(255,255,255,0.06);">
                    <button type="button" onclick="switchPeriod('today', this)" class="chart-filter-btn" style="padding:0.4rem 0.85rem; border-radius:0.4rem; font-size:0.78rem; font-weight:600; border:none; cursor:pointer; transition:all 0.2s; background:transparent; color:#64748b;">Hari Ini</button>
                    <button type="button" onclick="switchPeriod('week', this)" class="chart-filter-btn" style="padding:0.4rem 0.85rem; border-radius:0.4rem; font-size:0.78rem; font-weight:600; border:none; cursor:pointer; transition:all 0.2s; background:transparent; color:#64748b;">7 Hari</button>
                    <button type="button" onclick="switchPeriod('thirty', this)" class="chart-filter-btn" style="padding:0.4rem 0.85rem; border-radius:0.4rem; font-size:0.78rem; font-weight:600; border:none; cursor:pointer; transition:all 0.2s; background:transparent; color:#64748b;">30 Hari</button>
                    <button type="button" onclick="switchPeriod('month', this)" id="defaultFilterBtn" class="chart-filter-btn chart-filter-active" style="padding:0.4rem 0.85rem; border-radius:0.4rem; font-size:0.78rem; font-weight:600; border:none; cursor:pointer; transition:all 0.2s; background:rgba(56,189,248,0.15); color:#38bdf8;">Bulan Ini</button>
                </div>
            </div>

            {{-- Summary Stats Row --}}
            <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:1rem; margin-bottom:1.5rem;">
                <div style="background:rgba(56,189,248,0.06); border:1px solid rgba(56,189,248,0.12); border-radius:0.75rem; padding:1rem 1.25rem; text-align:center;">
                    <div id="statTotal" style="font-size:1.5rem; font-weight:800; color:#38bdf8; line-height:1;">—</div>
                    <div style="font-size:0.72rem; color:#64748b; margin-top:0.3rem; text-transform:uppercase; letter-spacing:0.05em;">Total Unit Terjual</div>
                </div>
                <div style="background:rgba(129,140,248,0.06); border:1px solid rgba(129,140,248,0.12); border-radius:0.75rem; padding:1rem 1.25rem; text-align:center;">
                    <div id="statAvg" style="font-size:1.5rem; font-weight:800; color:#818cf8; line-height:1;">—</div>
                    <div style="font-size:0.72rem; color:#64748b; margin-top:0.3rem; text-transform:uppercase; letter-spacing:0.05em;">Rata-rata / Periode</div>
                </div>
                <div style="background:rgba(52,211,153,0.06); border:1px solid rgba(52,211,153,0.12); border-radius:0.75rem; padding:1rem 1.25rem; text-align:center;">
                    <div id="statPeak" style="font-size:1.5rem; font-weight:800; color:#34d399; line-height:1;">—</div>
                    <div style="font-size:0.72rem; color:#64748b; margin-top:0.3rem; text-transform:uppercase; letter-spacing:0.05em;">Penjualan Tertinggi</div>
                </div>
            </div>

            {{-- Chart Canvas --}}
            <div style="height:300px; width:100%; position:relative;">
                <canvas id="salesChart"></canvas>
            </div>
        </div>

        {{-- ── Top Products Row ── --}}
        <div class="top-products-grid">

            {{-- Top 5 Terlaris --}}
            <div class="glass-card" style="padding:1.5rem;">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.25rem;">
                    <div style="display:flex; align-items:center; gap:0.6rem;">
                        <div style="width:32px; height:32px; border-radius:50%; background:linear-gradient(135deg,rgba(52,211,153,0.2),rgba(52,211,153,0.05)); border:1px solid rgba(52,211,153,0.25); display:flex; align-items:center; justify-content:center;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#34d399" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
                        </div>
                        <div>
                            <h3 style="font-size:0.88rem; font-weight:700; color:#e2e8f0; margin:0;">Top 5 Terlaris</h3>
                            <p style="font-size:0.72rem; color:#64748b; margin:0;">30 hari terakhir</p>
                        </div>
                    </div>
                    <span class="badge badge-green" style="font-size:0.68rem;">Best Seller</span>
                </div>
                <div style="display:flex; flex-direction:column; gap:0.85rem;">
                    @forelse($topSellingProducts as $index => $product)
                    <div style="display:flex; align-items:center; gap:0.75rem;">
                        <span style="width:20px; text-align:center; font-size:0.75rem; font-weight:800; color:{{ $index === 0 ? '#fbbf24' : ($index === 1 ? '#94a3b8' : ($index === 2 ? '#b45309' : '#475569')) }}; flex-shrink:0;">
                            {{ $index === 0 ? '🥇' : ($index === 1 ? '🥈' : ($index === 2 ? '🥉' : '#'.($index+1))) }}
                        </span>
                        <div style="flex:1; min-width:0;">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.3rem;">
                                <span style="font-size:0.8rem; font-weight:600; color:#cbd5e1; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:75%;">{{ $product['nama'] }}</span>
                                <span style="font-size:0.78rem; font-weight:700; color:#34d399; flex-shrink:0; margin-left:0.5rem;">{{ number_format($product['total_sold']) }} unit</span>
                            </div>
                            <div style="height:5px; background:rgba(255,255,255,0.05); border-radius:3px; overflow:hidden;">
                                <div style="height:100%; width:{{ $product['percentage'] }}%; background:linear-gradient(90deg,#34d399,#059669); border-radius:3px; transition:width 0.8s ease;"></div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div style="text-align:center; padding:1.5rem 0; color:#475569;">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin:0 auto 0.6rem; display:block; opacity:0.4;"><path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-8 2a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"/></svg>
                        <div style="font-size:0.8rem; font-weight:600; color:#475569;">Belum ada data transaksi</div>
                        <div style="font-size:0.72rem; color:#334155; margin-top:0.25rem;">Data akan muncul setelah ada transaksi barang keluar</div>
                    </div>
                    @endforelse
                </div>
            </div>

            {{-- Top 5 Slow Moving --}}
            <div class="glass-card" style="padding:1.5rem;">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.25rem;">
                    <div style="display:flex; align-items:center; gap:0.6rem;">
                        <div style="width:32px; height:32px; border-radius:50%; background:linear-gradient(135deg,rgba(251,191,36,0.2),rgba(251,191,36,0.05)); border:1px solid rgba(251,191,36,0.25); display:flex; align-items:center; justify-content:center;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#fbbf24" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 18 13.5 8.5 8.5 13.5 1 6"/><polyline points="17 18 23 18 23 12"/></svg>
                        </div>
                        <div>
                            <h3 style="font-size:0.88rem; font-weight:700; color:#e2e8f0; margin:0;">Top 5 Slow Moving</h3>
                            <p style="font-size:0.72rem; color:#64748b; margin:0;">Penjualan terendah · stok masih ada</p>
                        </div>
                    </div>
                    <span class="badge badge-yellow" style="font-size:0.68rem;">Perhatian</span>
                </div>
                <div style="display:flex; flex-direction:column; gap:0.85rem;">
                    @forelse($slowMovingProducts as $index => $product)
                    <div style="display:flex; align-items:center; gap:0.75rem;">
                        <span style="width:20px; text-align:center; font-size:0.75rem; font-weight:700; color:#475569; flex-shrink:0;">#{{ $index + 1 }}</span>
                        <div style="flex:1; min-width:0;">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.3rem;">
                                <span style="font-size:0.8rem; font-weight:600; color:#cbd5e1; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:65%;">{{ $product['nama'] }}</span>
                                <div style="display:flex; gap:0.5rem; align-items:center; flex-shrink:0; margin-left:0.5rem;">
                                    <span style="font-size:0.68rem; color:#64748b;">{{ number_format($product['total_sold']) }} terjual</span>
                                    <span style="font-size:0.68rem; background:rgba(251,191,36,0.1); color:#fbbf24; padding:0.1rem 0.4rem; border-radius:1rem; border:1px solid rgba(251,191,36,0.2);">Stok: {{ number_format($product['stok_sisa']) }}</span>
                                </div>
                            </div>
                            <div style="height:5px; background:rgba(255,255,255,0.05); border-radius:3px; overflow:hidden;">
                                <div style="height:100%; width:{{ max($product['percentage'], 3) }}%; background:linear-gradient(90deg,#f59e0b,#d97706); border-radius:3px;"></div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div style="text-align:center; padding:1.5rem 0; color:#475569;">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin:0 auto 0.6rem; display:block; opacity:0.4;"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                        <div style="font-size:0.8rem; font-weight:600; color:#475569;">Semua produk bergerak normal</div>
                        <div style="font-size:0.72rem; color:#334155; margin-top:0.25rem;">Tidak ada produk dengan stok aktif yang terdeteksi slow moving</div>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- ── AI Insight Card ── --}}
        <div class="glass-card" style="padding:1.5rem; border-color:rgba(129,140,248,0.2); background:linear-gradient(135deg,rgba(129,140,248,0.04),rgba(56,189,248,0.02));">
            <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:1.25rem;">
                <div style="width:36px; height:36px; border-radius:50%; background:linear-gradient(135deg,rgba(129,140,248,0.25),rgba(56,189,248,0.1)); border:1px solid rgba(129,140,248,0.3); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#818cf8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a2 2 0 0 1 2 2c0 .74-.4 1.39-1 1.73V7h1a7 7 0 0 1 7 7h1a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v1a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-1H2a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h1a7 7 0 0 1 7-7h1V5.73c-.6-.34-1-.99-1-1.73a2 2 0 0 1 2-2z"/><circle cx="7.5" cy="14.5" r="1.5"/><circle cx="16.5" cy="14.5" r="1.5"/></svg>
                </div>
                <div>
                    <h3 style="font-size:0.95rem; font-weight:700; color:#e2e8f0; margin:0;">AI Insight & Rekomendasi</h3>
                    <p style="font-size:0.72rem; color:#64748b; margin:0;">Analisis otomatis berdasarkan data stok, penjualan, dan kedaluwarsa.</p>
                </div>
                <span style="margin-left:auto; font-size:0.68rem; background:linear-gradient(135deg,rgba(129,140,248,0.2),rgba(56,189,248,0.1)); color:#818cf8; padding:0.3rem 0.7rem; border-radius:2rem; border:1px solid rgba(129,140,248,0.25); font-weight:600; white-space:nowrap;">⚡ Auto-Generated</span>
            </div>
            <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(260px,1fr)); gap:0.85rem;">
                @foreach($aiInsights as $insight)
                <div style="
                    background:rgba({{ $insight['type'] === 'danger' ? '244,63,94' : ($insight['type'] === 'success' ? '52,211,153' : '251,191,36') }},0.05);
                    border:1px solid rgba({{ $insight['type'] === 'danger' ? '244,63,94' : ($insight['type'] === 'success' ? '52,211,153' : '251,191,36') }},0.18);
                    border-radius:0.75rem; padding:1rem 1.1rem; display:flex; gap:0.75rem; align-items:flex-start;">
                    <div style="width:28px; height:28px; border-radius:50%; flex-shrink:0; margin-top:0.1rem; display:flex; align-items:center; justify-content:center;
                        background:rgba({{ $insight['type'] === 'danger' ? '244,63,94' : ($insight['type'] === 'success' ? '52,211,153' : '251,191,36') }},0.12);">
                        @if($insight['icon'] === 'alert' || $insight['icon'] === 'expired')
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="{{ $insight['type'] === 'danger' ? '#f43f5e' : '#fbbf24' }}" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        @elseif($insight['icon'] === 'trending')
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#34d399" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
                        @elseif($insight['icon'] === 'slow')
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#fbbf24" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 18 13.5 8.5 8.5 13.5 1 6"/><polyline points="17 18 23 18 23 12"/></svg>
                        @else
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#34d399" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        @endif
                    </div>
                    <div>
                        <div style="font-size:0.8rem; font-weight:700; color:{{ $insight['type'] === 'danger' ? '#fda4af' : ($insight['type'] === 'success' ? '#6ee7b7' : '#fde68a') }}; margin-bottom:0.3rem;">{{ $insight['title'] }}</div>
                        <div style="font-size:0.77rem; color:#94a3b8; line-height:1.5;">{{ $insight['message'] }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

    </div>

    <style>
        .chart-filter-active {
            background: rgba(56,189,248,0.15) !important;
            color: #38bdf8 !important;
        }
        .top-products-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.25rem;
            margin-bottom: 1.25rem;
        }
        @media (max-width: 768px) {
            #chartFilterGroup { flex-wrap: wrap; }
            .chart-filter-btn { flex: 1; text-align: center; }
            .top-products-grid { grid-template-columns: 1fr; }
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('salesChart').getContext('2d');

            const chartDataSets = {
                today:  { labels: {!! json_encode($chartTodayLabels) !!},  data: {!! json_encode($chartTodayData) !!} },
                week:   { labels: {!! json_encode($chartWeekLabels) !!},   data: {!! json_encode($chartWeekData) !!} },
                thirty: { labels: {!! json_encode($chartThirtyLabels) !!}, data: {!! json_encode($chartThirtyData) !!} },
                month:  { labels: {!! json_encode($chartMonthLabels) !!},  data: {!! json_encode($chartMonthData) !!} },
            };

            function makeGradient(colorStart, colorEnd) {
                const g = ctx.createLinearGradient(0, 0, 0, 300);
                g.addColorStop(0, colorStart);
                g.addColorStop(1, colorEnd);
                return g;
            }

            function updateStats(data) {
                const total = data.reduce((a, b) => a + b, 0);
                const avg   = data.length ? Math.round(total / data.length) : 0;
                const peak  = data.length ? Math.max(...data) : 0;
                document.getElementById('statTotal').textContent = total.toLocaleString('id-ID');
                document.getElementById('statAvg').textContent   = avg.toLocaleString('id-ID');
                document.getElementById('statPeak').textContent  = peak.toLocaleString('id-ID');
            }

            const initData = chartDataSets.month;

            let salesChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: initData.labels,
                    datasets: [{
                        label: 'Qty Terjual',
                        data: initData.data,
                        backgroundColor: makeGradient('rgba(56,189,248,0.45)', 'rgba(56,189,248,0.03)'),
                        borderColor: '#38bdf8',
                        borderWidth: 1.5,
                        borderRadius: 6,
                        borderSkipped: false,
                        hoverBackgroundColor: 'rgba(56,189,248,0.70)',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { intersect: false, mode: 'index' },
                    animation: { duration: 500, easing: 'easeInOutQuart' },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(15,23,42,0.95)',
                            titleColor: '#f1f5f9',
                            bodyColor: '#94a3b8',
                            borderColor: 'rgba(56,189,248,0.35)',
                            borderWidth: 1,
                            cornerRadius: 10,
                            padding: 14,
                            displayColors: false,
                            callbacks: {
                                title: function(items) { return '📅 ' + items[0].label; },
                                label: function(item) {
                                    return ' ' + item.parsed.y.toLocaleString('id-ID') + ' unit terjual';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                                color: '#475569',
                                font: { family: 'Outfit', size: 11 },
                                callback: v => v.toLocaleString('id-ID')
                            },
                            grid: { color: 'rgba(255,255,255,0.03)', drawBorder: false },
                            border: { dash: [4, 4] }
                        },
                        x: {
                            ticks: { color: '#475569', font: { family: 'Outfit', size: 10 }, maxRotation: 45, minRotation: 0 },
                            grid: { display: false },
                            border: { display: false }
                        }
                    }
                }
            });

            updateStats(initData.data);

            window.switchPeriod = function(period, btn) {
                // Update active button styling
                document.querySelectorAll('.chart-filter-btn').forEach(b => {
                    b.style.background = 'transparent';
                    b.style.color = '#64748b';
                });
                btn.style.background = 'rgba(56,189,248,0.15)';
                btn.style.color = '#38bdf8';

                const dataset = chartDataSets[period];
                salesChart.data.labels = dataset.labels;
                salesChart.data.datasets[0].data = dataset.data;
                salesChart.update('active');
                updateStats(dataset.data);
            };
        });
    </script>
    @endif

    <!-- Main Analytics Grid -->
    <div class="grid-2">
        
        <!-- Restock Forecast Chart/Table -->
        <div class="glass-card">
            <div class="card-title" style="flex-wrap:wrap; gap:0.75rem;">
                <span>Smart Restock Forecast (30 Hari)</span>
                <span class="badge badge-blue">Otomatis</span>
            </div>
            <p style="margin-bottom: 1rem;">Prediksi kebutuhan order restock berdasarkan rata-rata volume pengeluaran harian dan parameter Lead Time (3 Hari).</p>

            {{-- Filter Tabs + Search --}}
            <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:1rem; flex-wrap:wrap;">
                <div id="restockFilterGroup" style="display:flex; gap:0.35rem; background:rgba(0,0,0,0.2); padding:0.3rem; border-radius:0.6rem; border:1px solid rgba(255,255,255,0.06);">
                    <button type="button" onclick="filterRestock('semua', this)" class="chart-filter-btn chart-filter-active" style="padding:0.4rem 0.85rem; border-radius:0.4rem; font-size:0.78rem; font-weight:600; border:none; cursor:pointer; transition:all 0.2s; background:rgba(56,189,248,0.15); color:#38bdf8;">Semua</button>
                    <button type="button" onclick="filterRestock('kritis', this)" class="chart-filter-btn" style="padding:0.4rem 0.85rem; border-radius:0.4rem; font-size:0.78rem; font-weight:600; border:none; cursor:pointer; transition:all 0.2s; background:transparent; color:#64748b;">🔴 Kritis</button>
                    <button type="button" onclick="filterRestock('aman', this)" class="chart-filter-btn" style="padding:0.4rem 0.85rem; border-radius:0.4rem; font-size:0.78rem; font-weight:600; border:none; cursor:pointer; transition:all 0.2s; background:transparent; color:#64748b;">🟢 Aman</button>
                </div>
                <div style="position:relative; flex:1; min-width:180px; max-width:280px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="position:absolute; left:10px; top:50%; transform:translateY(-50%); pointer-events:none;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" id="restockSearch" class="form-control" placeholder="Cari produk..." style="padding-left:32px; min-height:34px; font-size:0.82rem;">
                </div>
            </div>

            <div class="table-responsive">
                <table class="table-premium">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Stok</th>
                            <th>Demand/Hari</th>
                            <th>Min / ROP</th>
                            <th>Estimasi Habis</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="restockTableBody">
                        @foreach($restockForecasts as $forecast)
                            <tr>
                                <td>
                                    <strong>{{ $forecast['nama'] }}</strong><br>
                                    <span style="font-size:0.75rem; color:var(--text-muted);">{{ $forecast['kode'] }}</span>
                                </td>
                                <td>{{ $forecast['stok_sekarang'] }}</td>
                                <td>{{ $forecast['daily_demand'] }}</td>
                                <td>{{ $forecast['stok_min'] }} / {{ $forecast['rop'] }}</td>
                                <td>
                                    @if($forecast['est_days_remaining'] === 'N/A')
                                        <span style="color: var(--text-muted);">N/A</span>
                                    @else
                                        <strong>{{ $forecast['est_days_remaining'] }}</strong>
                                    @endif
                                </td>
                                <td>
                                    @if($forecast['status'] === 'Aman')
                                        <span class="badge badge-green">Aman</span>
                                    @elseif(str_contains($forecast['status'], 'Peringatan'))
                                        <span class="badge badge-yellow">Reorder</span>
                                    @else
                                        <span class="badge badge-red">Kritis</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <script>
        (function() {
            let currentFilter = 'semua';
            let searchTimer;

            function renderRestockRows(data) {
                const tbody = document.getElementById('restockTableBody');
                if (!data.length) {
                    tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; padding:2rem; color:var(--text-muted);">Tidak ada data yang sesuai filter.</td></tr>';
                    return;
                }
                tbody.style.opacity = '0.3';
                setTimeout(() => {
                    let html = '';
                    data.forEach(f => {
                        let badgeClass = 'badge-green', badgeText = 'Aman';
                        if (f.status === 'Kritis') { badgeClass = 'badge-red'; badgeText = 'Kritis'; }
                        else if (f.status === 'Peringatan') { badgeClass = 'badge-yellow'; badgeText = 'Reorder'; }

                        html += `<tr>
                            <td><strong>${f.nama}</strong><br><span style="font-size:0.75rem;color:var(--text-muted);">${f.kode}</span></td>
                            <td>${f.stok_sekarang}</td>
                            <td>${f.daily_demand}</td>
                            <td>${f.stok_min} / ${f.rop}</td>
                            <td>${f.est_days_remaining === 'N/A' ? '<span style="color:var(--text-muted);">N/A</span>' : '<strong>' + f.est_days_remaining + '</strong>'}</td>
                            <td><span class="badge ${badgeClass}">${badgeText}</span></td>
                        </tr>`;
                    });
                    tbody.innerHTML = html;
                    tbody.style.opacity = '1';
                }, 150);
            }

            function fetchRestock() {
                const search = document.getElementById('restockSearch').value;
                const url = `{{ route('dashboard.restock-filter') }}?filter=${currentFilter}&search=${encodeURIComponent(search)}`;

                fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(r => r.json())
                    .then(data => renderRestockRows(data))
                    .catch(err => console.error('Restock filter error:', err));
            }

            window.filterRestock = function(filter, btn) {
                currentFilter = filter;
                document.querySelectorAll('#restockFilterGroup .chart-filter-btn').forEach(b => {
                    b.style.background = 'transparent';
                    b.style.color = '#64748b';
                });
                btn.style.background = 'rgba(56,189,248,0.15)';
                btn.style.color = '#38bdf8';
                fetchRestock();
            };

            document.getElementById('restockSearch').addEventListener('input', function() {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(fetchRestock, 300);
            });
        })();
        </script>

        <!-- Expired Risk Detection -->
        <div class="glass-card">
            <div class="card-title">
                <span>Deteksi Risiko Kedaluwarsa (Expired Risk)</span>
                <span class="badge badge-red pulse-indicator">Kritis</span>
            </div>
            <p style="margin-bottom: 1rem;">Pemantauan batch produksi yang mendekati tanggal batas kedaluwarsa demi kelancaran pengeluaran barang (FEFO).</p>
            <div class="table-responsive">
                <table class="table-premium">
                    <thead>
                        <tr>
                            <th>Batch</th>
                            <th>Produk</th>
                            <th>Sisa Stok</th>
                            <th>Tgl Kedaluwarsa</th>
                            <th>Sisa Hari</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expiredBatches as $batch)
                            <tr>
                                <td><code>{{ $batch['batch_number'] }}</code></td>
                                <td>{{ $batch['nama_produk'] }} (Rak {{ $batch['rak'] }})</td>
                                <td>{{ $batch['stok_sisa'] }}</td>
                                <td>{{ $batch['expired_date'] }}</td>
                                <td>
                                    @if($batch['days_remaining'] <= 0)
                                        <span style="color:var(--accent-red); font-weight:bold;">Selesai ({{ $batch['days_remaining'] }} hari)</span>
                                    @else
                                        {{ $batch['days_remaining'] }} Hari lagi
                                    @endif
                                </td>
                                <td>
                                    @if($batch['status'] === 'Kedaluwarsa')
                                        <span class="badge badge-red">Expired</span>
                                    @elseif($batch['status'] === 'Risiko Tinggi (< 30 Hari)')
                                        <span class="badge badge-red">Tinggi</span>
                                    @else
                                        <span class="badge badge-yellow">Sedang</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 2rem;">Tidak ada produk aktif yang mendekati masa kedaluwarsa.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Supplier Performance Rating -->
        <div class="glass-card">
            <div class="card-title">
                <span>Performa Efisiensi Pengiriman Supplier</span>
                <span class="badge badge-blue">Rating</span>
            </div>
            <p style="margin-bottom: 1rem;">Penilaian objektif ketepatan kuantitas pengiriman supplier berdasarkan Purchase Order vs penerimaan aktual.</p>
            <div class="table-responsive">
                <table class="table-premium">
                    <thead>
                        <tr>
                            <th>Peringkat</th>
                            <th>Supplier</th>
                            <th>Total PO</th>
                            <th>Tingkat Pemenuhan</th>
                            <th>Skor Evaluasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($supplierRankings as $index => $supplier)
                            <tr>
                                <td><strong>#{{ $index + 1 }}</strong></td>
                                <td>
                                    <strong>{{ $supplier['nama'] }}</strong><br>
                                    <span style="font-size:0.75rem; color:var(--text-muted);">{{ $supplier['kontak'] }}</span>
                                </td>
                                <td>{{ $supplier['total_po'] }} Transaksi</td>
                                <td>
                                    <div style="display:flex; align-items:center; gap:0.5rem;">
                                        <div style="flex:1; background:rgba(255,255,255,0.05); height:8px; border-radius:4px; overflow:hidden; min-width:80px;">
                                            <div style="background:var(--accent-blue); width:{{ $supplier['persentase'] }}%; height:100%;"></div>
                                        </div>
                                        <span>{{ $supplier['persentase'] }}%</span>
                                    </div>
                                </td>
                                <td>
                                    @if($supplier['persentase'] >= 95)
                                        <span class="badge badge-green">Sangat Baik</span>
                                    @elseif($supplier['persentase'] >= 80)
                                        <span class="badge badge-blue">Baik</span>
                                    @elseif($supplier['total_po'] == 0)
                                        <span style="color: var(--text-muted);">N/A</span>
                                    @else
                                        <span class="badge badge-red">Kurang</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
