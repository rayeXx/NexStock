<x-app-layout>
    <div style="margin-bottom: 2rem;">
        <a href="{{ route('rack.index') }}" style="color: var(--accent-blue); text-decoration: none; font-weight: 500; font-size: 0.9rem;">
            &larr; Kembali ke Daftar Rak
        </a>
        <h1 style="margin-top: 0.5rem;">Edit Kapasitas Rak</h1>
        <p>Sesuaikan nilai batas volume tampung maksimum rak pergudangan.</p>
    </div>

    <div class="glass-card" style="max-width: 500px;">
        <form action="{{ route('rack.update', $rack->kode_rak) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="form-group">
                <label class="form-label" for="kode_rak">Kode Rak</label>
                <input type="text" id="kode_rak" class="form-control" value="Rak {{ $rack->kode_rak }}" disabled style="opacity: 0.6; cursor: not-allowed;">
            </div>

            <div class="form-group">
                <label class="form-label" for="kapasitas_maksimum_volume">Kapasitas Maksimum Volume (Unit) *</label>
                <input type="number" name="kapasitas_maksimum_volume" id="kapasitas_maksimum_volume" class="form-control" min="{{ $rack->kapasitas_terpakai }}" value="{{ old('kapasitas_maksimum_volume', $rack->kapasitas_maksimum_volume) }}" required>
                <p style="font-size: 0.75rem; margin-top: 0.25rem; color: var(--accent-yellow);">Batas minimum kapasitas tidak boleh lebih kecil dari kapasitas terpakai saat ini ({{ $rack->kapasitas_terpakai }} unit).</p>
            </div>

            <div style="margin-top: 2rem; display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">
                    Perbarui Kapasitas
                </button>
                <a href="{{ route('rack.index') }}" class="btn btn-secondary" style="flex: 1;">
                    Batal
                </a>
            </div>
        </form>
    </div>
</x-app-layout>
