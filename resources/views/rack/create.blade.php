<x-app-layout>
    <div style="margin-bottom: 2rem;">
        <a href="{{ route('rack.index') }}" style="color: var(--accent-blue); text-decoration: none; font-weight: 500; font-size: 0.9rem;">
            &larr; Kembali ke Daftar Rak
        </a>
        <h1 style="margin-top: 0.5rem;">Tambah Rak Baru</h1>
        <p>Definisikan kode lokasi lorong rak baru beserta kapasitas volume tampungnya.</p>
    </div>

    <div class="glass-card" style="max-width: 500px;">
        <form action="{{ route('rack.store') }}" method="POST">
            @csrf
            
            <div class="form-group">
                <label class="form-label" for="kode_rak">Kode Rak *</label>
                <input type="text" name="kode_rak" id="kode_rak" class="form-control" placeholder="Contoh: A3, B4" value="{{ old('kode_rak') }}" required style="text-transform: uppercase;">
                <p style="font-size: 0.75rem; margin-top: 0.25rem;">Gunakan kombinasi huruf dan angka penunjuk lorong.</p>
            </div>

            <div class="form-group">
                <label class="form-label" for="kapasitas_maksimum_volume">Kapasitas Maksimum Volume (Unit) *</label>
                <input type="number" name="kapasitas_maksimum_volume" id="kapasitas_maksimum_volume" class="form-control" min="1" placeholder="Contoh: 1000" value="{{ old('kapasitas_maksimum_volume') }}" required>
            </div>

            <div style="margin-top: 2rem; display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">
                    Simpan Rak
                </button>
                <a href="{{ route('rack.index') }}" class="btn btn-secondary" style="flex: 1;">
                    Batal
                </a>
            </div>
        </form>
    </div>
</x-app-layout>
