<x-app-layout>
    <div style="margin-bottom: 2rem;">
        <a href="{{ route('outbound.create') }}" style="color: var(--accent-blue); text-decoration: none; font-weight: 500; font-size: 0.9rem;">
            &larr; Kembali &amp; Ulangi Pilihan Produk
        </a>
        <h1 style="margin-top: 0.5rem;">Konfirmasi Pengambilan Fisik Barang</h1>
        <p>Sistem telah menentukan batch yang harus diambil sesuai algoritma <strong>FEFO</strong>. Ambil barang dari rak yang ditunjuk, kemudian <strong>input atau scan</strong> nomor batch dari label fisik untuk memvalidasi.</p>
    </div>

    {{-- Warning Banner --}}
    <div style="background: rgba(251,191,36,0.1); border: 1px solid rgba(251,191,36,0.4); border-radius: 0.75rem; padding: 1rem 1.25rem; margin-bottom: 1.5rem; display: flex; align-items: flex-start; gap: 0.75rem;">
        <span style="font-size: 1.25rem; flex-shrink: 0;">⚠️</span>
        <div style="font-size: 0.88rem; color: var(--text-muted); line-height: 1.6;">
            <strong style="color: var(--accent-yellow);">Tujuan Pengiriman:</strong>
            <span style="color: #f1f5f9; font-weight: 500;">{{ $tujuan }}</span><br>
            Stok <strong>HANYA akan dipotong</strong> jika semua nomor batch yang Anda masukkan cocok persis dengan instruksi FEFO di bawah. Perbedaan satu karakter pun akan ditolak sistem.
        </div>
    </div>

    {{-- Error --}}
    @if(session('error'))
        <div style="background: rgba(239,68,68,0.12); border: 1px solid rgba(239,68,68,0.4); border-radius: 0.75rem; padding: 1rem 1.25rem; margin-bottom: 1.5rem; color: #fca5a5; font-size: 0.88rem;">
            {!! session('error') !!}
        </div>
    @endif

    <form action="{{ route('outbound.confirm') }}" method="POST" id="confirmForm">
        @csrf

        <div class="glass-card">
            <div class="card-title">
                <span>🔍 Picking Slip — Instruksi FEFO &amp; Konfirmasi Batch Fisik</span>
                <span class="badge badge-yellow pulse-indicator">{{ count($pickingSlip) }} Baris</span>
            </div>
            <div class="table-responsive">
                <table class="table-premium">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Produk</th>
                            <th>Qty Dikeluarkan</th>
                            <th>Lokasi Rak</th>
                            <th>
                                <span style="color: var(--accent-yellow);">📦 Batch FEFO (Instruksi)</span><br>
                                <span style="font-size: 0.7rem; font-weight: 400; color: var(--text-muted);">Ambil dari batch ini</span>
                            </th>
                            <th>Exp. Date</th>
                            <th>
                                <span style="color: var(--accent-green);">✅ Input / Scan Batch Fisik *</span><br>
                                <span style="font-size: 0.7rem; font-weight: 400; color: var(--text-muted);">Ketik atau scan dari label</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pickingSlip as $index => $pick)
                            <tr class="confirm-row" id="row-{{ $index }}">
                                <td style="color: var(--text-muted);">{{ $index + 1 }}</td>
                                <td>
                                    <strong>{{ $pick['produk_nama'] }}</strong><br>
                                    <span style="font-size: 0.75rem; color: var(--text-muted);">{{ $pick['produk_id'] }}</span>
                                </td>
                                <td>
                                    <strong style="color: var(--accent-blue); font-size: 1.05rem;">{{ $pick['qty_keluar'] }}</strong>
                                    <span style="font-size: 0.75rem; color: var(--text-muted);"> unit</span>
                                </td>
                                <td>
                                    <span class="badge badge-blue">📍 Rak {{ $pick['rak_id'] }}</span>
                                </td>
                                <td>
                                    <code style="background: rgba(251,191,36,0.12); color: var(--accent-yellow); padding: 0.3rem 0.5rem; border-radius: 4px; font-size: 0.85rem; letter-spacing: 0.03em;">
                                        {{ $pick['batch_number'] }}
                                    </code>
                                </td>
                                <td>
                                    @php
                                        $expDate  = $pick['expired_date'];
                                        $expCarbon = \Carbon\Carbon::parse($expDate);
                                        $daysLeft  = now()->diffInDays($expCarbon, false);
                                    @endphp
                                    <span style="font-size: 0.85rem; color: {{ $daysLeft < 14 ? 'var(--accent-red)' : ($daysLeft < 30 ? 'var(--accent-yellow)' : 'var(--text-muted)') }};">
                                        {{ $expCarbon->format('d M Y') }}
                                        @if($daysLeft >= 0)
                                            <br><small>({{ $daysLeft }} hari lagi)</small>
                                        @else
                                            <br><small style="color:var(--accent-red); font-weight:600;">EXPIRED</small>
                                        @endif
                                    </span>
                                </td>
                                <td>
                                    <div style="position: relative;">
                                        <input type="text"
                                               name="batch_scanned[{{ $index }}]"
                                               id="scan_{{ $index }}"
                                               class="form-control batch-scan-input"
                                               placeholder="Ketik atau scan batch..."
                                               data-expected="{{ $pick['batch_number'] }}"
                                               autocomplete="off"
                                               required
                                               style="font-family: monospace; font-size: 0.9rem; padding-right: 2.5rem; text-transform: uppercase;"
                                               value="{{ old('batch_scanned.' . $index) }}">
                                        <span class="scan-indicator" id="ind-{{ $index }}"
                                              style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); font-size: 1rem; pointer-events: none; opacity: 0; transition: opacity 0.2s;">
                                        </span>
                                    </div>
                                    <div class="scan-feedback" id="fb-{{ $index }}" style="font-size: 0.72rem; margin-top: 0.25rem; min-height: 1em;"></div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div style="margin-top: 1.5rem; display: flex; gap: 1rem; flex-wrap: wrap;">
            <button type="submit" class="btn btn-success" style="flex: 2; min-width: 200px; font-size: 1rem; font-weight: 700; letter-spacing: 0.02em;" id="confirmBtn">
                ✅ Konfirmasi &amp; Selesaikan Outbound
            </button>
            <a href="{{ route('outbound.create') }}" class="btn btn-secondary" style="flex: 1; min-width: 150px;" onclick="return confirm('Batalkan proses ini? Picking slip akan dihapus dari sesi.')">
                ✕ Batalkan
            </a>
        </div>
    </form>

    <style>
        .confirm-row { transition: background 0.2s; }
        .confirm-row.row-match  { background: rgba(16,185,129,0.06); }
        .confirm-row.row-mismatch { background: rgba(239,68,68,0.07); }

        .batch-scan-input:focus { border-color: var(--accent-blue) !important; }
        .batch-scan-input.input-match    { border-color: var(--accent-green) !important; }
        .batch-scan-input.input-mismatch { border-color: var(--accent-red)   !important; }
    </style>

    <script>
        // Live validation: compare scan input against expected FEFO batch
        document.querySelectorAll('.batch-scan-input').forEach(function(input) {
            input.addEventListener('input', function() {
                const expected  = this.dataset.expected.toUpperCase();
                const entered   = this.value.trim().toUpperCase();
                const index     = this.id.replace('scan_', '');
                const indicator = document.getElementById('ind-' + index);
                const feedback  = document.getElementById('fb-' + index);
                const row       = document.getElementById('row-' + index);

                this.value = this.value.toUpperCase(); // force uppercase

                if (!entered) {
                    this.classList.remove('input-match', 'input-mismatch');
                    indicator.style.opacity = '0';
                    feedback.textContent = '';
                    row.classList.remove('row-match', 'row-mismatch');
                    return;
                }

                if (entered === expected) {
                    this.classList.add('input-match');
                    this.classList.remove('input-mismatch');
                    indicator.textContent = '✅';
                    indicator.style.opacity = '1';
                    feedback.textContent = '✔ Batch cocok!';
                    feedback.style.color = 'var(--accent-green)';
                    row.classList.add('row-match');
                    row.classList.remove('row-mismatch');
                } else if (expected.startsWith(entered)) {
                    // partial match - neutral
                    this.classList.remove('input-match', 'input-mismatch');
                    indicator.textContent = '⌨️';
                    indicator.style.opacity = '1';
                    feedback.textContent = 'Terus ketik...';
                    feedback.style.color = 'var(--text-muted)';
                    row.classList.remove('row-match', 'row-mismatch');
                } else {
                    this.classList.add('input-mismatch');
                    this.classList.remove('input-match');
                    indicator.textContent = '❌';
                    indicator.style.opacity = '1';
                    feedback.textContent = `Tidak cocok. Harusnya: ${expected}`;
                    feedback.style.color = 'var(--accent-red)';
                    row.classList.add('row-mismatch');
                    row.classList.remove('row-match');
                }
            });
        });

        // Check all match before submit
        document.getElementById('confirmForm').addEventListener('submit', function(e) {
            const inputs    = document.querySelectorAll('.batch-scan-input');
            const allMatch  = Array.from(inputs).every(inp =>
                inp.value.trim().toUpperCase() === inp.dataset.expected.toUpperCase()
            );

            if (!allMatch) {
                e.preventDefault();
                alert('⚠️ Ada batch yang tidak cocok atau belum diisi!\nPastikan semua input batch sudah terisi dan COCOK dengan instruksi FEFO sebelum mengkonfirmasi.');
            }
        });
    </script>
</x-app-layout>
