<x-app-layout>
    <div style="margin-bottom: 2rem;">
        <a href="{{ route('inbound.index') }}" style="color: var(--accent-blue); text-decoration: none; font-weight: 500; font-size: 0.9rem;">
            &larr; Kembali ke Riwayat Inbound
        </a>
        <h1 style="margin-top: 0.5rem;">Proses Penerimaan Barang Masuk</h1>
        <p>Pilih Purchase Order yang sudah dipesan ke supplier dan siap diterima, lalu catat detail penerimaan batch barang.</p>
    </div>

    {{-- Step 1: Select a PO --}}
    @if(!$selectedPo)
        <div class="glass-card" style="max-width: 600px;">
            <div class="card-title">Langkah 1: Pilih atau Masukkan No. Purchase Order</div>
            <form method="GET" action="{{ route('inbound.create') }}">
                <div class="form-group">
                    <label class="form-label" for="po_id">Pilih Dokumen PO yang Siap Diterima</label>
                    <select name="po_id" id="po_id" class="form-control select2" required onchange="this.form.submit()">
                        <option value="" disabled selected>-- Pilih Nomor PO --</option>
                        @foreach($approvedPos as $po)
                            <option value="{{ $po->id }}" {{ $selectedPo && $selectedPo->id == $po->id ? 'selected' : '' }}>
                                {{ $po->po_number }} | {{ $po->supplier->nama_supplier }} | {{ \Carbon\Carbon::parse($po->tanggal_po ?? $po->created_at)->format('d M Y') }}
                            </option>
                        @endforeach
                    </select>
                    @if(count($approvedPos) === 0)
                        <p style="font-size: 0.8rem; color: var(--accent-yellow); margin-top: 0.5rem;">Belum ada Purchase Order yang siap diterima.</p>
                    @endif
                </div>
                <noscript>
                    <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">
                        Tampilkan Detail PO & Form Penerimaan
                    </button>
                </noscript>
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
                <span class="badge {{ $selectedPo->status == 'Completed' ? 'badge-green' : 'badge-yellow' }}">{{ $selectedPo->status }}</span>
            </div>

            <form method="POST" action="{{ route('inbound.store') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="po_id" value="{{ $selectedPo->id }}">

                <div style="display:flex; flex-direction:column; gap:1.5rem;">
                    @foreach($poDetails as $index => $detail)
                        @php
                            $sisaQty = $detail->qty_pesan - $detail->qty_diterima;
                            $history = $selectedPo->receivingHistory->where('produk_id', $detail->produk_id);
                            $totalSudahDatang = $history->sum('qty_datang');
                            $totalDiterima = $history->sum('qty_diterima');
                            $totalRusak = $history->sum('qty_rusak');
                        @endphp
                        @if($sisaQty > 0)
                        <div style="background:rgba(0,0,0,0.2); padding:1rem; border-radius:0.5rem; border:1px solid rgba(255,255,255,0.05);">
                            <div style="margin-bottom:1rem; border-bottom:1px dashed rgba(255,255,255,0.1); padding-bottom:0.5rem; font-size: 1.1rem;">
                                <strong>{{ $detail->product->nama_produk }}</strong> <code>({{ $detail->produk_id }})</code>
                            </div>
                            
                            {{-- Ringkasan Progres --}}
                            <div style="background:rgba(255,255,255,0.02); padding:0.75rem; border-radius:0.5rem; margin-bottom:1rem; font-size: 0.9rem; border-left: 3px solid var(--accent-blue); display:flex; gap:1.5rem; flex-wrap:wrap;">
                                <div><small style="color:var(--text-muted); display:block;">Qty Dipesan</small><strong>{{ $detail->qty_pesan }} {{ $detail->product->uom }}</strong></div>
                                <div><small style="color:var(--text-muted); display:block;">Total Sudah Datang</small><strong>{{ $totalSudahDatang }} {{ $detail->product->uom }}</strong></div>
                                <div><small style="color:var(--text-muted); display:block;">Total Diterima (Baik)</small><strong style="color:var(--accent-green);">{{ $totalDiterima }} {{ $detail->product->uom }}</strong></div>
                                <div><small style="color:var(--text-muted); display:block;">Total Rusak</small><strong style="color:var(--accent-red);">{{ $totalRusak }} {{ $detail->product->uom }}</strong></div>
                                <div><small style="color:var(--text-muted); display:block;">Sisa Harus Dipenuhi</small><strong style="color:var(--accent-yellow);">{{ $sisaQty }} {{ $detail->product->uom }}</strong></div>
                            </div>
                            
                            <input type="hidden" name="items[{{ $index }}][produk_id]" value="{{ $detail->produk_id }}">
                            
                            <div class="grid-4" style="gap:1rem;">
                                <div>
                                    <label class="form-label">Qty Datang</label>
                                    <input type="number" name="items[{{ $index }}][qty_datang]" id="qty_datang_{{ $index }}" class="form-control" min="0" max="{{ $sisaQty }}" value="0" oninput="calculateReceive({{ $index }})" style="background: rgba(15, 23, 42, 0.8);">
                                </div>
                                <div>
                                    <label class="form-label">Qty Rusak</label>
                                    <input type="number" name="items[{{ $index }}][qty_rusak]" id="qty_rusak_{{ $index }}" class="form-control" min="0" max="{{ $sisaQty }}" value="0" oninput="calculateReceive({{ $index }})" style="background: rgba(15, 23, 42, 0.8);">
                                </div>
                                <div>
                                    <label class="form-label">Qty Diterima (Baik)</label>
                                    <input type="number" id="qty_diterima_{{ $index }}" class="form-control" readonly style="background: rgba(255, 255, 255, 0.03); color: var(--accent-green); font-weight: bold; border-color: rgba(255, 255, 255, 0.05); opacity: 0.8; cursor: not-allowed;">
                                </div>
                                <div>
                                    <label class="form-label">Kondisi Barang</label>
                                    <input type="text" id="kondisi_{{ $index }}" class="form-control" readonly style="background: rgba(255, 255, 255, 0.03); border-color: rgba(255, 255, 255, 0.05); opacity: 0.8; cursor: not-allowed;">
                                </div>
                            </div>
                            
                            <div class="grid-3" style="gap:1rem; margin-top:1rem;" id="good_fields_{{ $index }}">
                                <div>
                                    <label class="form-label">Tanggal Expired</label>
                                    <input type="date" name="items[{{ $index }}][expired_date]" id="expired_{{ $index }}" class="form-control" style="background: rgba(15, 23, 42, 0.8);">
                                </div>
                                <div>
                                    <label class="form-label">Rak Penyimpanan</label>
                                    <select name="items[{{ $index }}][rak_id]" id="rak_{{ $index }}" class="form-control" style="background: rgba(15, 23, 42, 0.8); color: var(--text-main);">
                                        <option value="">-- Pilih Rak --</option>
                                        @foreach($racks as $r)
                                            <option value="{{ $r->kode_rak }}" {{ $loop->first ? 'selected' : '' }}>Rak {{ $r->kode_rak }} (Sisa: {{ $r->kapasitas_maksimum_volume - $r->kapasitas_terpakai }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label">Batch Supplier (Opsional)</label>
                                    <input type="text" name="items[{{ $index }}][batch_supplier]" class="form-control" placeholder="Masukkan jika ada" style="background: rgba(15, 23, 42, 0.8);">
                                </div>
                            </div>

                            <div class="grid-2" style="gap:1rem; margin-top:1rem; display:none;" id="damaged_fields_{{ $index }}">
                                <div>
                                    <label class="form-label">Alasan Kerusakan</label>
                                    <select name="items[{{ $index }}][alasan_kerusakan]" id="alasan_{{ $index }}" class="form-control" onchange="toggleCatatan({{ $index }})" style="background: rgba(15, 23, 42, 0.8); color: var(--text-main);">
                                        <option value="">-- Pilih Alasan --</option>
                                        <option value="Kemasan penyok">Kemasan penyok</option>
                                        <option value="Kardus basah">Kardus basah</option>
                                        <option value="Segel rusak">Segel rusak</option>
                                        <option value="Barang pecah">Barang pecah</option>
                                        <option value="Expired">Expired</option>
                                        <option value="Lainnya">Lainnya</option>
                                    </select>
                                </div>
                                <div id="catatan_container_{{ $index }}" style="display:none;">
                                    <label class="form-label">Catatan Kerusakan</label>
                                    <input type="text" name="items[{{ $index }}][catatan]" class="form-control" placeholder="Jelaskan kerusakan..." style="background: rgba(15, 23, 42, 0.8);">
                                </div>
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>

                {{-- Upload Bukti Serah Terima --}}
                <div style="background: rgba(15, 23, 42, 0.4); padding: 1.5rem; border-radius: 0.75rem; border: 1px solid rgba(255,255,255,0.08); margin-top: 1.5rem;">
                    <div style="margin-bottom: 1rem; border-bottom: 1px dashed rgba(255,255,255,0.1); padding-bottom: 0.5rem; font-size: 1.05rem; font-weight: 600; color: #fff;">
                        Upload Bukti Serah Terima
                    </div>
                    <div>
                        <label class="form-label" for="foto_bukti" style="font-weight: 600; margin-bottom: 0.5rem;">Foto Bukti Serah Terima @if(auth()->user()->role === 'staff_gudang') <span style="color: var(--accent-red);">*</span> @endif</label>
                        <input type="file" name="foto_bukti" id="foto_bukti" class="form-control" accept="image/*" @if(auth()->user()->role === 'staff_gudang') required @endif style="padding: 8px 12px; height: auto; background: rgba(15, 23, 42, 0.8);">
                        <small style="color:var(--text-muted); display:block; margin-top:0.5rem;">Format file: JPG, JPEG, PNG (Maksimal 2MB).</small>
                    </div>
                </div>

                <div style="margin-top:2rem; display:flex; gap:1rem;">
                    <button type="submit" class="btn btn-primary" style="flex:1;" onclick="return confirm('Simpan data penerimaan ini?')">Simpan Penerimaan Barang</button>
                    <a href="{{ route('inbound.create') }}" class="btn btn-secondary" style="flex:1; text-align:center;">Ganti PO</a>
                </div>
            </form>

            <script>
                function calculateReceive(idx) {
                    const qtyDatangEl = document.getElementById('qty_datang_' + idx);
                    const qtyRusakEl = document.getElementById('qty_rusak_' + idx);
                    const qtyDiterimaEl = document.getElementById('qty_diterima_' + idx);
                    const kondisiEl = document.getElementById('kondisi_' + idx);
                    const goodFields = document.getElementById('good_fields_' + idx);
                    const damagedFields = document.getElementById('damaged_fields_' + idx);
                    const expiredEl = document.getElementById('expired_' + idx);
                    const rakEl = document.getElementById('rak_' + idx);

                    let datang = parseInt(qtyDatangEl.value) || 0;
                    let rusak = parseInt(qtyRusakEl.value) || 0;

                    if (rusak > datang) {
                        rusak = datang;
                        qtyRusakEl.value = rusak;
                    }

                    let diterima = datang - rusak;
                    qtyDiterimaEl.value = diterima;

                    if (datang === 0) {
                        kondisiEl.value = '';
                        kondisiEl.style.color = 'inherit';
                        goodFields.style.opacity = '0.5';
                        damagedFields.style.display = 'none';
                        expiredEl.removeAttribute('required');
                        rakEl.removeAttribute('required');
                        return;
                    }

                    if (rusak === 0) {
                        kondisiEl.value = 'Baik';
                        kondisiEl.style.color = 'var(--accent-green)';
                        damagedFields.style.display = 'none';
                    } else if (diterima > 0) {
                        kondisiEl.value = 'Rusak Sebagian';
                        kondisiEl.style.color = 'var(--accent-yellow)';
                        damagedFields.style.display = 'flex';
                    } else {
                        kondisiEl.value = 'Ditolak';
                        kondisiEl.style.color = 'var(--accent-red)';
                        damagedFields.style.display = 'flex';
                    }

                    if (diterima > 0) {
                        goodFields.style.opacity = '1';
                        expiredEl.setAttribute('required', 'required');
                        rakEl.setAttribute('required', 'required');
                    } else {
                        goodFields.style.opacity = '0.5';
                        expiredEl.removeAttribute('required');
                        rakEl.removeAttribute('required');
                    }
                }

                function toggleCatatan(idx) {
                    const select = document.getElementById('alasan_' + idx);
                    const container = document.getElementById('catatan_container_' + idx);
                    if (select.value === 'Lainnya') {
                        container.style.display = 'block';
                    } else {
                        container.style.display = 'none';
                    }
                }
            </script>
        </div>
    @endif
</x-app-layout>
