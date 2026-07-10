<x-app-layout>
    <div class="flex-between" style="margin-bottom: 2rem;">
        <div>
            <h1>Daftar Pemusnahan Barang</h1>
            <p>Daftar tugas pemusnahan barang karantina yang rusak secara fisik dari gudang.</p>
        </div>
    </div>

    {{-- Filter & Search --}}
    <div class="glass-card" style="margin-bottom: 1.5rem; padding: 1rem 1.5rem;">
        <form method="GET" action="{{ route('destruction.index') }}" style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 200px;">
                <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.5rem; text-transform: uppercase;">Filter Status</label>
                <select name="status" onchange="this.form.submit()" style="width: 100%; padding: 0.6rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 0.375rem; color: var(--text-primary); outline: none;">
                    <option value="">Semua Status</option>
                    <option value="Menunggu Konfirmasi" {{ request('status') === 'Menunggu Konfirmasi' ? 'selected' : '' }}>Menunggu Konfirmasi</option>
                    <option value="Selesai" {{ request('status') === 'Selesai' ? 'selected' : '' }}>Selesai</option>
                </select>
            </div>
            <div style="flex: 1; min-width: 200px;">
                <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.5rem; text-transform: uppercase;">Periode Penugasan</label>
                <select name="period" onchange="this.form.submit()" style="width: 100%; padding: 0.6rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 0.375rem; color: var(--text-primary); outline: none;">
                    <option value="">Semua Waktu</option>
                    <option value="today" {{ request('period') === 'today' ? 'selected' : '' }}>Hari Ini</option>
                    <option value="week" {{ request('period') === 'week' ? 'selected' : '' }}>Minggu Ini</option>
                    <option value="month" {{ request('period') === 'month' ? 'selected' : '' }}>Bulan Ini</option>
                </select>
            </div>
            <div style="flex: 1; min-width: 200px;">
                <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.5rem; text-transform: uppercase;">Tanggal Spesifik</label>
                <input type="date" name="date" class="form-control" value="{{ request('date') }}" onchange="this.form.submit()" style="width: 100%; padding: 0.5rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 0.375rem; color: var(--text-primary); outline: none; min-height: 38px;">
            </div>
        </form>
    </div>

    {{-- Table List --}}
    <div class="glass-card">
        <div class="table-responsive">
            <table class="table-premium">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Produk</th>
                        <th>Batch</th>
                        <th>Rak Karantina</th>
                        <th>Qty Dimusnahkan</th>
                        <th>Alasan Kerusakan</th>
                        <th>Catatan Instruksi</th>
                        <th>Ditugaskan Oleh</th>
                        <th>Status</th>
                        <th>Foto Bukti</th>
                        @if(auth()->user()->role === 'admin_gudang' || auth()->user()->role === 'staff_gudang')
                            <th>Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($destructions as $destruction)
                        <tr>
                            <td>#{{ $destruction->id }}</td>
                            <td><strong>{{ $destruction->product->nama_produk }}</strong></td>
                            <td><code>{{ $destruction->batch_number }}</code></td>
                            <td><span class="badge badge-blue">Rak {{ $destruction->rak_id }}</span></td>
                            <td><strong style="color: var(--accent-red);">{{ $destruction->qty_dimusnahkan }}</strong></td>
                            <td style="max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $destruction->alasan }}</td>
                            <td style="max-width: 180px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $destruction->catatan_pemusnahan }}">
                                @if($destruction->catatan_pemusnahan)
                                    {{ $destruction->catatan_pemusnahan }}
                                @else
                                    <span style="color: var(--text-muted); font-style: italic;">Tidak ada catatan</span>
                                @endif
                            </td>
                            <td>
                                @if($destruction->assigner)
                                    <div>{{ $destruction->assigner->name }}</div>
                                    <div style="font-size: 0.72rem; color: var(--text-muted);">{{ $destruction->assigned_at->format('d/m/Y H:i') }}</div>
                                @else
                                    <span style="color: var(--text-muted); font-style: italic;">Belum ditugaskan</span>
                                @endif
                            </td>
                            <td>
                                @if($destruction->status === 'Belum Ditugaskan')
                                    <span class="badge" style="background: rgba(245, 158, 11, 0.15); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.3); padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.75rem; font-weight: 600;">Belum Ditugaskan</span>
                                @elseif($destruction->status === 'Menunggu Konfirmasi')
                                    <span class="badge badge-yellow pulse-indicator">Menunggu Pemusnahan</span>
                                @else
                                    <span class="badge badge-green">Selesai (Dimusnahkan)</span>
                                @endif
                            </td>
                            <td>
                                @if($destruction->foto_pemusnahan)
                                    <button type="button" onclick="openLightbox('{{ asset('storage/' . $destruction->foto_pemusnahan) }}', 'Bukti Pemusnahan #{{ $destruction->id }}')" class="btn btn-secondary" style="padding: 5px 10px; min-height: 36px; font-size: 0.75rem;">
                                        Lihat Foto
                                    </button>
                                @else
                                    <span style="color: var(--text-muted); font-size: 0.8rem;">Belum ada</span>
                                @endif
                            </td>
                            @if(auth()->user()->role === 'admin_gudang' || auth()->user()->role === 'staff_gudang')
                                <td>
                                    @if($destruction->status === 'Belum Ditugaskan' && auth()->user()->role === 'admin_gudang')
                                        <button type="button" onclick="openAssignModal({{ $destruction->id }}, '{{ addslashes($destruction->product->nama_produk) }}', '{{ $destruction->batch_number }}')" class="btn btn-danger" style="padding: 5px 10px; min-height: 36px; font-size: 0.75rem; white-space: nowrap;">
                                            Tugaskan Pemusnahan
                                        </button>
                                    @elseif($destruction->status === 'Menunggu Konfirmasi' && auth()->user()->role === 'staff_gudang')
                                        <a href="{{ route('destruction.confirm', $destruction->id) }}" class="btn btn-danger" style="padding: 5px 10px; min-height: 36px; font-size: 0.75rem; text-decoration: none; white-space: nowrap; display: inline-block;">
                                            Konfirmasi Selesai
                                        </a>
                                    @elseif($destruction->status === 'Belum Ditugaskan')
                                        <span style="color: var(--text-muted); font-size: 0.85rem;">Menunggu Penugasan</span>
                                    @elseif($destruction->status === 'Menunggu Konfirmasi')
                                        <span style="color: var(--text-muted); font-size: 0.85rem;">Menunggu Konfirmasi</span>
                                    @else
                                        <span style="color: var(--text-muted); font-size: 0.85rem;">Selesai</span>
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ (auth()->user()->role === 'admin_gudang' || auth()->user()->role === 'staff_gudang') ? 11 : 10 }}" style="text-align: center; padding: 2rem; color: var(--text-muted);">Belum ada tugas pemusnahan barang.</td>
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

    <!-- Assign Destruction Modal -->
    <div id="destructionModalBackdrop" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.85); backdrop-filter:blur(6px); z-index:900; align-items:center; justify-content:center;" onclick="closeDestructionModal()">
        <div style="background:var(--glass-bg,#1e293b); border:1px solid rgba(255,255,255,0.1); border-radius:1rem; padding:2rem; width:100%; max-width:480px; margin:1rem;" onclick="event.stopPropagation()">
            <h3 style="font-size:1.1rem; font-weight:700; color:var(--accent-red); margin-bottom:0.5rem;">Tugaskan Pemusnahan</h3>
            <p id="destructionModalDesc" style="font-size:0.85rem; color:var(--text-muted); margin-bottom:1.5rem;"></p>

            <form id="destructionForm" method="POST" action="">
                @csrf
                <div style="margin-bottom:1.25rem;">
                    <label style="display:block; font-size:0.85rem; font-weight:600; color:var(--text-secondary); margin-bottom:0.5rem;">
                        Catatan untuk Staff (opsional)
                    </label>
                    <textarea name="catatan_pemusnahan" rows="3" placeholder="Mis: Musnahkan dengan cara dibakar, dokumentasikan proses..." style="width:100%; padding:0.75rem; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); border-radius:0.5rem; color:var(--text-primary); font-size:0.9rem; resize:vertical;"></textarea>
                </div>
                <div style="display:flex; gap:0.75rem;">
                    <button type="button" onclick="closeDestructionModal()" class="btn btn-secondary" style="flex:1;">Batal</button>
                    <button type="submit" class="btn btn-danger" style="flex:1;">Buat Tugas Pemusnahan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
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

        function openAssignModal(id, productName, batchNumber) {
            const backdrop = document.getElementById('destructionModalBackdrop');
            const form = document.getElementById('destructionForm');
            const desc = document.getElementById('destructionModalDesc');

            form.action = `/destruction/${id}/assign`;
            desc.textContent = `${productName} (Batch: ${batchNumber})`;
            backdrop.style.display = 'flex';
        }

        function closeDestructionModal() {
            document.getElementById('destructionModalBackdrop').style.display = 'none';
        }

        document.addEventListener('keydown', function(event) {
            if (event.key === "Escape") {
                closeLightbox();
                closeDestructionModal();
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            const periodSelect = document.querySelector('select[name="period"]');
            const dateInput = document.querySelector('input[name="date"]');
            if (periodSelect && dateInput) {
                periodSelect.addEventListener('change', function() {
                    if (periodSelect.value !== '') {
                        dateInput.value = '';
                    }
                });
                dateInput.addEventListener('change', function() {
                    if (dateInput.value !== '') {
                        periodSelect.value = '';
                    }
                });
            }
        });
    </script>
</x-app-layout>
