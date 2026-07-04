<x-app-layout>
    <div style="margin-bottom: 2rem;">
        <a href="{{ route('po.index') }}" style="color: var(--accent-blue); text-decoration: none; font-weight: 500; font-size: 0.9rem;">
            &larr; Kembali ke Daftar PO
        </a>
        <h1 style="margin-top: 0.5rem;">Detail Purchase Order</h1>
        <p>Rincian dokumen pengadaan dan daftar item barang yang dipesan.</p>
    </div>

    <div style="display: grid; grid-template-columns: 1fr; gap: 1.5rem;">
        {{-- PO Header Info --}}
        <div class="glass-card">
            <div class="card-title">
                <span>Informasi PO: <code>{{ $po->po_number }}</code></span>
                @if($po->status === 'Draft')
                    <span class="badge badge-blue">Draft</span>
                @elseif($po->status === 'Ordered')
                    <span class="badge badge-yellow pulse-indicator">Ordered</span>
                @elseif($po->status === 'Partially Received')
                    <span class="badge badge-yellow">🟡 Partially Received</span>
                @elseif($po->status === 'Completed')
                    <span class="badge badge-green">🟢 Completed</span>
                @elseif($po->status === 'Cancelled')
                    <span class="badge badge-red">🔴 Cancelled</span>
                @else
                    <span class="badge badge-red">{{ $po->status }}</span>
                @endif
            </div>
            <div class="grid-2">
                <div>
                    <p style="margin-bottom: 0.25rem;"><strong>Supplier:</strong> {{ $po->supplier->nama_supplier }}</p>
                    <p style="margin-bottom: 0.25rem;"><strong>Kontak Supplier:</strong> {{ $po->supplier->kontak }}</p>
                    <p style="margin-bottom: 0;"><strong>Dibuat oleh:</strong> {{ $po->creator->name }} ({{ strtoupper(str_replace('_', ' ', $po->creator->role)) }})</p>
                </div>
                <div>
                    <p style="margin-bottom: 0.25rem;"><strong>Tgl. Dibuat:</strong> {{ $po->created_at->format('d F Y, H:i') }}</p>
                    <p style="margin-bottom: 0;"><strong>Total Nilai Pengadaan:</strong>
                        <span style="color: var(--accent-blue); font-size: 1.1rem; font-weight: 700;">Rp {{ number_format($po->total_harga, 0, ',', '.') }}</span>
                    </p>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div style="margin-top: 1.5rem; display: flex; gap: 0.75rem; flex-wrap: wrap; border-top: 1px solid var(--border-color); padding-top: 1.25rem;">
                @if($po->status === 'Draft' && auth()->user()->role === 'admin_gudang')
                    <form action="{{ route('po.order', $po->id) }}" method="POST" onsubmit="return confirm('Pesan PO ini ke Supplier?');">
                        @csrf
                        <button type="submit" class="btn btn-primary">Pesan ke Supplier</button>
                    </form>
                @endif

            </div>
        </div>

        {{-- PO Items Detail Table --}}
        <div class="glass-card">
            <div class="card-title">Rincian Item Barang yang Dipesan</div>
            <div class="table-responsive">
                <table class="table-premium">
                    <thead>
                        <tr>
                            <th>Kode SKU</th>
                            <th>Nama Produk</th>
                            <th>Satuan</th>
                            <th>Qty Dipesan</th>
                            <th>Qty Diterima</th>
                            <th>Qty Sisa</th>
                            <th>Status Penerimaan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($po->details as $detail)
                            @php
                                $sisaQty = $detail->qty_pesan - $detail->qty_diterima;
                            @endphp
                            <tr>
                                <td><code>{{ $detail->produk_id }}</code></td>
                                <td><strong>{{ $detail->product->nama_produk }}</strong></td>
                                <td>{{ $detail->product->uom }}</td>
                                <td>{{ $detail->qty_pesan }}</td>
                                <td>
                                    @if($detail->qty_diterima > 0)
                                        <strong style="color: var(--accent-green);">{{ $detail->qty_diterima }}</strong>
                                    @else
                                        <span style="color: var(--text-muted);">0</span>
                                    @endif
                                </td>
                                <td>
                                    @if($sisaQty > 0)
                                        <span style="color: var(--accent-yellow);">{{ $sisaQty }}</span>
                                    @else
                                        <strong style="color: var(--accent-green);">Lunas</strong>
                                    @endif
                                </td>
                                <td>
                                    @if($detail->qty_diterima >= $detail->qty_pesan)
                                        <span class="badge badge-green">🟢 Selesai</span>
                                    @elseif($detail->qty_diterima > 0)
                                        <span class="badge badge-yellow">🟡 Partial</span>
                                    @else
                                        <span class="badge" style="background:rgba(148,163,184,0.15); color:#94a3b8;">⚪ Pending</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Receiving History --}}
        @if($po->receivingHistory && $po->receivingHistory->count() > 0)
        <div class="glass-card">
            <div class="card-title">
                <span>Riwayat Penerimaan Barang</span>
                <span class="badge badge-blue">{{ $po->receivingHistory->count() }} Penerimaan</span>
            </div>
            <div class="table-responsive">
                <table class="table-premium" style="font-size: 0.85rem;">
                    <thead>
                        <tr>
                            <th>Tanggal & Waktu</th>
                            <th>Produk</th>
                            <th>Datang</th>
                            <th>Rusak</th>
                            <th>Diterima</th>
                            <th>Kondisi / Status Retur</th>
                            <th>Batch Int. / Supp.</th>
                            <th>Exp. Date</th>
                            <th>Rak</th>
                            <th>Diterima Oleh</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($po->receivingHistory->sortByDesc('received_at') as $history)
                            <tr>
                                <td>{{ $history->received_at->format('d M Y, H:i') }}</td>
                                <td>
                                    <strong>{{ $history->product->nama_produk }}</strong><br>
                                    <span style="font-size:0.75rem; color:var(--text-muted);">{{ $history->produk_id }}</span>
                                </td>
                                <td>{{ $history->qty_datang }}</td>
                                <td>
                                    @if($history->qty_rusak > 0)
                                        <strong style="color: var(--accent-red);">{{ $history->qty_rusak }}</strong>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td><strong style="color: var(--accent-green);">{{ $history->qty_received }}</strong> {{ $history->product->uom }}</td>
                                <td>
                                    @if($history->kondisi_barang === 'Baik')
                                        <span class="badge badge-green">Baik</span>
                                    @elseif($history->kondisi_barang === 'Rusak Sebagian')
                                        <span class="badge badge-yellow">Rusak Sbg</span>
                                    @elseif($history->kondisi_barang === 'Ditolak')
                                        <span class="badge badge-red">Ditolak</span>
                                    @endif
                                    
                                    @if($history->status_retur)
                                        <br><span style="font-size:0.7rem; color:var(--accent-yellow);">{{ $history->status_retur }}</span>
                                    @endif

                                    @if($history->alasan_kerusakan)
                                        <br><span style="font-size:0.75rem; color:var(--text-muted);">Alasan: {{ $history->alasan_kerusakan }}</span>
                                    @endif
                                    @if($history->catatan)
                                        <br><span style="font-size:0.75rem; color:var(--text-muted);">Catatan: {{ $history->catatan }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($history->batch_number)
                                        <code style="font-size:0.7rem;">Int: {{ $history->batch_number }}</code>
                                    @endif
                                    @if($history->batch_supplier)
                                        <br><code style="font-size:0.7rem;">Sup: {{ $history->batch_supplier }}</code>
                                    @endif
                                </td>
                                <td>{{ $history->expired_date ? \Carbon\Carbon::parse($history->expired_date)->format('d M Y') : '-' }}</td>
                                <td>{{ $history->rak_id ?? '-' }}</td>
                                <td>{{ $history->receiver->name }}</td>
                                <td>
                                    @if($history->qty_rusak > 0 && auth()->user()->role === 'admin_gudang')
                                        <button type="button" class="btn btn-secondary" style="padding:0.25rem 0.5rem; font-size:0.75rem;" onclick="openReturModal({{ $history->id }}, '{{ $history->status_retur }}', '{{ $history->tanggal_retur ? \Carbon\Carbon::parse($history->tanggal_retur)->format('Y-m-d') : '' }}', '{{ $history->catatan_retur ?? '' }}')">
                                            Update Retur
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>


    {{-- MODAL UPDATE RETUR --}}
    <div id="returModal" class="lightbox-modal" style="display:none; padding-top:2rem;">
        <div class="glass-card" style="width:90%; max-width:500px; margin:auto; position:relative; padding:2rem;">
            <span class="lightbox-close" onclick="closeReturModal()" style="position:absolute; top:1rem; right:1.5rem; cursor:pointer; font-size:1.5rem;">&times;</span>
            <h3 style="margin-top:0;">Update Status Retur</h3>
            <hr style="border-color:rgba(255,255,255,0.1); margin-bottom:1.5rem;">

            <form id="returForm" method="POST">
                @csrf
                <div style="display:flex; flex-direction:column; gap:1rem;">
                    <div>
                        <label class="form-label">Status Retur</label>
                        <select name="status_retur" id="retur_status" class="form-input" required onchange="toggleReturFields()">
                            <option value="Menunggu Retur">Menunggu Retur</option>
                            <option value="Sudah Diretur">Sudah Diretur</option>
                        </select>
                    </div>
                    
                    <div id="retur_extra_fields" style="display:none; flex-direction:column; gap:1rem;">
                        <div>
                            <label class="form-label">Tanggal Retur</label>
                            <input type="date" name="tanggal_retur" id="retur_tanggal" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Catatan Retur</label>
                            <textarea name="catatan_retur" id="retur_catatan" class="form-input" rows="3" placeholder="Opsional..."></textarea>
                        </div>
                    </div>
                </div>

                <div style="margin-top:2rem; text-align:right;">
                    <button type="button" class="btn btn-secondary" onclick="closeReturModal()">Batal</button>
                    <button type="submit" class="btn btn-primary" style="margin-left:0.5rem;">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openReturModal(historyId, currentStatus, currentDate, currentCatatan) {
            const form = document.getElementById('returForm');
            form.action = `{{ url('po') }}/{{ $po->id }}/history/${historyId}/retur`;

            document.getElementById('retur_status').value = currentStatus || 'Menunggu Retur';
            document.getElementById('retur_tanggal').value = currentDate || '';
            document.getElementById('retur_catatan').value = currentCatatan || '';
            
            toggleReturFields();

            document.getElementById('returModal').style.display = 'block';
        }

        function closeReturModal() {
            document.getElementById('returModal').style.display = 'none';
        }

        function toggleReturFields() {
            const status = document.getElementById('retur_status').value;
            const extraFields = document.getElementById('retur_extra_fields');
            const tanggalInput = document.getElementById('retur_tanggal');

            if (status === 'Sudah Diretur') {
                extraFields.style.display = 'flex';
                tanggalInput.setAttribute('required', 'required');
            } else {
                extraFields.style.display = 'none';
                tanggalInput.removeAttribute('required');
            }
        }
    </script>
</x-app-layout>
