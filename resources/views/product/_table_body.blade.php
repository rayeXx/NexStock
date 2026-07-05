@forelse($products as $product)
    <tr data-status="{{ strtolower($product->stock_status) }}" data-nama="{{ strtolower($product->nama_produk) }}" data-sku="{{ strtolower($product->kode_produk) }}" data-kategori="{{ strtolower($product->category->nama_kategori) }}">
        <td><code>{{ $product->kode_produk }}</code></td>
        <td><strong>{{ $product->nama_produk }}</strong></td>
        <td>{{ $product->category->nama_kategori }}</td>
        <td>Rp {{ number_format((double)$product->harga_beli, 0, ',', '.') }}</td>
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
        @if(auth()->user()->role !== 'owner')
        <td>
            <div style="display: flex; gap: 0.5rem;">
                <a href="{{ route('product.edit', $product->kode_produk) }}" class="btn btn-secondary" style="padding: 6px 12px; min-height:36px; min-width:36px; font-size: 0.85rem;">
                    Edit
                </a>
                <form action="{{ route('product.destroy', $product->kode_produk) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk ini?');" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" style="padding: 6px 12px; min-height:36px; min-width:36px; font-size: 0.85rem;">
                        Hapus
                    </button>
                </form>
            </div>
        </td>
        @endif
    </tr>
@empty
    <tr>
        <td colspan="{{ auth()->user()->role === 'owner' ? '8' : '9' }}" style="text-align: center; padding: 2rem; color: var(--text-muted);">Belum ada data produk. Klik "+ Tambah Produk Baru" untuk menambahkan.</td>
    </tr>
@endforelse
