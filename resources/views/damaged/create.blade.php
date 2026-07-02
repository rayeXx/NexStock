<x-app-layout>
    <div style="margin-bottom: 2rem;">
        <a href="{{ route('damaged.index') }}" style="color: var(--accent-blue); text-decoration: none; font-weight: 500; font-size: 0.9rem;">
            &larr; Kembali ke Daftar Karantina
        </a>
        <h1 style="margin-top: 0.5rem;">Laporkan Barang Rusak / Cacat</h1>
        <p>Sistem akan <strong>langsung memotong</strong> stok dari area siap jual dan memindahkan ke status Karantina saat laporan ini dikirim.</p>
    </div>

    <div class="glass-card" style="max-width: 600px;">
        <form action="{{ route('damaged.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="form-group">
                <label class="form-label" for="batch_number">Pilih No. Batch yang Rusak *</label>
                <select name="batch_number" id="batch_number" class="form-control select2" required>
                    <option value="" disabled selected>-- Pilih Batch Aktif dari Rak --</option>
                    @foreach($activeBatches as $batch)
                        <option value="{{ $batch->batch_number }}">
                            {{ $batch->product->nama_produk }} | Batch: {{ $batch->batch_number }} | Rak: {{ $batch->rak_id }} | Sisa: {{ $batch->stok_sisa_batch }} | Exp: {{ $batch->expired_date->format('d M Y') }}
                        </option>
                    @endforeach
                    <!-- DUMMY DATA UNTUK TESTING -->
                    <option value="BATCH-DUMMY-001">
                        Dummy Produk A | Batch: BATCH-DUMMY-001 | Rak: 1 | Sisa: 100 | Exp: 31 Dec 2026
                    </option>
                    <option value="BATCH-DUMMY-002">
                        Dummy Produk B | Batch: BATCH-DUMMY-002 | Rak: 2 | Sisa: 50 | Exp: 31 Dec 2026
                    </option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="qty_rusak">Jumlah Unit yang Rusak *</label>
                <input type="number" name="qty_rusak" id="qty_rusak" class="form-control" min="1" placeholder="Contoh: 5" value="{{ old('qty_rusak') }}" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="alasan">Alasan / Deskripsi Kerusakan *</label>
                <textarea name="alasan" id="alasan" class="form-control" rows="3" placeholder="Contoh: Kemasan penyok parah saat bongkar muat dari truk, produk bocor." required style="min-height: 80px;">{{ old('alasan') }}</textarea>
            </div>

            <div class="form-group">
                <label class="form-label" for="foto_bukti">Foto Bukti Fisik Kerusakan *</label>
                <input type="file" name="foto_bukti" id="foto_bukti" class="form-control" accept="image/*" required style="padding: 10px;">
                <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">Format: JPG, PNG, WebP. Maksimal 2MB. Foto diperlukan untuk validasi Owner.</p>
            </div>

            <div style="padding: 0.75rem 1rem; background: rgba(244, 63, 94, 0.08); border: 1px solid rgba(244, 63, 94, 0.2); border-radius: 0.5rem; margin-bottom: 1.5rem;">
                <p style="color: #fca5a5; font-size: 0.85rem; margin: 0;">
                    ⚠ <strong>Perhatian:</strong> Setelah laporan ini dikirim, stok akan langsung dikurangi dari database dan menunggu keputusan Owner. Jika laporan <em>ditolak</em>, stok otomatis dikembalikan.
                </p>
            </div>

            <div style="display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-danger" style="flex: 1;">
                    Kirim Laporan Karantina
                </button>
                <a href="{{ route('damaged.index') }}" class="btn btn-secondary" style="flex: 1;">
                    Batal
                </a>
            </div>
        </form>
    </div>
</x-app-layout>
