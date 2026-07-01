<x-app-layout>
    <div style="margin-bottom: 2rem;">
        <a href="{{ route('user.index') }}" style="color: var(--accent-blue); text-decoration: none; font-weight: 500; font-size: 0.9rem;">
            &larr; Kembali ke Daftar User
        </a>
        <h1 style="margin-top: 0.5rem;">Edit User / Operator</h1>
        <p>Perbarui profil nama, email, dan level otorisasi akses operator.</p>
    </div>

    <div class="glass-card" style="max-width: 500px;">
        <form action="{{ route('user.update', $user->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="form-group">
                <label class="form-label" for="name">Nama Lengkap Operator *</label>
                <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $user->name) }}" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="email">Email Kredensial *</label>
                <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $user->email) }}" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="role">Peran Otoritas (Role) *</label>
                <select name="role" id="role" class="form-control" required>
                    <option value="staff_gudang" {{ old('role', $user->role) == 'staff_gudang' ? 'selected' : '' }}>Staff Gudang (Operasional Lapangan)</option>
                    <option value="admin_gudang" {{ old('role', $user->role) == 'admin_gudang' ? 'selected' : '' }}>Admin Gudang (Administrasi Data)</option>
                    <option value="owner" {{ old('role', $user->role) == 'owner' ? 'selected' : '' }}>Owner (Manajerial & Pengambil Keputusan)</option>
                </select>
            </div>

            <div style="border-top: 1px solid var(--border-color); margin-top: 1.5rem; padding-top: 1.5rem;">
                <h3 style="font-size: 1rem; margin-bottom: 0.5rem; color: var(--accent-yellow);">Ubah Kata Sandi (Opsional)</h3>
                <p style="font-size: 0.8rem; margin-bottom: 1rem;">Biarkan kosong jika Anda tidak ingin mengubah kata sandi user.</p>

                <div class="form-group">
                    <label class="form-label" for="password">Kata Sandi Baru (Min 8 Karakter)</label>
                    <input type="password" name="password" id="password" class="form-control">
                </div>

                <div class="form-group">
                    <label class="form-label" for="password_confirmation">Ulangi Kata Sandi Baru</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
                </div>
            </div>

            <div style="margin-top: 2rem; display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">
                    Perbarui User
                </button>
                <a href="{{ route('user.index') }}" class="btn btn-secondary" style="flex: 1;">
                    Batal
                </a>
            </div>
        </form>
    </div>
</x-app-layout>
