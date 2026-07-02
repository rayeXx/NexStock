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
            <p>Laporan barang rusak berstatus pending dan sedang menunggu review Owner.</p>
        </div>
    </div>

    <!-- Sales Chart (Owner Only) -->
    @if($userRole === 'owner')
    <div class="glass-card" style="margin-bottom: 1.5rem;">
        <div class="card-title">
            <span>Grafik Penjualan (Barang Keluar)</span>
            <select id="chartPeriodFilter" class="form-control" style="width: auto; min-width: 160px; padding: 8px 36px 8px 12px; font-size: 0.85rem; min-height: 36px;">
                <option value="month" selected>Bulan Ini</option>
                <option value="week">Minggu Ini</option>
            </select>
        </div>
        <div style="height: 320px; width: 100%;">
            <canvas id="salesChart"></canvas>
        </div>
    </div>
    @endif

    <!-- Main Analytics Grid -->
    <div class="grid-2">
        
        <!-- Restock Forecast Chart/Table -->
        <div class="glass-card">
            <div class="card-title">
                <span>Smart Restock Forecast (30 Hari)</span>
                <span class="badge badge-blue">Otomatis</span>
            </div>
            <p style="margin-bottom: 1rem;">Prediksi kebutuhan order restock berdasarkan rata-rata volume pengeluaran harian dan parameter Lead Time (3 Hari).</p>
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
                    <tbody>
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
                                    @elseif($forecast['status'] === 'Peringatan (Mendekati Reorder Point)')
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
    @if($userRole === 'owner')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('salesChart').getContext('2d');

            const chartDataSets = {
                month: {
                    labels: {!! json_encode($chartMonthLabels) !!},
                    data: {!! json_encode($chartMonthData) !!}
                },
                week: {
                    labels: {!! json_encode($chartWeekLabels) !!},
                    data: {!! json_encode($chartWeekData) !!}
                }
            };

            const gradient = ctx.createLinearGradient(0, 0, 0, 320);
            gradient.addColorStop(0, 'rgba(56, 189, 248, 0.35)');
            gradient.addColorStop(1, 'rgba(56, 189, 248, 0.02)');

            let salesChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: chartDataSets.month.labels,
                    datasets: [{
                        label: 'Total Qty Terjual',
                        data: chartDataSets.month.data,
                        backgroundColor: gradient,
                        borderColor: '#38bdf8',
                        borderWidth: 1.5,
                        borderRadius: 6,
                        borderSkipped: false,
                        hoverBackgroundColor: 'rgba(56, 189, 248, 0.6)',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },
                    plugins: {
                        legend: {
                            labels: {
                                color: '#9ca3af',
                                font: { family: 'Outfit', size: 13 }
                            }
                        },
                        tooltip: {
                            backgroundColor: '#1e293b',
                            titleColor: '#f8fafc',
                            bodyColor: '#94a3b8',
                            borderColor: 'rgba(56, 189, 248, 0.3)',
                            borderWidth: 1,
                            cornerRadius: 8,
                            padding: 12
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0, color: '#64748b', font: { family: 'Outfit' } },
                            grid: { color: 'rgba(255,255,255,0.04)', drawBorder: false }
                        },
                        x: {
                            ticks: { color: '#64748b', font: { family: 'Outfit', size: 11 }, maxRotation: 45, minRotation: 0 },
                            grid: { display: false }
                        }
                    }
                }
            });

            // Period filter switching
            document.getElementById('chartPeriodFilter').addEventListener('change', function() {
                const period = this.value;
                const dataset = chartDataSets[period];

                salesChart.data.labels = dataset.labels;
                salesChart.data.datasets[0].data = dataset.data;
                salesChart.update('active');
            });
        });
    </script>
    @endif
</x-app-layout>
