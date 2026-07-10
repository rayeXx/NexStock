<x-app-layout>
    <div class="flex-between" style="margin-bottom: 2rem;">
        <div>
            <h1>Riwayat Barang Keluar (Outbound)</h1>
            <p>Histori pengurangan stok berbasis FEFO — produk dengan tanggal kedaluwarsa terdekat dikeluarkan terlebih dahulu.</p>
        </div>
        @if(auth()->user()->role === 'admin_gudang')
        <a href="{{ route('outbound.create') }}" class="btn btn-primary">
            + Daftar Barang Keluar
        </a>
        @endif
    </div>

    {{-- Live Search & Date Filters --}}
    <div class="glass-card" style="margin-bottom: 1.5rem; padding: 1.25rem; display: flex; flex-direction: column; gap: 1rem;">
        <div style="position:relative; max-width:400px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); pointer-events:none;">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text" id="outboundSearch" class="form-control" placeholder="Cari nomor outbound, tujuan, atau nama produk..." style="padding-left:38px; min-height:40px; font-size:0.88rem;">
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

    @if(session('instructions'))
        <div class="glass-card" style="margin-bottom: 1.5rem; border-color: var(--accent-green); background: rgba(16, 185, 129, 0.08); padding: 1.5rem;">
            <h3 style="color: var(--accent-green); font-size: 1.05rem; font-weight: 700; margin-top: 0; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 11l3 3L22 4"></path>
                    <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                </svg>
                Instruksi Pengambilan Barang (Picking Slip)
            </h3>
            <div class="table-responsive">
                <table class="table-premium" style="margin-top: 0.5rem; font-size: 0.85rem;">
                    <thead>
                        <tr>
                            <th>Nama Produk</th>
                            <th>Qty Diambil</th>
                            <th>Lokasi Rak</th>
                            <th>Nomor Batch</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(session('instructions') as $inst)
                            <tr>
                                <td><strong>{{ $inst['produk'] }}</strong></td>
                                <td><strong style="color: var(--accent-green);">{{ $inst['qty'] }}</strong></td>
                                <td><span class="badge badge-blue">Rak {{ $inst['rak'] }}</span></td>
                                <td><code>{{ $inst['batch'] }}</code></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div class="glass-card">
        <div class="table-responsive">
            <table class="table-premium">
                <thead>
                    <tr>
                        <th>No. Outbound</th>
                        <th>Tujuan Pengiriman</th>
                        <th>Tgl. Keluar</th>
                        <th>Total Item</th>
                        <th>Rincian Produk</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="outboundTableBody">
                    @forelse($outbounds as $outbound)
                        <tr data-search="{{ strtolower($outbound->outbound_number . ' ' . $outbound->tujuan . ' ' . $outbound->details->pluck('product.nama_produk')->implode(' ') . ' ' . $outbound->details->pluck('produk_id')->implode(' ')) }}" data-date="{{ $outbound->tanggal_keluar->format('Y-m-d') }}">
                            <td><code>{{ $outbound->outbound_number }}</code></td>
                            <td><strong>{{ $outbound->tujuan }}</strong></td>
                            <td>{{ $outbound->tanggal_keluar->format('d M Y') }}</td>
                            <td>{{ $outbound->details->count() }} jenis produk</td>
                            <td>
                                @foreach($outbound->details as $detail)
                                    <div style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.25rem;">
                                        {{ $detail->product->nama_produk }} — <strong>{{ $detail->qty_keluar }} {{ $detail->product->uom }}</strong>
                                        (Batch: <code>{{ $detail->batch_number }}</code> | Lokasi: <span class="badge badge-blue">Rak {{ $detail->rak_id ?? ($detail->batchInbound->rak_id ?? '-') }}</span>)
                                    </div>
                                @endforeach
                            </td>
                            <td>
                                @if($outbound->status === 'Pending')
                                    <span class="badge" style="background: rgba(245, 158, 11, 0.15); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.3); padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.75rem; font-weight: 600;">Pending</span>
                                @else
                                    <span class="badge" style="background: rgba(16, 185, 129, 0.15); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3); padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.75rem; font-weight: 600;">Confirmed</span>
                                    @if($outbound->bukti_foto)
                                        <div style="margin-top: 0.5rem;">
                                            <button type="button" onclick="openLightbox('{{ asset('storage/' . $outbound->bukti_foto) }}', 'Bukti Pengambilan - {{ $outbound->outbound_number }}')" class="btn btn-sm btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.75rem; white-space: nowrap;">
                                                Lihat Bukti
                                            </button>
                                        </div>
                                    @endif
                                @endif
                            </td>
                            <td>
                                @if($outbound->status === 'Pending')
                                    @if(auth()->user()->role === 'staff_gudang')
                                        <a href="{{ route('outbound.confirm', $outbound->id) }}" class="btn btn-sm btn-success" style="padding: 0.25rem 0.5rem; font-size: 0.75rem; white-space: nowrap;">
                                            Konfirmasi Picking
                                        </a>
                                    @else
                                        <span style="color: #f59e0b; font-size: 0.8rem; font-weight: 500; white-space: nowrap;">
                                            Menunggu Konfirmasi
                                        </span>
                                    @endif
                                @else
                                    <span style="color: var(--text-muted); font-size: 0.8rem;">Selesai</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 2rem; color: var(--text-muted);">Belum ada riwayat barang keluar.</td>
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
        const search = document.getElementById('outboundSearch');
        const tbody = document.getElementById('outboundTableBody');
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

        let noResult = document.getElementById('noResultOutbound');
        if (visibleCount === 0 && rows.length > 0) {
            if (!noResult) {
                const tr = document.createElement('tr');
                tr.id = 'noResultOutbound';
                tr.innerHTML = '<td colspan="5" style="text-align:center; padding:2rem; color:var(--text-muted);">Tidak ada data yang sesuai filter.</td>';
                tbody.appendChild(tr);
            }
        } else if (noResult) {
            noResult.remove();
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        let timer;
        document.getElementById('outboundSearch').addEventListener('input', function() {
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
            const filterParam = urlParams.get('filter') || urlParams.get('period');
            if (filterParam) {
                const btn = document.querySelector(`#dateFilterGroup button[onclick*="${filterParam}"]`);
                if (btn) {
                    btn.click();
                }
            }
        }
    });

    function openLightbox(imgSrc, captionText) {
        const modal = document.getElementById('lightboxModal');
        const img = document.getElementById('lightboxImg');
        const caption = document.getElementById('lightboxCaption');
        
        modal.style.display = "block";
        img.src = imgSrc;
        caption.innerHTML = captionText;
    }

    function closeLightbox() {
        const modal = document.getElementById('lightboxModal');
        modal.style.display = "none";
    }

    // Close on ESC key press
    document.addEventListener('keydown', function(event) {
        if (event.key === "Escape") {
            closeLightbox();
        }
    });
    </script>

    <!-- Lightbox Modal -->
    <div id="lightboxModal" class="lightbox-modal" onclick="closeLightbox()">
        <span class="lightbox-close" onclick="closeLightbox()">&times;</span>
        <img class="lightbox-content" id="lightboxImg" alt="Bukti Foto Pengambilan" onclick="event.stopPropagation()">
        <div id="lightboxCaption" class="lightbox-caption"></div>
    </div>
</x-app-layout>
