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

    {{-- Search Bar --}}
    <div class="glass-card" style="margin-bottom: 1.5rem; padding: 1rem 1.25rem;">
        <div style="position:relative; max-width:400px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); pointer-events:none;">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text" id="userSearch" class="form-control" placeholder="Cari nama operator, email, atau role..." style="padding-left:38px; min-height:40px; font-size:0.88rem;">
        </div>
    </div>

    <div class="glass-card">
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
                        <tr data-search="{{ strtolower($user->name . ' ' . $user->email . ' ' . $user->role) }}">
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                    <div style="width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, #38bdf8, #10b981); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.85rem; box-shadow: 0 4px 10px rgba(56, 189, 248, 0.15);">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </div>
                                    <strong>{{ $user->name }}</strong>
                                </div>
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.5rem; color: var(--text-muted);">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                        <polyline points="22,6 12,13 2,6"/>
                                    </svg>
                                    <code>{{ $user->email }}</code>
                                </div>
                            </td>
                            <td>
                                @if($user->role === 'owner')
                                    <span class="badge badge-red"><span class="badge-dot"></span>Owner</span>
                                @elseif($user->role === 'admin_gudang')
                                    <span class="badge badge-blue"><span class="badge-dot"></span>Admin Gudang</span>
                                @else
                                    <span class="badge badge-green"><span class="badge-dot"></span>Staff Gudang</span>
                                @endif
                            </td>
                            <td>{{ $user->created_at->format('d M Y') }}</td>
                            <td>
                                <div style="display: flex; gap: 0.5rem;">
                                    @if(auth()->user()->role === 'admin_gudang' && $user->role === 'owner')
                                        <span class="badge badge-blue" style="opacity: 0.7; font-weight: 500;">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 2px;">
                                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                            </svg>
                                            Dilindungi
                                        </span>
                                    @else
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
                                            <span class="badge badge-green" style="font-weight: 500;">Aktif</span>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('userSearch');
        const tbody = document.querySelector('.table-premium tbody');
        let timer;

        searchInput.addEventListener('input', function() {
            clearTimeout(timer);
            timer = setTimeout(() => {
                const val = this.value.toLowerCase().trim();
                const rows = tbody.querySelectorAll('tr[data-search]');
                let visibleCount = 0;

                rows.forEach(row => {
                    const data = row.getAttribute('data-search');
                    const match = !val || data.includes(val);
                    row.style.display = match ? '' : 'none';
                    if (match) visibleCount++;
                });

                let noResult = document.getElementById('noResultUser');
                if (visibleCount === 0 && rows.length > 0) {
                    if (!noResult) {
                        const tr = document.createElement('tr');
                        tr.id = 'noResultUser';
                        tr.innerHTML = '<td colspan="5" style="text-align:center; padding:2rem; color:var(--text-muted);">Tidak ada operator yang sesuai pencarian.</td>';
                        tbody.appendChild(tr);
                    }
                } else if (noResult) {
                    noResult.remove();
                }
            }, 200);
        });
    });
    </script>
</x-app-layout>
