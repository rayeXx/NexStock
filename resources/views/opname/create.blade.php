<x-app-layout>
    <div style="margin-bottom: 2rem;">
        <a href="{{ route('opname.index') }}" style="color: var(--accent-blue); text-decoration: none; font-weight: 500; font-size: 0.9rem;">
            &larr; Kembali ke Riwayat Opname
        </a>
        <h1 style="margin-top: 0.5rem;">Mulai Sesi Stock Opname</h1>
        <p>Masukkan jumlah fisik yang Anda hitung langsung di lapangan. Jumlah stok sistem <strong>disembunyikan</strong> demi objektivitas audit.</p>
    </div>

    @if(count($batches) === 0)
        <div class="glass-card" style="text-align: center; padding: 3rem; max-width: 100%;">
            <p style="font-size: 1.1rem; margin-bottom: 0;">Tidak ada batch aktif untuk diaudit saat ini.</p>
        </div>
    @else
        <div class="instruction-box" style="max-width: 100%;">
            <h4>Panduan Stock Opname</h4>
            <ol>
                <li>Hitung stok fisik setiap batch di rak gudang secara aktual.</li>
                <li>Masukkan angka hasil hitungan di kolom <strong>"Qty Fisik"</strong> tanpa melihat angka sistem.</li>
            </ol>
        </div>

        <div class="glass-card" style="max-width: 100%;">
            <form action="{{ route('opname.store') }}" method="POST">
                @csrf
                <div class="table-responsive">
                    <table class="table-premium">
                        <thead>
                            <tr>
                                <th>Batch No.</th>
                                <th>Nama Produk</th>
                                <th>Lokasi Rak</th>
                                <th>Tgl. Kedaluwarsa</th>
                                <th style="color: var(--text-muted); font-style: italic;">Stok Sistem (Tersembunyi)</th>
                                <th style="color: var(--accent-blue);">Qty Fisik (Input Anda) *</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($batches as $index => $batch)
                                <input type="hidden" name="items[{{ $index }}][batch_number]" value="{{ $batch->batch_number }}">
                                <tr>
                                    <td><code>{{ $batch->batch_number }}</code></td>
                                    <td><strong>{{ $batch->product->nama_produk }}</strong></td>
                                    <td><span class="badge badge-blue">Rak {{ $batch->rak_id }}</span></td>
                                    <td>{{ $batch->expired_date->format('d M Y') }}</td>
                                    <td>
                                        {{-- BLIND AUDIT: System stock hidden until hover --}}
                                        <span class="opname-hidden-value" title="Hover untuk melihat">{{ $batch->stok_sisa_batch }}</span>
                                    </td>
                                    <td>
                                        <input type="number" name="items[{{ $index }}][qty_fisik]" class="form-control" min="0" placeholder="Hitung fisik..." required style="min-width: 130px;">
                                    </td>
                                    <td>
                                        <input type="text" name="items[{{ $index }}][catatan]" class="form-control" placeholder="Opsional..." style="min-width: 150px;">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div style="margin-top: 1.5rem; display: flex; gap: 1rem;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;" onclick="return confirm('Ajukan hasil opname ini untuk disetujui Owner / Admin?')">
                        ✓ Ajukan Hasil Opname untuk Persetujuan
                    </button>
                    <a href="{{ route('opname.index') }}" class="btn btn-secondary" style="flex: 1;">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    @endif
</x-app-layout>
