<x-app-layout>
    <div style="margin-bottom: 2rem;">
        <a href="{{ route('user.index') }}" style="color: var(--accent-blue); text-decoration: none; font-weight: 500; font-size: 0.9rem;">
            &larr; Kembali ke Daftar User
        </a>
        <h1 style="margin-top: 0.5rem;">Daftarkan User Baru</h1>
        <p>Buat akun operator baru dengan pembagian wewenang hak akses (RBAC) spesifik.</p>
    </div>

    <div class="glass-card" style="max-width: 500px;">
        <form action="{{ route('user.store') }}" method="POST">
            @csrf
            
            <div class="form-group">
                <label class="form-label" for="name">Nama Lengkap Operator *</label>
                <input type="text" name="name" id="name" class="form-control" placeholder="Contoh: Muhammad Yusuf" value="{{ old('name') }}" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="email">Email Kredensial *</label>
                <input type="email" name="email" id="email" class="form-control" placeholder="Contoh: yusuf@nexstock.com" value="{{ old('email') }}" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="role">Peran Otoritas (Role) *</label>
                <select name="role" id="role" class="form-control" required>
                    <option value="" disabled selected>Pilih Role</option>
                    <option value="staff_gudang" {{ old('role') == 'staff_gudang' ? 'selected' : '' }}>Staff Gudang (Operasional Lapangan)</option>
                    <option value="admin_gudang" {{ old('role') == 'admin_gudang' ? 'selected' : '' }}>Admin Gudang (Administrasi Data)</option>
                    <option value="owner" {{ old('role') == 'owner' ? 'selected' : '' }}>Owner (Manajerial & Pengambil Keputusan)</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Kata Sandi (Min 8 Karakter) *</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="password_confirmation">Ulangi Kata Sandi *</label>
                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
            </div>

            <div style="margin-top: 2rem; display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">
                    Simpan User
                </button>
                <a href="{{ route('user.index') }}" class="btn btn-secondary" style="flex: 1;">
                    Batal
                </a>
            </div>
        </form>
    </div>
</x-app-layout>
