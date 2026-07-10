<x-app-layout>
    <div class="flex-between" style="margin-bottom: 2rem;">
        <div>
            <h1>Riwayat Barang Masuk (Inbound)</h1>
            <p>Seluruh histori penerimaan batch produk dari supplier berbasis validasi Purchase Order.</p>
        </div>
        @if(auth()->user()->role === 'staff_gudang')
            <a href="{{ route('inbound.create') }}" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 0.5rem; text-decoration: none;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Proses Barang Masuk
            </a>
        @endif
    </div>

    {{-- Live Search & Date Filters --}}
    <div class="glass-card" style="margin-bottom: 1.5rem; padding: 1.25rem; display: flex; flex-direction: column; gap: 1rem;">
        <div style="position:relative; max-width:400px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); pointer-events:none;">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text" id="inboundSearch" class="form-control" placeholder="Cari nama produk, SKU, atau batch..." style="padding-left:38px; min-height:40px; font-size:0.88rem;">
        </div>

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
                        <th>No. Batch</th>
                        <th>Produk</th>
                        <th>Referensi PO</th>
                        <th>Lokasi Rak</th>
                        <th>Tgl. Kedaluwarsa</th>
                        <th>Stok Awal</th>
                        <th>Sisa Stok</th>
                        <th>Diterima Tgl.</th>
                    </tr>
                </thead>
                <tbody id="inboundTableBody">
                    @forelse($inbounds as $batch)
                        <tr data-search="{{ strtolower($batch->product->nama_produk . ' ' . $batch->produk_id . ' ' . $batch->batch_number . ' ' . ($batch->product->category->nama_kategori ?? '')) }}" data-date="{{ $batch->created_at->format('Y-m-d') }}">
                            <td><code>{{ $batch->batch_number }}</code></td>
                            <td>
                                <strong>{{ $batch->product->nama_produk }}</strong><br>
                                <span style="font-size: 0.75rem; color: var(--text-muted);">{{ $batch->produk_id }}</span>
                            </td>
                            <td>
                                @if($batch->purchaseOrder)
                                    @if(auth()->user()->role === 'staff_gudang')
                                        <span>{{ $batch->purchaseOrder->po_number }}</span>
                                    @else
                                        <a href="{{ route('po.show', $batch->po_id) }}" style="color: var(--accent-blue);">{{ $batch->purchaseOrder->po_number }}</a>
                                    @endif
                                @else
                                    <span style="color: var(--text-muted);">N/A</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-blue">Rak {{ $batch->rak_id }}</span>
                            </td>
                            <td>
                                @php
                                    $daysLeft = now()->diffInDays($batch->expired_date, false);
                                @endphp
                                <span style="color: {{ $daysLeft <= 30 ? 'var(--accent-red)' : ($daysLeft <= 90 ? 'var(--accent-yellow)' : 'inherit') }}">
                                    {{ $batch->expired_date->format('d M Y') }}
                                </span>
                                @if($daysLeft <= 30 && $daysLeft > 0)
                                    <span class="badge badge-red" style="margin-left: 4px;">{{ $daysLeft }}h</span>
                                @elseif($daysLeft <= 0)
                                    <span class="badge badge-red" style="margin-left: 4px;">Expired!</span>
                                @endif
                            </td>
                            <td>{{ $batch->stok_awal_batch }}</td>
                            <td>
                                @if($batch->stok_sisa_batch > 0)
                                    <strong style="color: var(--accent-green);">{{ $batch->stok_sisa_batch }}</strong>
                                @else
                                    <span style="color: var(--text-muted);">Habis</span>
                                @endif
                            </td>
                            <td>{{ $batch->created_at->format('d M Y, H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 2rem; color: var(--text-muted);">Belum ada riwayat barang masuk.</td>
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
        return new Date(parts[0], parts[1] - 1, parts[2]);
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
        const search = document.getElementById('inboundSearch');
        const tbody = document.getElementById('inboundTableBody');
        const val = search.value.toLowerCase().trim();
        const rows = tbody.querySelectorAll('tr[data-search]');
        let visibleCount = 0;

        rows.forEach(row => {
            const searchMatch = !val || row.getAttribute('data-search').includes(val);
            const dateMatch = checkDateMatch(row.getAttribute('data-date'), currentFilter);
            const isVisible = searchMatch && dateMatch;
            
            row.style.display = isVisible ? '' : 'none';
            if (isVisible) visibleCount++;
        });

        let noResult = document.getElementById('noResultInbound');
        if (visibleCount === 0 && rows.length > 0) {
            if (!noResult) {
                const tr = document.createElement('tr');
                tr.id = 'noResultInbound';
                tr.innerHTML = '<td colspan="8" style="text-align:center; padding:2rem; color:var(--text-muted);">Tidak ada data yang sesuai filter.</td>';
                tbody.appendChild(tr);
            }
        } else if (noResult) {
            noResult.remove();
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        let timer;
        document.getElementById('inboundSearch').addEventListener('input', function() {
            clearTimeout(timer);
            timer = setTimeout(applyFilters, 200);
        });

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
        const filterParam = urlParams.get('filter');
        if (filterParam) {
            const btn = document.querySelector(`#dateFilterGroup button[onclick*="${filterParam}"]`);
            if (btn) {
                btn.click();
            }
        }
    });
    </script>
</x-app-layout>
