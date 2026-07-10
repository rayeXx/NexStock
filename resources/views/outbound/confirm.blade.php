<x-app-layout>
    <div style="margin-bottom: 2rem;">
        <a href="{{ route('outbound.index') }}" style="color: var(--accent-blue); text-decoration: none; font-weight: 500; font-size: 0.9rem;">
            &larr; Kembali ke Riwayat Outbound
        </a>
        <h1 style="margin-top: 0.5rem; font-weight: 800; font-size: 2.2rem; letter-spacing: -0.02em;">System-Directed Picking (Staf Gudang)</h1>
        <p style="color: var(--text-muted); font-size: 1.05rem;">Silakan ambil barang fisik dari rak yang tertera di bawah. Ikuti petunjuk visual secara teliti.</p>
    </div>

    {{-- Info Pelanggan & Metadata --}}
    <div class="glass-card" style="margin-bottom: 2.5rem; padding: 1.75rem; background: linear-gradient(135deg, rgba(14, 165, 233, 0.15) 0%, rgba(30, 41, 59, 0.4) 100%); border: 1px solid rgba(14, 165, 233, 0.25); border-radius: 1.25rem; box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1.25rem;">
            <div>
                <span style="font-size: 0.85rem; color: #38bdf8; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 0.35rem;">Tujuan Pengiriman / Customer</span>
                <span style="color: #f1f5f9; font-weight: 800; font-size: 1.35rem;">{{ $tujuan }}</span>
            </div>
            <div style="text-align: right;">
                <span style="font-size: 0.85rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 0.35rem;">Nomor Outbound</span>
                <span style="font-family: monospace; color: #f8fafc; font-weight: 700; font-size: 1.1rem; background: rgba(255,255,255,0.08); padding: 0.4rem 0.80rem; border-radius: 0.5rem; border: 1px solid rgba(255,255,255,0.1);">{{ $outbound->outbound_number }}</span>
            </div>
        </div>
    </div>

    {{-- Error --}}
    @if(session('error'))
        <div style="background: rgba(239,68,68,0.12); border: 1px solid rgba(239,68,68,0.4); border-radius: 0.75rem; padding: 1rem 1.25rem; margin-bottom: 1.5rem; color: #fca5a5; font-size: 0.88rem;">
            {!! session('error') !!}
        </div>
    @endif

    <form action="{{ url('/outbound/' . $outbound->id . '/confirm') }}" method="POST" id="confirmForm" enctype="multipart/form-data">
        @csrf

        {{-- Visual Picking Instruction Cards --}}
        <div style="margin-bottom: 2.5rem; display: flex; flex-direction: column; gap: 1.5rem;">
            @foreach($pickingSlip as $index => $pick)
                <div class="glass-card" style="background: rgba(30, 41, 59, 0.45); backdrop-filter: blur(16px); border: 1px solid rgba(255,255,255,0.06); border-radius: 1.25rem; padding: 2rem; position: relative; overflow: hidden; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);" onmouseover="this.style.borderColor='rgba(56, 189, 248, 0.3)'; this.style.transform='translateY(-2px)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.06)'; this.style.transform='translateY(0)'">
                    
                    {{-- Decorative item number --}}
                    <div style="position: absolute; right: 2rem; top: 0.5rem; font-size: 5rem; font-weight: 900; color: rgba(255,255,255,0.02); line-height: 1; user-select: none;">
                        #{{ $index + 1 }}
                    </div>

                    <div style="display: flex; gap: 2rem; align-items: flex-start; flex-wrap: wrap;">
                        {{-- Quantity Badge --}}
                        <div style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); width: 75px; height: 75px; border-radius: 1rem; display: flex; flex-direction: column; justify-content: center; align-items: center; color: #fff; box-shadow: 0 4px 20px rgba(2, 132, 199, 0.3); border: 1px solid rgba(255,255,255,0.15);">
                            <span style="font-size: 2rem; font-weight: 800; line-height: 1.1;">{{ $pick['qty_keluar'] }}</span>
                            <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase;">{{ $pick['uom'] ?? 'Unit' }}</span>
                        </div>

                        {{-- Product Details --}}
                        <div style="flex: 1; min-width: 250px;">
                            <div style="font-size: 0.85rem; color: #38bdf8; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.35rem; display: flex; align-items: center; gap: 0.5rem;">
                                <span>PRODUK YANG HARUS DIAMBIL</span>
                            </div>
                            <h2 style="font-size: 1.45rem; font-weight: 800; color: #f8fafc; margin: 0 0 1.25rem 0; letter-spacing: -0.01em;">{{ $pick['produk_nama'] }}</h2>

                            <div style="display: flex; gap: 1.25rem; flex-wrap: wrap; align-items: center;">
                                {{-- Rak Location --}}
                                <div style="display: flex; align-items: center; gap: 0.65rem; background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.25); padding: 0.45rem 0.85rem; border-radius: 0.5rem;">
                                    <div>
                                        <span style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; display: block; line-height: 1; margin-bottom: 0.15rem;">LOKASI RAK</span>
                                        <strong style="color: #10b981; font-size: 1rem; font-family: monospace;">RAK {{ $pick['rak_id'] }}</strong>
                                    </div>
                                </div>

                                {{-- Batch Number --}}
                                <div style="display: flex; align-items: center; gap: 0.65rem; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); padding: 0.45rem 0.85rem; border-radius: 0.5rem;">
                                    <div>
                                        <span style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; display: block; line-height: 1; margin-bottom: 0.15rem;">NOMOR BATCH</span>
                                        <strong style="color: #cbd5e1; font-size: 1rem; font-family: monospace;">{{ $pick['batch_number'] }}</strong>
                                    </div>
                                </div>

                                {{-- Expired Date --}}
                                @php
                                    $expCarbon = \Carbon\Carbon::parse($pick['expired_date']);
                                    $daysLeft  = now()->diffInDays($expCarbon, false);
                                    $expColor = $daysLeft < 14 ? '#ef4444' : ($daysLeft < 30 ? '#f59e0b' : '#34d399');
                                    $expBg = $daysLeft < 14 ? 'rgba(239, 68, 68, 0.1)' : ($daysLeft < 30 ? 'rgba(245, 158, 11, 0.1)' : 'rgba(52, 211, 153, 0.1)');
                                    $expBorder = $daysLeft < 14 ? 'rgba(239, 68, 68, 0.25)' : ($daysLeft < 30 ? 'rgba(245, 158, 11, 0.25)' : 'rgba(52, 211, 153, 0.25)');
                                @endphp
                                <div style="display: flex; align-items: center; gap: 0.65rem; background: {{ $expBg }}; border: 1px solid {{ $expBorder }}; padding: 0.45rem 0.85rem; border-radius: 0.5rem;">
                                    <div>
                                        <span style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; display: block; line-height: 1; margin-bottom: 0.15rem;">TANGGAL EXPIRED</span>
                                        <strong style="color: {{ $expColor }}; font-size: 1rem;">
                                            {{ $expCarbon->format('d M Y') }}
                                            @if($daysLeft >= 0)
                                                <small style="font-weight: 500; font-size: 0.75rem; color: var(--text-muted); margin-left: 0.25rem;">({{ $daysLeft }} hari lagi)</small>
                                            @else
                                                <small style="font-weight: 700; font-size: 0.75rem; color: #ef4444; margin-left: 0.25rem;">[KADALUWARSA]</small>
                                            @endif
                                        </strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Hidden input containing the expected batch number to preserve back-end validation compatibility --}}
                    <input type="hidden" name="batch_scanned[{{ $index }}]" value="{{ $pick['batch_number'] }}">
                </div>
            @endforeach
        </div>

        {{-- Upload Bukti Foto --}}
        <div class="glass-card" style="margin-bottom: 2.5rem; padding: 2rem; background: rgba(30, 41, 59, 0.45); border: 1px solid rgba(255,255,255,0.06); border-radius: 1.25rem; transition: all 0.3s ease;">
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                <label class="form-label" for="bukti_foto" style="margin-bottom: 0; font-weight: 700; font-size: 1.1rem; color: #f8fafc;">
                    Unggah Bukti Foto Pengambilan *
                </label>
            </div>
            <p style="font-size: 0.88rem; color: var(--text-muted); margin-top: 0; margin-bottom: 1.5rem; line-height: 1.5;">Format gambar (JPG, PNG, JPEG) maksimal 2MB. Wajib melampirkan foto fisik barang yang telah berhasil diambil sebagai bukti audit gudang.</p>
            
            <div style="position: relative; background: var(--bg-dark); border: 2px dashed rgba(255,255,255,0.1); border-radius: 0.75rem; padding: 2rem; text-align: center; cursor: pointer; transition: all 0.3s ease;" onmouseover="this.style.borderColor='rgba(56, 189, 248, 0.4)';" onmouseout="this.style.borderColor='rgba(255,255,255,0.1)';">
                <input type="file" name="bukti_foto" id="bukti_foto" class="form-control" required accept="image/*" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer;">
                <div id="uploadPlaceholder">
                    <span style="color: #38bdf8; font-weight: 600; font-size: 0.95rem;">Klik di sini untuk memilih foto</span>
                    <span style="display: block; font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">atau seret file gambar langsung ke area ini</span>
                </div>
                <div id="fileInfo" style="display: none; color: #34d399; font-weight: 600; font-size: 0.95rem;">
                    <span id="fileName">File terpilih</span>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div style="margin-top: 2.5rem; display: flex; gap: 1.25rem; flex-wrap: wrap;">
            <button type="submit" class="btn btn-success" style="flex: 2; min-width: 250px; font-size: 1.15rem; font-weight: 800; padding: 1.1rem; border-radius: 0.85rem; letter-spacing: 0.03em; box-shadow: 0 8px 25px rgba(16,185,129,0.3); transition: all 0.3s ease; display: flex; align-items: center; justify-content: center; gap: 0.5rem;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 12px 30px rgba(16,185,129,0.45)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 8px 25px rgba(16,185,129,0.3)';">
                Konfirmasi Selesai Diambil (1-Click Confirm)
            </button>
            <a href="{{ route('outbound.index') }}" class="btn btn-secondary" style="flex: 1; min-width: 150px; display: flex; align-items: center; justify-content: center; font-weight: 600; padding: 1.1rem; border-radius: 0.85rem; transition: all 0.3s ease;" onmouseover="this.style.background='rgba(255,255,255,0.08)';" onmouseout="this.style.background='rgba(255,255,255,0.03)';" onclick="return confirm('Batalkan proses ini?')">
                Batalkan
            </a>
        </div>
    </form>

    <script>
        document.getElementById('bukti_foto').addEventListener('change', function(e) {
            const fileName = e.target.files[0] ? e.target.files[0].name : '';
            const placeholder = document.getElementById('uploadPlaceholder');
            const fileInfo = document.getElementById('fileInfo');
            const nameSpan = document.getElementById('fileName');
            if (fileName) {
                placeholder.style.display = 'none';
                fileInfo.style.display = 'block';
                nameSpan.textContent = `Foto terpilih: ${fileName}`;
            } else {
                placeholder.style.display = 'block';
                fileInfo.style.display = 'none';
            }
        });
    </script>
</x-app-layout>
