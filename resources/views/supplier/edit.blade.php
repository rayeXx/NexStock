<x-app-layout>
    <div style="margin-bottom: 2rem;">
        <a href="{{ route('supplier.index') }}" style="color: var(--accent-blue); text-decoration: none; font-weight: 500; font-size: 0.9rem;">
            &larr; Kembali ke Daftar Supplier
        </a>
        <h1 style="margin-top: 0.5rem;">Edit Supplier</h1>
        <p>Perbarui informasi detail kontak dari mitra pemasok.</p>
    </div>

    <div class="glass-card">
        <form action="{{ route('supplier.update', $supplier->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="form-group">
                <label class="form-label" for="nama_supplier">Nama Perusahaan Supplier *</label>
                <input type="text" name="nama_supplier" id="nama_supplier" class="form-control" value="{{ old('nama_supplier', $supplier->nama_supplier) }}" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="kontak">Kontak Telepon / PIC *</label>
                <input type="text" name="kontak" id="kontak" class="form-control" value="{{ old('kontak', $supplier->kontak) }}" required>
            </div>

            <div style="margin-top: 2rem; display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">
                    Perbarui Supplier
                </button>
                <a href="{{ route('supplier.index') }}" class="btn btn-secondary" style="flex: 1;">
                    Batal
                </a>
            </div>
        </form>
    </div>
</x-app-layout>
