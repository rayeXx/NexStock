<x-app-layout>
    <div class="flex-between" style="margin-bottom: 2rem;">
        <div>
            <h1>Konfirmasi Pemusnahan Barang</h1>
            <p>Upload foto bukti pemusnahan barang karantina secara fisik.</p>
        </div>
        <a href="{{ route('destruction.index') }}" class="btn btn-secondary">Kembali</a>
    </div>

    {{-- Error Alerts --}}
    @if ($errors->any())
        <div class="glass-card" style="border-left: 4px solid var(--accent-red); margin-bottom: 1.5rem; padding: 1rem 1.5rem;">
            <strong style="color: var(--accent-red);">Terdapat Kesalahan:</strong>
            <ul style="margin: 0.5rem 0 0 1rem; color: var(--text-muted);">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; align-items: start;">

        {{-- Left Card: Task Details --}}
        <div class="glass-card">
            <h2 style="font-size: 1rem; font-weight: 700; color: var(--accent-red); margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:var(--accent-red);"></span>
                Informasi Pemusnahan
            </h2>

            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <div style="display: grid; grid-template-columns: 155px 1fr; gap: 0.5rem; align-items: center;">
                    <span style="color: var(--text-muted); font-size: 0.85rem;">ID Penugasan</span>
                    <span style="font-weight: 700; font-size: 1rem;">#{{ $destruction->id }}</span>
                </div>
                <div style="display: grid; grid-template-columns: 155px 1fr; gap: 0.5rem; align-items: center;">
                    <span style="color: var(--text-muted); font-size: 0.85rem;">Produk</span>
                    <span style="font-weight: 600;">{{ $destruction->product->nama_produk }}</span>
                </div>
                <div style="display: grid; grid-template-columns: 155px 1fr; gap: 0.5rem; align-items: center;">
                    <span style="color: var(--text-muted); font-size: 0.85rem;">Batch</span>
                    <code style="background: rgba(255,255,255,0.05); padding: 0.25rem 0.5rem; border-radius: 0.375rem; font-size: 0.85rem;">{{ $destruction->batch_number }}</code>
                </div>
                <div style="display: grid; grid-template-columns: 155px 1fr; gap: 0.5rem; align-items: center;">
                    <span style="color: var(--text-muted); font-size: 0.85rem;">Lokasi Karantina</span>
                    <span><span class="badge badge-blue">Rak {{ $destruction->rak_id }}</span></span>
                </div>
                <div style="display: grid; grid-template-columns: 155px 1fr; gap: 0.5rem; align-items: center;">
                    <span style="color: var(--text-muted); font-size: 0.85rem;">Kuantitas Musnah</span>
                    <span style="font-weight: 700; color: var(--accent-red); font-size: 1.1rem;">{{ $destruction->qty_dimusnahkan }} unit</span>
                </div>
                <div style="display: grid; grid-template-columns: 155px 1fr; gap: 0.5rem; align-items: center;">
                    <span style="color: var(--text-muted); font-size: 0.85rem;">Ditugaskan Oleh</span>
                    <span>{{ $destruction->assigner->name }}</span>
                </div>
                <div style="display: grid; grid-template-columns: 155px 1fr; gap: 0.5rem; align-items: center;">
                    <span style="color: var(--text-muted); font-size: 0.85rem;">Alasan Kerusakan</span>
                    <span style="color: var(--text-secondary);">{{ $destruction->alasan }}</span>
                </div>
            </div>

            @if($destruction->catatan_pemusnahan)
                <div style="margin-top: 1.5rem; padding: 1rem; background: rgba(245, 158, 11, 0.08); border: 1px solid rgba(245, 158, 11, 0.3); border-radius: 0.5rem;">
                    <p style="font-size: 0.8rem; font-weight: 600; color: #f59e0b; margin-bottom: 0.4rem; text-transform: uppercase; letter-spacing: 0.05em;">Instruksi Pemusnahan</p>
                    <p style="color: var(--text-secondary); font-size: 0.9rem; margin: 0; line-height: 1.5;">{{ $destruction->catatan_pemusnahan }}</p>
                </div>
            @endif
        </div>

        {{-- Right Card: Upload Form --}}
        <div class="glass-card">
            <h2 style="font-size: 1rem; font-weight: 700; color: var(--accent-blue); margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:var(--accent-blue);"></span>
                Unggah Bukti Fisik
            </h2>

            <form action="{{ route('destruction.confirm-post', $destruction->id) }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.75rem;">
                        Foto Bukti Pemusnahan <span style="color: var(--accent-red);">*</span>
                    </label>

                    <label id="uploadLabel" for="foto_pemusnahan" style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.75rem; padding: 2.5rem 1.5rem; border: 2px dashed rgba(255,255,255,0.15); border-radius: 0.75rem; cursor: pointer; transition: all 0.2s ease; background: rgba(255,255,255,0.02);" onmouseover="this.style.borderColor='rgba(99,102,241,0.5)'; this.style.background='rgba(99,102,241,0.05)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.15)'; this.style.background='rgba(255,255,255,0.02)'">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="color: var(--text-muted);">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                            <circle cx="8.5" cy="8.5" r="1.5"></circle>
                            <polyline points="21 15 16 10 5 21"></polyline>
                        </svg>
                        <div style="text-align: center;">
                            <p id="uploadText" style="font-weight: 600; color: var(--text-secondary); margin: 0;">Pilih file foto bukti</p>
                            <p style="font-size: 0.78rem; color: var(--text-muted); margin: 0.25rem 0 0;">JPG, PNG, WebP — maks. 5MB</p>
                        </div>
                    </label>
                    <input type="file" id="foto_pemusnahan" name="foto_pemusnahan" accept="image/jpeg,image/png,image/webp" style="display: none;" onchange="handleFileChange(this)">

                    {{-- Image Preview --}}
                    <div id="previewContainer" style="display: none; margin-top: 1rem;">
                        <img id="previewImg" src="" alt="Preview" style="width: 100%; max-height: 240px; object-fit: cover; border-radius: 0.5rem; border: 1px solid rgba(255,255,255,0.1);">
                        <button type="button" onclick="clearFile()" style="margin-top: 0.5rem; background: none; border: none; color: var(--accent-red); cursor: pointer; font-size: 0.8rem; padding: 0; text-decoration: underline;">Hapus foto</button>
                    </div>

                    @error('foto_pemusnahan')
                        <p style="color: var(--accent-red); font-size: 0.8rem; margin-top: 0.5rem;">{{ $message }}</p>
                    @enderror
                </div>

                <div style="padding: 1rem; background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.25); border-radius: 0.5rem; margin-bottom: 1.5rem;">
                    <p style="font-size: 0.82rem; color: #f87171; margin: 0; line-height: 1.6;">
                        <strong>Peringatan:</strong> Dengan menekan konfirmasi, Anda menyatakan bahwa barang tersebut telah hancur/dibuang secara permanen sesuai dengan instruksi yang diberikan.
                    </p>
                </div>

                <button type="submit" class="btn btn-danger" style="width: 100%;" onclick="return confirm('Apakah Anda yakin barang ini sudah dimusnahkan dan bukti foto valid?')">
                    Konfirmasi Pemusnahan Selesai
                </button>
            </form>
        </div>
    </div>

    <script>
        function handleFileChange(input) {
            const label = document.getElementById('uploadLabel');
            const previewContainer = document.getElementById('previewContainer');
            const previewImg = document.getElementById('previewImg');
            const uploadText = document.getElementById('uploadText');

            if (input.files && input.files[0]) {
                const file = input.files[0];
                uploadText.textContent = file.name;
                label.style.borderColor = 'rgba(99,102,241,0.5)';

                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    previewContainer.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        }

        function clearFile() {
            document.getElementById('foto_pemusnahan').value = '';
            document.getElementById('uploadText').textContent = 'Pilih file foto bukti';
            document.getElementById('previewContainer').style.display = 'none';
            document.getElementById('uploadLabel').style.borderColor = 'rgba(255,255,255,0.15)';
        }
    </script>
</x-app-layout>
