<x-app-layout>
    <div class="flex-between" style="margin-bottom: 2rem;">
        <div>
            <h1>Master Data Produk</h1>
            <p>Kelola master produk, SKU pabrik, batas minimum stok, dan harga beli persediaan.</p>
        </div>
        <a href="{{ route('product.create') }}" class="btn btn-primary">
            + Tambah Produk Baru
        </a>
    </div>

    <div class="glass-card">
        <div class="table-responsive">
            <table class="table-premium">
                <thead>
                    <tr>
                        <th>Kode SKU</th>
                        <th>Nama Produk</th>
                        <th>Kategori</th>
                        <th>Harga Beli</th>
                        <th>Stok Minimum</th>
                        <th>UOM (Satuan)</th>
                        <th>Stok Saat Ini</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td><code>{{ $product->kode_produk }}</code></td>
                            <td><strong>{{ $product->nama_produk }}</strong></td>
                            <td>{{ $product->category->nama_kategori }}</td>
                            <td>Rp {{ number_format((double)$product->harga_beli, 0, ',', '.') }}</td>
                            <td>{{ $product->stok_minimum }}</td>
                            <td><span class="badge badge-blue">{{ $product->uom }}</span></td>
                            <td>
                                @if($product->total_stok <= $product->stok_minimum)
                                    <strong style="color: var(--accent-red);">{{ $product->total_stok }} (Kritis)</strong>
                                @else
                                    <strong style="color: var(--accent-green);">{{ $product->total_stok }}</strong>
                                @endif
                            </td>
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
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 2rem; color: var(--text-muted);">Belum ada data produk. Klik "+ Tambah Produk Baru" untuk menambahkan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
