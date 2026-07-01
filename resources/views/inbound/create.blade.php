<x-app-layout>
    <div style="margin-bottom: 2rem;">
        <a href="{{ route('inbound.index') }}" style="color: var(--accent-blue); text-decoration: none; font-weight: 500; font-size: 0.9rem;">
            &larr; Kembali ke Riwayat Inbound
        </a>
        <h1 style="margin-top: 0.5rem;">Proses Penerimaan Barang Masuk</h1>
        <p>Masukkan No. PO yang sudah <strong>Approved</strong> oleh Owner, lalu catat detail penerimaan batch barang dari supplier.</p>
    </div>

    {{-- Step 1: Select a PO --}}
    @if(!$selectedPo)
        <div class="glass-card" style="max-width: 600px;">
            <div class="card-title">Langkah 1: Pilih atau Masukkan No. Purchase Order</div>
            <form method="GET" action="{{ route('inbound.create') }}">
                <div class="form-group">
                    <label class="form-label" for="po_id">Pilih Dokumen PO yang Sudah Disetujui (Approved)</label>
                    <select name="po_id" id="po_id" class="form-control" required>
                        <option value="" disabled selected>-- Pilih Nomor PO --</option>
                        @foreach($approvedPos as $po)
                            <option value="{{ $po->id }}">{{ $po->po_number }} — {{ $po->supplier->nama_supplier }}</option>
                        @endforeach
                    </select>
                    @if(count($approvedPos) === 0)
                        <p style="font-size: 0.8rem; color: var(--accent-yellow); margin-top: 0.5rem;">Tidak ada PO berstatus "Approved". Minta Owner untuk menyetujui PO terlebih dahulu.</p>
                    @endif
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;" {{ count($approvedPos) === 0 ? 'disabled' : '' }}>
                    Tampilkan Detail PO & Form Penerimaan
                </button>
            </form>
        </div>
    @else
        {{-- Step 2: Input received quantities --}}
        <div class="instruction-box" style="max-width: 800px;">
            <h4>Panduan Penerimaan Barang (FR-01)</h4>
            <ol>
                <li>Hitung fisik jumlah barang yang diturunkan dari kendaraan pengiriman.</li>
                <li>Masukkan Qty yang benar-benar diterima, No. Batch Produksi, dan Tanggal Kedaluwarsa dari label produk.</li>
                <li>Sistem akan menampilkan rekomendasi rak terbaik secara otomatis setelah disimpan.</li>
            </ol>
        </div>

        <div class="glass-card" style="max-width: 800px;">
            <div class="card-title">
                <span>PO: <code>{{ $selectedPo->po_number }}</code> — {{ $selectedPo->supplier->nama_supplier }}</span>
                <span class="badge badge-green">Approved</span>
            </div>

            <form method="POST" action="{{ route('inbound.store') }}">
                @csrf
                <input type="hidden" name="po_id" value="{{ $selectedPo->id }}">

                @foreach($poDetails as $index => $detail)
                    @php
                        $sisaQty = $detail->qty_pesan - $detail->qty_diterima;
                    @endphp
                    <div style="padding: 1.25rem; background: rgba(255,255,255,0.03); border-radius: 0.75rem; border: 1px solid var(--border-color); margin-bottom: 1rem;">
                        <div style="margin-bottom: 1rem;">
                            <strong>{{ $detail->product->nama_produk }}</strong>
                            <span style="color: var(--text-muted); font-size: 0.85rem;">({{ $detail->produk_id }}) — Sisa yang Perlu Diterima: <span style="color: var(--accent-yellow); font-weight: 700;">{{ $sisaQty }} {{ $detail->product->uom }}</span></span>
                        </div>
                        <input type="hidden" name="items[{{ $index }}][produk_id]" value="{{ $detail->produk_id }}">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.75rem;">
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label">Qty Nyata Diterima (Maks: {{ $sisaQty }}) *</label>
                                <input type="number" name="items[{{ $index }}][qty_terima]" class="form-control" min="1" max="{{ $sisaQty }}" required>
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label">No. Batch Produksi Pabrik *</label>
                                <input type="text" name="items[{{ $index }}][batch_number]" class="form-control" placeholder="Contoh: BTC-20260601-A" required>
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label">Tanggal Kedaluwarsa *</label>
                                <input type="date" name="items[{{ $index }}][expired_date]" class="form-control" min="{{ date('Y-m-d', strtotime('+1 day')) }}" required>
                            </div>
                        </div>
                    </div>
                @endforeach

                <div style="margin-top: 1.5rem; display: flex; gap: 1rem;">
                    <button type="submit" class="btn btn-success" style="flex: 1;">
                        ✓ Konfirmasi Penerimaan & Dapatkan Rekomendasi Rak
                    </button>
                    <a href="{{ route('inbound.create') }}" class="btn btn-secondary" style="flex: 1;">
                        Ganti PO
                    </a>
                </div>
            </form>
        </div>
    @endif
</x-app-layout>
