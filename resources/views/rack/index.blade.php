<x-app-layout>
    <div class="flex-between" style="margin-bottom: 2rem;">
        <div>
            <h1>Master Data Lokasi Rak</h1>
            <p>Kelola kapasitas volume rak pergudangan dinamis dan monitor sisa ruang kosong.</p>
        </div>
        @if(auth()->user()->role !== 'owner')
        <a href="{{ route('rack.create') }}" class="btn btn-primary">
            + Tambah Rak Baru
        </a>
        @endif
    </div>

    <div class="glass-card" style="max-width: 900px;">
        <div class="table-responsive">
            <table class="table-premium">
                <thead>
                    <tr>
                        <th>Kode Rak</th>
                        <th>Kapasitas Maksimum</th>
                        <th>Kapasitas Terpakai</th>
                        <th>Sisa Ruang Kosong</th>
                        <th>Tingkat Kepadatan Rak</th>
                        @if(auth()->user()->role !== 'owner')
                        <th>Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($racks as $rack)
                        @php
                            $percentage = round(($rack->kapasitas_terpakai / $rack->kapasitas_maksimum_volume) * 100);
                            $badgeClass = 'badge-green';
                            $barBg = 'var(--accent-green)';
                            if ($percentage >= 90) {
                                $badgeClass = 'badge-red';
                                $barBg = 'var(--accent-red)';
                            } elseif ($percentage >= 70) {
                                $badgeClass = 'badge-yellow';
                                $barBg = 'var(--accent-yellow)';
                            }
                        @endphp
                        <tr>
                            <td><strong>Rak {{ $rack->kode_rak }}</strong></td>
                            <td>{{ $rack->kapasitas_maksimum_volume }} Unit</td>
                            <td>{{ $rack->kapasitas_terpakai }} Unit</td>
                            <td>{{ $rack->sisa_kapasitas }} Unit</td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.75rem; min-width: 150px;">
                                    <div style="flex: 1; background: rgba(255, 255, 255, 0.05); height: 10px; border-radius: 5px; overflow: hidden;">
                                        <div style="background: {{ $barBg }}; width: {{ min(100, $percentage) }}%; height: 100%; transition: var(--transition);"></div>
                                    </div>
                                    <span class="badge {{ $badgeClass }}">{{ $percentage }}%</span>
                                </div>
                            </td>
                            @if(auth()->user()->role !== 'owner')
                            <td>
                                <div style="display: flex; gap: 0.5rem;">
                                    <a href="{{ route('rack.edit', $rack->kode_rak) }}" class="btn btn-secondary" style="padding: 6px 12px; min-height:36px; min-width:36px; font-size: 0.85rem;">
                                        Edit
                                    </a>
                                    <form action="{{ route('rack.destroy', $rack->kode_rak) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus rak ini?');" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger" {{ $rack->kapasitas_terpakai > 0 ? 'disabled style=opacity:0.4;cursor:not-allowed;' : '' }} style="padding: 6px 12px; min-height:36px; min-width:36px; font-size: 0.85rem;">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 2rem; color: var(--text-muted);">Belum ada data rak. Klik "+ Tambah Rak Baru" untuk mendefinisikan rak pertama.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
