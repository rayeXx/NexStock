<x-app-layout>
    <div style="margin-bottom: 2rem;">
        <a href="{{ route('supplier.index') }}" style="color: var(--accent-blue); text-decoration: none; font-weight: 500; font-size: 0.9rem;">
            &larr; Kembali ke Daftar Supplier
        </a>
        <h1 style="margin-top: 0.5rem;">Tambah Supplier Baru</h1>
        <p>Hubungkan mitra pemasok baru ke dalam database logistik pengadaan.</p>
    </div>

    <div class="glass-card" style="max-width: 500px;">
        <form action="{{ route('supplier.store') }}" method="POST">
            @csrf
            
            <div class="form-group">
                <label class="form-label" for="nama_supplier">Nama Perusahaan Supplier *</label>
                <input type="text" name="nama_supplier" id="nama_supplier" class="form-control" placeholder="Contoh: PT Pangan Makmur Abadi" value="{{ old('nama_supplier') }}" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="kontak">Kontak Telepon / PIC *</label>
                <input type="text" name="kontak" id="kontak" class="form-control" placeholder="Contoh: 081234567890 (Bpk. Agus)" value="{{ old('kontak') }}" required>
            </div>

            <div style="margin-top: 2rem; display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">
                    Simpan Supplier
                </button>
                <a href="{{ route('supplier.index') }}" class="btn btn-secondary" style="flex: 1;">
                    Batal
                </a>
            </div>
        </form>
    </div>
</x-app-layout>
