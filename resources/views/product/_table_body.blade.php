@forelse($products as $product)
    <tr data-status="{{ strtolower($product->stock_status) }}" data-nama="{{ strtolower($product->nama_produk) }}" data-sku="{{ strtolower($product->kode_produk) }}" data-kategori="{{ strtolower($product->category->nama_kategori) }}">
        <td><code>{{ $product->kode_produk }}</code></td>
        <td><strong>{{ $product->nama_produk }}</strong></td>
        <td>{{ $product->category->nama_kategori }}</td>
        <td>Rp {{ number_format((double)$product->harga_beli, 0, ',', '.') }}</td>
        <td>Rp {{ number_format((double)$product->harga_jual, 0, ',', '.') }}</td>
        @if(auth()->user()->role === 'owner')
        <td>
            @php
                $keuntungan = (double)$product->harga_jual - (double)$product->harga_beli;
                $margin = (double)$product->harga_jual > 0 ? ($keuntungan / (double)$product->harga_jual) * 100 : 0;
            @endphp
            @if($keuntungan > 0)
                <span style="color: var(--accent-green); font-weight:600;">
                    Rp {{ number_format($keuntungan, 0, ',', '.') }} ({{ round($margin, 1) }}%)
                </span>
            @elseif($keuntungan < 0)
                <span style="color: var(--accent-red); font-weight:600;">
                    Rp {{ number_format($keuntungan, 0, ',', '.') }} ({{ round($margin, 1) }}%)
                </span>
            @else
                <span style="color: var(--text-muted);">
                    Rp 0 (0%)
                </span>
            @endif
        </td>
        @endif
        <td>{{ $product->stok_minimum }}</td>
        <td>
            @if(($product->satuan_beli ?? '') !== '')
                <span class="badge badge-blue" style="font-size:0.78rem;">
                    {{ $product->satuan_beli }} / {{ $product->satuan_jual }} (1:{{ $product->rasio_konversi }})
                </span>
            @else
                <span class="badge badge-blue">{{ $product->uom }}</span>
            @endif
        </td>
        <td>
            <strong style="color: var({{ $product->stock_status === 'Kritis' ? '--accent-red' : ($product->stock_status === 'Menipis' ? '--accent-yellow' : '--accent-green') }});">{{ $product->total_stok }}</strong>
        </td>
        <td>
            @if($product->stock_status === 'Kritis')
                <span class="badge badge-red">🔴 Kritis</span>
            @elseif($product->stock_status === 'Menipis')
                <span class="badge badge-yellow">🟡 Menipis</span>
            @else
                <span class="badge badge-green">🟢 Aman</span>
            @endif
        </td>
        <td>
            <div style="display: flex; gap: 0.5rem;">
                @if(auth()->user()->role === 'owner')
                    <a href="{{ route('product.edit', $product->kode_produk) }}" class="btn btn-primary" style="padding: 6px 12px; min-height:36px; font-size: 0.85rem; background: rgba(168,85,247,0.15); color: #a855f7; border: 1px solid rgba(168,85,247,0.3);">
                        Edit Harga
                    </a>
                @else
                    <a href="{{ route('product.edit', $product->kode_produk) }}" class="btn btn-secondary" style="padding: 6px 12px; min-height:36px; font-size: 0.85rem;">
                        Edit
                    </a>
                    <form action="{{ route('product.destroy', $product->kode_produk) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk ini?');" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" style="padding: 6px 12px; min-height:36px; font-size: 0.85rem;">
                            Hapus
                        </button>
                    </form>
                @endif
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="{{ auth()->user()->role === 'owner' ? '11' : '10' }}" style="text-align: center; padding: 2rem; color: var(--text-muted);">Belum ada data produk. Klik "+ Tambah Produk Baru" untuk menambahkan.</td>
    </tr>
@endforelse
