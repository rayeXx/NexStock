<x-app-layout>
    <div class="flex-between" style="margin-bottom: 2rem;">
        <div>
            <h1>Riwayat Stock Opname</h1>
            <p>Rekaman sesi audit rekonsiliasi stok fisik gudang vs data sistem.</p>
        </div>
        @if(auth()->user()->role !== 'owner')
        <a href="{{ route('opname.create') }}" class="btn btn-primary">+ Mulai Sesi Opname</a>
        @endif
    </div>

    {{-- Date Filter Bar --}}
    <div class="glass-card" style="margin-bottom: 1.5rem; padding: 1.25rem; display: flex; flex-direction: column; gap: 1rem;">
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
                <tbody id="opnameTableBody">
                    @forelse($opnames as $opname)
                        @php
                            $totalSelisih = $opname->details->sum('selisih');
                        @endphp
                        <tr data-date="{{ $opname->tanggal_opname->format('Y-m-d') }}">
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
                            <td colspan="7" style="text-align: center; padding: 2rem; color: var(--text-muted);">Belum ada sesi stock opname yang dilakukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script>
        let currentFilter = 'all';

        function parseLocalDate(str) {
            if (!str) return new Date();
            const parts = str.split('-');
            return new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, parseInt(parts[2]));
        }

        function checkDateMatch(dateStr, filter) {
            if (filter === 'all') return true;

            if (filter.startsWith('specific:')) {
                const specDate = filter.substring(9);
                return dateStr === specDate;
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
            if (dateInput) dateInput.value = '';

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
            const tbody = document.getElementById('opnameTableBody');
            const rows = tbody.querySelectorAll('tr[data-date]');
            let visibleCount = 0;

            rows.forEach(row => {
                const dateMatch = checkDateMatch(row.getAttribute('data-date'), currentFilter);
                row.style.display = dateMatch ? '' : 'none';
                if (dateMatch) visibleCount++;
            });

            let noResult = document.getElementById('noResultOpname');
            if (visibleCount === 0 && rows.length > 0) {
                if (!noResult) {
                    const tr = document.createElement('tr');
                    tr.id = 'noResultOpname';
                    tr.innerHTML = `<td colspan="7" style="text-align:center; padding:2rem; color:var(--text-muted);">Tidak ada data yang sesuai filter.</td>`;
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
        });
    </script>
</x-app-layout>
