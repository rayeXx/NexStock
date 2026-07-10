<x-app-layout>
    <div class="flex-between" style="margin-bottom: 2rem;">
        <div>
            <h1>Laporan Karantina Barang Rusak</h1>
            <p>Histori produk cacat yang diisolasi dari stok siap jual dan menunggu tindak lanjut Admin.</p>
        </div>
        @if(auth()->user()->role === 'staff_gudang')
        <a href="{{ route('damaged.create') }}" class="btn btn-danger">
            + Laporkan Barang Rusak
        </a>
        @endif
    </div>
    {{-- Date Filter Bar --}}
    <div class="glass-card" style="margin-bottom: 1.5rem; padding: 1.25rem; display: flex; flex-direction: column; gap: 1rem;">
        <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; flex-wrap: wrap;">
            <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                <span style="font-size: 0.85rem; color: var(--text-muted); font-weight: 500;">Filter Tanggal:</span>
                <div id="dateFilterGroup" style="display: flex; gap: 0.35rem; background: rgba(0,0,0,0.2); padding: 0.3rem; border-radius: 0.6rem; border: 1px solid rgba(255,255,255,0.06); flex-wrap: wrap;">
                    <button type="button" onclick="filterByDate('all', this)" class="chart-filter-btn chart-filter-active" style="padding: 0.4rem 0.85rem; border-radius: 0.4rem; font-size: 0.78rem; font-weight: 600; border: none; cursor: pointer; transition: all 0.2s; background: rgba(56,189,248,0.15); color: #38bdf8;">Semua</button>
                    <button type="button" onclick="filterByDate('today', this)" class="chart-filter-btn" style="padding: 0.4rem 0.85rem; border-radius: 0.4rem; font-size: 0.78rem; font-weight: 600; border: none; cursor: pointer; transition: all 0.2s; background: transparent; color: #64748b;">Hari Ini</button>
                    <button type="button" onclick="filterByDate('week', this)" class="chart-filter-btn" style="padding: 0.4rem 0.85rem; border-radius: 0.4rem; font-size: 0.78rem; font-weight: 600; border: none; cursor: pointer; transition: all 0.2s; background: transparent; color: #64748b;">Minggu Ini</button>
                    <button type="button" onclick="filterByDate('month', this)" class="chart-filter-btn" style="padding: 0.4rem 0.85rem; border-radius: 0.4rem; font-size: 0.78rem; font-weight: 600; border: none; cursor: pointer; transition: all 0.2s; background: transparent; color: #64748b;">Bulan Ini</button>
                    <button type="button" onclick="filterByDate('year', this)" class="chart-filter-btn" style="padding: 0.4rem 0.85rem; border-radius: 0.4rem; font-size: 0.78rem; font-weight: 600; border: none; cursor: pointer; transition: all 0.2s; background: transparent; color: #64748b;">Tahun Ini</button>
                </div>
                
                <input type="date" id="dateFilter" class="form-control" style="max-width: 160px; min-height: 38px; font-size: 0.85rem; padding: 0.3rem 0.75rem; border-radius: 0.5rem; border: 1px solid rgba(255,255,255,0.1); background: rgba(255,255,255,0.05); color: var(--text-primary);">
            </div>
            
            <div style="background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.2); border-radius: 0.5rem; padding: 0.45rem 1rem; display: flex; align-items: center; gap: 0.5rem;">
                <span style="font-size: 0.82rem; color: #94a3b8; font-weight: 500;">Total Kerugian Terfilter:</span>
                <span id="filteredLossText" style="font-size: 0.95rem; font-weight: 800; color: #ef4444;">Rp 0</span>
            </div>
        </div>
    </div>


    <div class="glass-card">
        <div class="table-responsive">
            <table class="table-premium">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Produk</th>
                        <th>Batch</th>
                        <th>Rak Asal</th>
                        <th>Qty Rusak</th>
                        <th>Estimasi Kerugian</th>
                        <th>Alasan</th>
                        <th>Dilaporkan Oleh</th>
                        <th>Status</th>
                        <th>Foto Bukti</th>
                        @if(auth()->user()->role === 'admin_gudang' || auth()->user()->role === 'staff_gudang')
                            <th>Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody id="damagedTableBody">
                    @forelse($reports as $report)
                        @php
                            $rowLoss = $report->qty_rusak * (int)($report->product->harga_beli ?? 0);
                        @endphp
                        <tr data-date="{{ $report->created_at->format('Y-m-d') }}" data-qty="{{ $report->qty_rusak }}" data-loss="{{ $rowLoss }}" data-product="{{ addslashes($report->product->nama_produk) }}" data-status="{{ $report->status }}">
                            <td>#{{ $report->id }}</td>
                            <td><strong>{{ $report->product->nama_produk }}</strong></td>
                            <td><code>{{ $report->batch_number }}</code></td>
                            <td><span class="badge badge-blue">Rak {{ $report->rak_id }}</span></td>
                            <td><strong style="color: var(--accent-red);">{{ $report->qty_rusak }}</strong></td>
                            <td>
                                <span style="font-family: monospace; color: var(--accent-red); font-weight: 600;">
                                    @if($report->status === 'Rejected')
                                        <del style="opacity: 0.5; color: var(--text-muted);">Rp {{ number_format($rowLoss, 0, ',', '.') }}</del>
                                    @else
                                        Rp {{ number_format($rowLoss, 0, ',', '.') }}
                                    @endif
                                </span>
                            </td>
                            <td style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $report->alasan }}</td>
                            <td>{{ $report->creator->name }}</td>
                            <td>
                                @if($report->status === 'Pending')
                                    <span class="badge badge-yellow pulse-indicator">Pending Approval</span>
                                @elseif($report->status === 'Approved')
                                    <span class="badge badge-green">Terkarantina (Disetujui)</span>
                                @elseif($report->status === 'Destruction Assigned')
                                    <span class="badge" style="background: rgba(148, 163, 184, 0.15); color: #94a3b8; border: 1px solid rgba(148, 163, 184, 0.3); padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.75rem; font-weight: 600;">Ditugaskan Musnah</span>
                                @elseif($report->status === 'Expired Pending Check')
                                    <span class="badge" style="background: rgba(249, 115, 22, 0.15); color: #f97316; border: 1px solid rgba(249, 115, 22, 0.3); padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.75rem; font-weight: 600;">Menunggu Pemeriksaan Staff</span>
                                @else
                                    <span class="badge badge-red">Ditolak (Stok Dikembalikan)</span>
                                @endif
                            </td>
                            <td>
                                @if($report->foto_bukti)
                                    <button type="button" onclick="openLightbox('{{ asset('storage/' . $report->foto_bukti) }}', 'Foto Kerusakan #{{ $report->id }}')" class="btn btn-secondary" style="padding: 5px 10px; min-height: 36px; font-size: 0.75rem;">
                                        Lihat Foto
                                    </button>
                                @else
                                    <span style="color: var(--text-muted);">Tidak ada</span>
                                @endif
                            </td>
                            @if(auth()->user()->role === 'admin_gudang')
                                <td>
                                    @if($report->status === 'Pending')
                                        <div style="display: flex; gap: 0.5rem;">
                                            <form action="{{ route('damaged.approve', $report->id) }}" method="POST" onsubmit="return confirm('Setujui laporan barang rusak ini?');" style="margin: 0;">
                                                @csrf
                                                <button type="submit" class="btn btn-success" style="padding: 5px 10px; min-height: 36px; font-size: 0.75rem;">
                                                    Setujui
                                                </button>
                                            </form>
                                            <form action="{{ route('damaged.reject', $report->id) }}" method="POST" onsubmit="return confirm('Tolak laporan barang rusak ini dan kembalikan stok?');" style="margin: 0;">
                                                @csrf
                                                <button type="submit" class="btn btn-danger" style="padding: 5px 10px; min-height: 36px; font-size: 0.75rem;">
                                                    Tolak
                                                </button>
                                            </form>
                                        </div>
                                    @elseif($report->status === 'Expired Pending Check')
                                        <span style="color: var(--text-muted); font-size: 0.85rem;">Menunggu Pemeriksaan Staff</span>
                                    @else
                                        <span style="color: var(--text-muted); font-size: 0.85rem;">Selesai</span>
                                    @endif
                                </td>
                            @elseif(auth()->user()->role === 'staff_gudang')
                                <td>
                                    @if($report->status === 'Expired Pending Check')
                                        <a href="{{ route('damaged.confirm-expired', $report->id) }}" class="btn btn-danger" style="padding: 5px 10px; min-height: 36px; font-size: 0.75rem; text-decoration: none; display: inline-block; white-space: nowrap;">
                                            Periksa Barang
                                        </a>
                                    @else
                                        <span style="color: var(--text-muted); font-size: 0.85rem;">-</span>
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" style="text-align: center; padding: 2rem; color: var(--text-muted);">Belum ada laporan barang rusak.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Lightbox Modal -->
    <div id="lightboxModal" class="lightbox-modal" onclick="closeLightbox()">
        <span class="lightbox-close" onclick="closeLightbox()">&times;</span>
        <img class="lightbox-content" id="lightboxImg" alt="Foto Bukti" onclick="event.stopPropagation()">
        <div id="lightboxCaption" class="lightbox-caption"></div>
    </div>

    <script>
        let currentFilter = 'all';

        function parseLocalDate(str) {
            if (!str) return new Date();
            const parts = str.split('-');
            return new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, parseInt(parts[2]));
        }

        function openLightbox(imgSrc, captionText) {
            const modal = document.getElementById('lightboxModal');
            const img = document.getElementById('lightboxImg');
            const caption = document.getElementById('lightboxCaption');
            modal.style.display = "block";
            img.src = imgSrc;
            caption.innerHTML = captionText;
        }

        function closeLightbox() {
            document.getElementById('lightboxModal').style.display = "none";
        }

        document.addEventListener('keydown', function(event) {
            if (event.key === "Escape") {
                closeLightbox();
            }
        });

        function checkDateMatch(dateStr, filter) {
            if (filter === 'all') return true;

            if (filter.startsWith('specific:')) {
                const specDate = filter.substring(9);
                return dateStr.startsWith(specDate);
            }

            const date = parseLocalDate(dateStr);
            const today = new Date();
            today.setHours(0,0,0,0);
            
            const rowTime = date.getTime();
            
            if (filter === 'today') {
                return date.toDateString() === today.toDateString();
            }
            
            if (filter === 'week') {
                const dayOfWeek = today.getDay();
                const startOfWeek = new Date(today);
                startOfWeek.setDate(today.getDate() - dayOfWeek);
                startOfWeek.setHours(0,0,0,0);
                
                const endOfWeek = new Date(startOfWeek);
                endOfWeek.setDate(startOfWeek.getDate() + 6);
                endOfWeek.setHours(23,59,59,999);
                
                return rowTime >= startOfWeek.getTime() && rowTime <= endOfWeek.getTime();
            }
            
            if (filter === 'month') {
                return date.getMonth() === today.getMonth() && date.getFullYear() === today.getFullYear();
            }
            
            if (filter === 'year') {
                return date.getFullYear() === today.getFullYear();
            }
            
            return false;
        }

        window.filterByDate = function(filter, btn) {
            const dateInput = document.getElementById('dateFilter');
            if (dateInput) {
                dateInput.value = '';
            }

            currentFilter = filter;
            document.querySelectorAll('#dateFilterGroup .chart-filter-btn').forEach(b => {
                b.style.background = 'transparent';
                b.style.color = '#64748b';
                b.classList.remove('chart-filter-active');
            });
            btn.style.background = 'rgba(56,189,248,0.15)';
            btn.style.color = '#38bdf8';
            btn.classList.add('chart-filter-active');
            applyFilters();
        };

        function applyFilters() {
            const tbody = document.getElementById('damagedTableBody');
            const rows = tbody.querySelectorAll('tr[data-date]');
            let visibleCount = 0;
            let totalLoss = 0;

            rows.forEach(row => {
                const dateMatch = checkDateMatch(row.getAttribute('data-date'), currentFilter);
                row.style.display = dateMatch ? '' : 'none';
                if (dateMatch) {
                    visibleCount++;
                    // Only sum non-Rejected cases
                    if (row.getAttribute('data-status') !== 'Rejected') {
                        totalLoss += parseInt(row.getAttribute('data-loss')) || 0;
                    }
                }
            });

            const lossTextEl = document.getElementById('filteredLossText');
            if (lossTextEl) {
                lossTextEl.textContent = 'Rp ' + totalLoss.toLocaleString('id-ID');
            }

            let noResult = document.getElementById('noResultDamaged');
            if (visibleCount === 0 && rows.length > 0) {
                if (!noResult) {
                    const tr = document.createElement('tr');
                    tr.id = 'noResultDamaged';
                    // Count columns
                    const colCount = document.querySelectorAll('thead th').length;
                    tr.innerHTML = `<td colspan="${colCount}" style="text-align:center; padding:2rem; color:var(--text-muted);">Tidak ada data yang sesuai filter.</td>`;
                    tbody.appendChild(tr);
                }
            } else if (noResult) {
                noResult.remove();
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const dateInput = document.getElementById('dateFilter');
            if (dateInput) {
                dateInput.addEventListener('change', function() {
                    if (dateInput.value) {
                        // Remove highlight from buttons
                        document.querySelectorAll('#dateFilterGroup .chart-filter-btn').forEach(b => {
                            b.style.background = 'transparent';
                            b.style.color = '#64748b';
                            b.classList.remove('chart-filter-active');
                        });
                        currentFilter = 'specific:' + dateInput.value;
                        applyFilters();
                    }
                });
            }

            // Auto-filter based on URL query parameter
            const urlParams = new URLSearchParams(window.location.search);
            const dateParam = urlParams.get('date');
            if (dateParam) {
                document.querySelectorAll('#dateFilterGroup .chart-filter-btn').forEach(b => {
                    b.style.background = 'transparent';
                    b.style.color = '#64748b';
                    b.classList.remove('chart-filter-active');
                });
                if (dateInput) {
                    if (dateParam.length === 10) {
                        dateInput.value = dateParam;
                    } else {
                        dateInput.value = '';
                    }
                }
                currentFilter = 'specific:' + dateParam;
                applyFilters();
            } else {
                const filterParam = urlParams.get('period') || urlParams.get('filter');
                if (filterParam) {
                    const btn = document.querySelector(`#dateFilterGroup button[onclick*="${filterParam}"]`);
                    if (btn) {
                        btn.click();
                    } else {
                        applyFilters();
                    }
                } else {
                    applyFilters();
                }
            }
        });
    </script>
</x-app-layout>
