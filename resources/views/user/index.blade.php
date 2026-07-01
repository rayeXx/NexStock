<x-app-layout>
    <div class="flex-between" style="margin-bottom: 2rem;">
        <div>
            <h1>Manajemen User & Operator</h1>
            <p>Kelola data akun operator lapangan, admin logistik, dan hak akses sistem (RBAC).</p>
        </div>
        <a href="{{ route('user.create') }}" class="btn btn-primary">
            + Daftarkan User Baru
        </a>
    </div>

    <div class="glass-card" style="max-width: 800px;">
        <div class="table-responsive">
            <table class="table-premium">
                <thead>
                    <tr>
                        <th>Nama Lengkap</th>
                        <th>Email Kredensial</th>
                        <th>Peran (Role)</th>
                        <th>Terdaftar Sejak</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td><strong>{{ $user->name }}</strong></td>
                            <td><code>{{ $user->email }}</code></td>
                            <td>
                                @if($user->role === 'owner')
                                    <span class="badge badge-red">Owner</span>
                                @elseif($user->role === 'admin_gudang')
                                    <span class="badge badge-blue">Admin Gudang</span>
                                @else
                                    <span class="badge badge-green">Staff Gudang</span>
                                @endif
                            </td>
                            <td>{{ $user->created_at->format('d M Y') }}</td>
                            <td>
                                <div style="display: flex; gap: 0.5rem;">
                                    <a href="{{ route('user.edit', $user->id) }}" class="btn btn-secondary" style="padding: 6px 12px; min-height:36px; min-width:36px; font-size: 0.85rem;">
                                        Edit
                                    </a>
                                    @if($user->id !== auth()->id())
                                        <form action="{{ route('user.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus user ini?');" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger" style="padding: 6px 12px; min-height:36px; min-width:36px; font-size: 0.85rem;">
                                                Hapus
                                            </button>
                                        </form>
                                    @else
                                        <span style="font-size:0.8rem; color:var(--text-muted); font-style:italic;">Akun Aktif</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
