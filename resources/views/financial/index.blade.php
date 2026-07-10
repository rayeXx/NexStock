<x-app-layout>
    <div style="margin-bottom: 2rem;">
        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.4rem;">
            <h1 style="margin: 0;">Analisis Keuangan</h1>
            <span class="badge badge-blue">Owner Only</span>
        </div>
        <p style="margin: 0; color: var(--text-muted);">Ringkasan nilai aset, arus kas, margin keuntungan, dan saran strategis dari AI berdasarkan data gudang.</p>
    </div>

    {{-- Period Filter --}}
    <div class="glass-card" style="margin-bottom: 1.5rem; padding: 1rem 1.25rem;">
        <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
            <span style="font-size: 0.85rem; color: var(--text-muted); font-weight: 500;">Periode:</span>
            <div style="display: flex; gap: 0.35rem; background: rgba(0,0,0,0.2); padding: 0.3rem; border-radius: 0.6rem; border: 1px solid rgba(255,255,255,0.06); flex-wrap: wrap;">
                @foreach(['all' => 'Semua Waktu', 'today' => 'Hari Ini', 'week' => 'Minggu Ini', 'month' => 'Bulan Ini', 'year' => 'Tahun Ini'] as $key => $label)
                    <a href="{{ route('financial.index', ['period' => $key]) }}"
                       style="padding: 0.4rem 0.85rem; border-radius: 0.4rem; font-size: 0.78rem; font-weight: 600; text-decoration: none; transition: all 0.2s;
                              {{ $period === $key ? 'background: rgba(56,189,248,0.15); color: #38bdf8;' : 'background: transparent; color: #64748b;' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
            <span style="font-size: 0.78rem; color: #64748b; margin-left: auto;">
                📅 Menampilkan data: <strong style="color: #e2e8f0;">{{ $periodLabel }}</strong>
            </span>
        </div>
    </div>

    {{-- 4 KPI Cards --}}
    <div class="grid-4" style="margin-bottom: 1.5rem;">
        <div class="glass-card metric-box" style="border: 1px solid rgba(56,189,248,0.15);">
            <span class="metric-label">💼 Nilai Aset Inventaris</span>
            <span class="metric-value" style="color: #38bdf8; font-size: 1.4rem;">Rp {{ number_format($nilaiAset, 0, ',', '.') }}</span>
            <p>Total nilai stok aktif di seluruh gudang saat ini.</p>
        </div>
        <div class="glass-card metric-box" style="border: 1px solid rgba(16,185,129,0.15);">
            <span class="metric-label">📈 Total Pemasukan</span>
            <span class="metric-value" style="color: #10b981; font-size: 1.4rem;">Rp {{ number_format($totalPemasukan, 0, ',', '.') }}</span>
            <p>Omzet dari semua penjualan yang sudah terkonfirmasi.</p>
        </div>
        <div class="glass-card metric-box" style="border: 1px solid rgba(239,68,68,0.15);">
            <span class="metric-label">📉 Total Kerugian</span>
            <span class="metric-value" style="color: #ef4444; font-size: 1.4rem;">Rp {{ number_format($totalKerugian, 0, ',', '.') }}</span>
            <p>Estimasi kerugian dari barang rusak & kadaluarsa.</p>
        </div>
        <div class="glass-card metric-box" style="border: 1px solid rgba(168,85,247,0.15);">
            <span class="metric-label">📊 Gross Margin</span>
            <span class="metric-value" style="color: {{ $grossMargin >= 0 ? '#a855f7' : '#ef4444' }}; font-size: 1.4rem;">
                Rp {{ number_format($grossMargin, 0, ',', '.') }}
            </span>
            <p>
                Selisih pemasukan dan HPP barang keluar.
                <span style="font-weight: 700; color: {{ $marginPct >= 20 ? '#10b981' : ($marginPct >= 0 ? '#f59e0b' : '#ef4444') }};">
                    ({{ $marginPct }}%)
                </span>
            </p>
        </div>
    </div>

    {{-- Trend Chart & AI Suggestions --}}
    <div class="grid-2" style="margin-bottom: 1.5rem;">

        {{-- Trend Chart --}}
        <div class="glass-card">
            <div class="card-title">
                <span>📊 Tren Pemasukan vs Kerugian (7 Hari Terakhir)</span>
            </div>
            <div style="height: 260px; position: relative;">
                <canvas id="financialTrendChart"></canvas>
            </div>
        </div>

        {{-- AI Suggestions Panel --}}
        <div class="glass-card" style="border: 1px solid rgba(168,85,247,0.2); background: linear-gradient(135deg, rgba(168,85,247,0.04) 0%, rgba(56,189,248,0.04) 100%);">
            <div class="card-title" style="margin-bottom: 1rem;">
                <span style="display:flex; align-items:center; gap:0.5rem;">
                    <span style="font-size: 1.2rem;">🤖</span>
                    <span>Saran Strategis AI</span>
                    <span class="badge" style="background: rgba(168,85,247,0.15); color: #a855f7; border: 1px solid rgba(168,85,247,0.3); font-size: 0.65rem; padding: 0.2rem 0.5rem;">OpenRouter AI</span>
                </span>
            </div>
            <div id="aiSuggestionsContent" style="font-size: 0.88rem; line-height: 1.75; color: #cbd5e1;">
                {!! $aiSuggestions !!}
            </div>
            <div style="margin-top: 1rem; padding-top: 0.75rem; border-top: 1px solid rgba(255,255,255,0.06);">
                <button onclick="refreshAiSuggestions()" id="refreshAiBtn"
                    style="display: flex; align-items: center; gap: 0.4rem; background: rgba(168,85,247,0.1); border: 1px solid rgba(168,85,247,0.25); color: #a855f7; border-radius: 0.5rem; padding: 0.45rem 0.9rem; font-size: 0.78rem; font-weight: 600; cursor: pointer; transition: all 0.2s;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline>
                        <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
                    </svg>
                    Perbarui Saran AI
                </button>
            </div>
        </div>
    </div>

    {{-- Top Products Tables --}}
    <div class="grid-2" style="margin-bottom: 1.5rem;">

        {{-- Top Sales --}}
        <div class="glass-card">
            <div class="card-title" style="margin-bottom: 1rem;">
                <span>🏆 Top 5 Produk Penjualan Terbaik</span>
            </div>
            @if($topProducts->isEmpty())
                <p style="color: var(--text-muted); text-align: center; padding: 1.5rem 0;">Tidak ada data penjualan pada periode ini.</p>
            @else
                <div class="table-responsive">
                    <table class="table-premium">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Produk</th>
                                <th>Qty Keluar</th>
                                <th>Total Pemasukan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topProducts as $i => $item)
                                <tr>
                                    <td><span style="font-weight: 700; color: {{ $i === 0 ? '#f59e0b' : ($i === 1 ? '#94a3b8' : ($i === 2 ? '#cd7f32' : 'var(--text-muted)')) }};">{{ $i + 1 }}</span></td>
                                    <td><strong>{{ $item->product?->nama_produk ?? '—' }}</strong></td>
                                    <td><span class="badge badge-blue">{{ number_format($item->total_qty) }} unit</span></td>
                                    <td><strong style="color: #10b981;">Rp {{ number_format($item->total_revenue, 0, ',', '.') }}</strong></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Top Loss Products --}}
        <div class="glass-card">
            <div class="card-title" style="margin-bottom: 1rem;">
                <span>⚠️ Top 5 Produk Kerugian Terbesar</span>
            </div>
            @if($topLossProducts->isEmpty())
                <p style="color: var(--text-muted); text-align: center; padding: 1.5rem 0;">Tidak ada data kerugian pada periode ini.</p>
            @else
                <div class="table-responsive">
                    <table class="table-premium">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Produk</th>
                                <th>Qty Rusak</th>
                                <th>Total Kerugian</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topLossProducts as $i => $item)
                                <tr>
                                    <td><span style="font-weight: 700; color: var(--text-muted);">{{ $i + 1 }}</span></td>
                                    <td><strong>{{ $item['product']?->nama_produk ?? '—' }}</strong></td>
                                    <td><span class="badge badge-red">{{ number_format($item['total_qty']) }} unit</span></td>
                                    <td><strong style="color: #ef4444;">Rp {{ number_format($item['total_loss'], 0, ',', '.') }}</strong></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('financialTrendChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($trendLabels) !!},
                datasets: [
                    {
                        label: 'Pemasukan',
                        data: {!! json_encode($trendPemasukan) !!},
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16,185,129,0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.3,
                        pointBackgroundColor: '#10b981',
                        pointHoverRadius: 6,
                    },
                    {
                        label: 'Kerugian',
                        data: {!! json_encode($trendKerugian) !!},
                        borderColor: '#ef4444',
                        backgroundColor: 'rgba(239,68,68,0.08)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.3,
                        pointBackgroundColor: '#ef4444',
                        pointHoverRadius: 6,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: { duration: 600, easing: 'easeInOutQuart' },
                plugins: {
                    legend: {
                        display: true,
                        labels: { color: '#94a3b8', font: { family: 'Outfit', size: 12 } }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(15,23,42,0.95)',
                        titleColor: '#f1f5f9',
                        bodyColor: '#94a3b8',
                        borderColor: 'rgba(255,255,255,0.08)',
                        borderWidth: 1,
                        cornerRadius: 10,
                        padding: 12,
                        callbacks: {
                            label: function(item) {
                                return ' Rp ' + item.parsed.y.toLocaleString('id-ID');
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: '#475569',
                            font: { family: 'Outfit', size: 11 },
                            callback: v => 'Rp ' + (v >= 1000000 ? (v/1000000).toFixed(1)+'jt' : v.toLocaleString('id-ID'))
                        },
                        grid: { color: 'rgba(255,255,255,0.03)' },
                        border: { dash: [4, 4] }
                    },
                    x: {
                        ticks: { color: '#475569', font: { family: 'Outfit', size: 10 } },
                        grid: { display: false },
                        border: { display: false }
                    }
                }
            }
        });
    });

    function refreshAiSuggestions() {
        const btn = document.getElementById('refreshAiBtn');
        const content = document.getElementById('aiSuggestionsContent');
        btn.disabled = true;
        btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="animation: spin 1s linear infinite;"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg> Memuat saran AI...';
        content.style.opacity = '0.4';

        const period = new URLSearchParams(window.location.search).get('period') || 'all';
        fetch(`/financial-analysis/ai-refresh?period=${period}`)
            .then(r => r.json())
            .then(data => {
                content.innerHTML = data.html;
                content.style.opacity = '1';
                btn.disabled = false;
                btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg> Perbarui Saran AI';
            })
            .catch(() => {
                content.innerHTML = '<p style="color:#f87171;">Gagal memuat saran AI.</p>';
                content.style.opacity = '1';
                btn.disabled = false;
                btn.innerHTML = 'Perbarui Saran AI';
            });
    }
    </script>

    <style>
    @keyframes spin { 0%{transform:rotate(0deg)} 100%{transform:rotate(360deg)} }
    #aiSuggestionsContent ul { padding-left: 0.5rem; margin: 0; }
    #aiSuggestionsContent li { list-style: none; padding: 0.4rem 0; border-bottom: 1px solid rgba(255,255,255,0.04); }
    #aiSuggestionsContent li:last-child { border-bottom: none; }
    </style>
</x-app-layout>
