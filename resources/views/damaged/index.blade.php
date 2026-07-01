<x-app-layout>
    <div class="flex-between" style="margin-bottom: 2rem;">
        <div>
            <h1>Laporan Karantina Barang Rusak</h1>
            <p>Histori produk cacat yang diisolasi dari stok siap jual dan menunggu tindak lanjut Owner.</p>
        </div>
        <a href="{{ route('damaged.create') }}" class="btn btn-danger">
            + Laporkan Barang Rusak
        </a>
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
                        <th>Alasan</th>
                        <th>Dilaporkan Oleh</th>
                        <th>Status</th>
                        <th>Foto Bukti</th>
                        @if(auth()->user()->role === 'owner')
                        <th>Aksi Owner</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($reports as $report)
                        <tr>
                            <td>#{{ $report->id }}</td>
                            <td><strong>{{ $report->product->nama_produk }}</strong></td>
                            <td><code>{{ $report->batch_number }}</code></td>
                            <td><span class="badge badge-blue">Rak {{ $report->rak_id }}</span></td>
                            <td><strong style="color: var(--accent-red);">{{ $report->qty_rusak }}</strong></td>
                            <td style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $report->alasan }}</td>
                            <td>{{ $report->creator->name }}</td>
                            <td>
                                @if($report->status === 'Pending')
                                    <span class="badge badge-yellow pulse-indicator">Pending Approval</span>
                                @elseif($report->status === 'Approved')
                                    <span class="badge badge-green">Disetujui (Dibuang)</span>
                                @else
                                    <span class="badge badge-blue">Ditolak (Stok Dikembalikan)</span>
                                @endif
                            </td>
                            <td>
                                @if($report->foto_bukti)
                                    <button type="button" onclick="openLightbox('{{ asset('storage/' . $report->foto_bukti) }}', 'Foto Bukti #{{ $report->id }} - {{ $report->product->nama_produk }}')" class="btn btn-secondary" style="padding: 5px 10px; min-height: 36px; font-size: 0.75rem;">
                                        Lihat Foto
                                    </button>
                                @else
                                    <span style="color: var(--text-muted);">Tidak ada</span>
                                @endif
                            </td>
                            @if(auth()->user()->role === 'owner')
                            <td>
                                @if($report->status === 'Pending')
                                    <div style="display: flex; gap: 0.5rem;">
                                        <form action="{{ route('damaged.approve', $report->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-success" style="padding: 5px 10px; min-height: 36px; font-size: 0.75rem;">Setujui</button>
                                        </form>
                                        <form action="{{ route('damaged.reject', $report->id) }}" method="POST" onsubmit="return confirm('Tolak laporan? Stok akan dikembalikan.');">
                                            @csrf
                                            <button type="submit" class="btn btn-secondary" style="padding: 5px 10px; min-height: 36px; font-size: 0.75rem;">Tolak</button>
                                        </form>
                                    </div>
                                @else
                                    <span style="color: var(--text-muted); font-size: 0.8rem;">Sudah Diproses</span>
                                @endif
                            </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()->role === 'owner' ? '10' : '9' }}" style="text-align: center; padding: 2rem; color: var(--text-muted);">Belum ada laporan barang rusak.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Lightbox Modal -->
    <div id="lightboxModal" class="lightbox-modal" onclick="closeLightbox()">
        <span class="lightbox-close" onclick="closeLightbox()">&times;</span>
        <img class="lightbox-content" id="lightboxImg" alt="Foto Bukti Kerusakan" onclick="event.stopPropagation()">
        <div id="lightboxCaption" class="lightbox-caption"></div>
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
</x-app-layout>
