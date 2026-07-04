<x-app-layout>
    <div style="margin-bottom: 2rem;">
        <h1>Review Pengajuan Restock</h1>
        <p>Tinjau pengajuan restock dari Staff Gudang. Approve untuk membuat PO Draft otomatis, atau Reject dengan alasan.</p>
    </div>

    <div class="glass-card">
        <div class="table-responsive">
            <table class="table-premium">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Stok Saat Ini</th>
                        <th>Min Stok</th>
                        <th>Qty Diajukan</th>
                        <th>Alasan</th>
                        <th>Diajukan Oleh</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $req)
                        <tr>
                            <td>
                                <strong>{{ $req->product->nama_produk }}</strong><br>
                                <span style="font-size:0.75rem; color:var(--text-muted);">{{ $req->produk_id }}</span>
                            </td>
                            <td>
                                @php $stok = $req->product->total_stok; @endphp
                                <strong style="color: var({{ $stok <= $req->product->stok_minimum ? '--accent-red' : '--accent-green' }});">{{ $stok }}</strong>
                            </td>
                            <td>{{ $req->product->stok_minimum }}</td>
                            <td><strong>{{ $req->qty_request }}</strong> {{ $req->product->uom }}</td>
                            <td style="max-width:180px; font-size:0.85rem;">{{ $req->alasan }}</td>
                            <td>
                                <strong>{{ $req->requester->name }}</strong><br>
                                <span style="font-size:0.75rem; color:var(--text-muted);">{{ strtoupper(str_replace('_', ' ', $req->requester->role)) }}</span>
                            </td>
                            <td style="white-space:nowrap;">{{ $req->created_at->format('d M Y') }}<br><span style="font-size:0.78rem; color:var(--text-muted);">{{ $req->created_at->format('H:i') }}</span></td>
                            <td>
                                @if($req->status === 'Menunggu Review')
                                    <span class="badge badge-yellow pulse-indicator">⏳ Menunggu</span>
                                @elseif($req->status === 'Approved')
                                    <span class="badge badge-green">✅ Approved</span>
                                @else
                                    <span class="badge badge-red">❌ Rejected</span>
                                @endif
                            </td>
                            <td>
                                @if($req->status === 'Menunggu Review')
                                    <div style="display:flex; gap:0.4rem; flex-direction:column;">
                                        {{-- Approve Button --}}
                                        <button type="button" class="btn btn-success" style="padding:5px 10px; font-size:0.8rem;" onclick="showApproveModal({{ $req->id }}, '{{ addslashes($req->product->nama_produk) }}', {{ $req->qty_request }})">
                                            ✓ Approve
                                        </button>
                                        {{-- Reject Button --}}
                                        <button type="button" class="btn btn-danger" style="padding:5px 10px; font-size:0.8rem;" onclick="showRejectModal({{ $req->id }}, '{{ addslashes($req->product->nama_produk) }}')">
                                            ✕ Reject
                                        </button>
                                    </div>
                                @elseif($req->status === 'Approved' && $req->purchaseOrder)
                                    <a href="{{ route('po.show', $req->po_id) }}" class="btn btn-secondary" style="padding:5px 10px; font-size:0.8rem;">
                                        Lihat PO
                                    </a>
                                @elseif($req->status === 'Rejected')
                                    <span style="font-size:0.78rem; color:var(--accent-red);">{{ $req->alasan_reject }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align:center; padding:2rem; color:var(--text-muted);">
                                Belum ada pengajuan restock dari Staff Gudang.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Approve Modal --}}
    <div id="approveModal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.6); z-index:9999; justify-content:center; align-items:center;">
        <div class="glass-card" style="max-width:480px; width:90%; margin:auto; margin-top:15vh;">
            <div class="card-title">
                <span>✓ Approve Pengajuan Restock</span>
            </div>
            <p id="approveProductName" style="margin-bottom:1rem;"></p>
            <form id="approveForm" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label" for="supplier_id">Pilih Supplier untuk PO *</label>
                    <select name="supplier_id" id="approve_supplier_id" class="form-control" required>
                        <option value="" disabled selected>Pilih Supplier</option>
                        @php $suppliers = \App\Models\Supplier::all(); @endphp
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->nama_supplier }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="display:flex; gap:0.75rem; margin-top:1.5rem;">
                    <button type="submit" class="btn btn-success" style="flex:1;">Approve & Buat PO Draft</button>
                    <button type="button" class="btn btn-secondary" style="flex:1;" onclick="closeModals()">Batal</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Reject Modal --}}
    <div id="rejectModal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.6); z-index:9999; justify-content:center; align-items:center;">
        <div class="glass-card" style="max-width:480px; width:90%; margin:auto; margin-top:15vh;">
            <div class="card-title">
                <span style="color:var(--accent-red);">✕ Tolak Pengajuan Restock</span>
            </div>
            <p id="rejectProductName" style="margin-bottom:1rem;"></p>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label" for="alasan_reject">Alasan Penolakan *</label>
                    <textarea name="alasan_reject" id="alasan_reject" class="form-control" rows="3" placeholder="Jelaskan alasan penolakan..." required></textarea>
                </div>
                <div style="display:flex; gap:0.75rem; margin-top:1.5rem;">
                    <button type="submit" class="btn btn-danger" style="flex:1;">Tolak Pengajuan</button>
                    <button type="button" class="btn btn-secondary" style="flex:1;" onclick="closeModals()">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function showApproveModal(id, productName, qty) {
        document.getElementById('approveProductName').innerHTML = 'Setujui pengajuan restock <strong>' + productName + '</strong> sebanyak <strong>' + qty + ' unit</strong>? Sistem akan otomatis membuat PO Draft.';
        document.getElementById('approveForm').action = '/restock-request/' + id + '/approve';
        document.getElementById('approveModal').style.display = 'flex';
    }

    function showRejectModal(id, productName) {
        document.getElementById('rejectProductName').innerHTML = 'Tolak pengajuan restock untuk <strong>' + productName + '</strong>?';
        document.getElementById('rejectForm').action = '/restock-request/' + id + '/reject';
        document.getElementById('rejectModal').style.display = 'flex';
    }

    function closeModals() {
        document.getElementById('approveModal').style.display = 'none';
        document.getElementById('rejectModal').style.display = 'none';
    }

    // Close modal on outside click
    document.getElementById('approveModal').addEventListener('click', function(e) { if (e.target === this) closeModals(); });
    document.getElementById('rejectModal').addEventListener('click', function(e) { if (e.target === this) closeModals(); });
    </script>
</x-app-layout>
