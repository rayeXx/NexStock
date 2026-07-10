<x-app-layout>
    <div style="margin-bottom: 2rem;">
        <a href="{{ route('product.index') }}" style="color: var(--accent-blue); text-decoration: none; font-weight: 500; font-size: 0.9rem;">
            &larr; Kembali ke Master Produk
        </a>
        <h1 style="margin-top: 0.5rem;">Detail Produk: {{ $product->nama_produk }}</h1>
        <p>Rincian spesifikasi produk, status persediaan, dan sebaran stok di rak penyimpanan.</p>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1.5fr; gap: 1.5rem; align-items: start;">
        
        <!-- Left Side: Product Specifications -->
        <div class="glass-card">
            <h3 style="margin-bottom: 1.25rem; font-weight: 700; color: #cbd5e1;">Spesifikasi Produk</h3>
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <div>
                    <span style="font-size: 0.75rem; color: var(--text-muted); display: block; text-transform: uppercase;">Kode SKU</span>
                    <strong style="font-size: 1.1rem; color: #f1f5f9;">{{ $product->kode_produk }}</strong>
                </div>
                <div>
                    <span style="font-size: 0.75rem; color: var(--text-muted); display: block; text-transform: uppercase;">Nama Produk</span>
                    <strong style="font-size: 1.1rem; color: #f1f5f9;">{{ $product->nama_produk }}</strong>
                </div>
                <div>
                    <span style="font-size: 0.75rem; color: var(--text-muted); display: block; text-transform: uppercase;">Kategori</span>
                    <span class="badge badge-blue">{{ $product->category->nama_kategori }}</span>
                </div>
                <div>
                    <span style="font-size: 0.75rem; color: var(--text-muted); display: block; text-transform: uppercase;">Unit of Measure (UoM)</span>
                    <span class="badge badge-gray">{{ $product->uom }}</span>
                </div>
                <div>
                    <span style="font-size: 0.75rem; color: var(--text-muted); display: block; text-transform: uppercase;">Stok Pengaman (Min)</span>
                    <strong style="color: #cbd5e1;">{{ $product->stok_minimum }} {{ $product->uom }}</strong>
                </div>
                <div>
                    <span style="font-size: 0.75rem; color: var(--text-muted); display: block; text-transform: uppercase;">Status Stok</span>
                    @if($product->stock_status === 'Aman')
                        <span class="badge badge-green">Stok Aman ({{ $product->total_stok }} Unit)</span>
                    @elseif($product->stock_status === 'Menipis')
                        <span class="badge badge-yellow">Stok Menipis ({{ $product->total_stok }} Unit)</span>
                    @else
                        <span class="badge badge-red">Stok Kritis ({{ $product->total_stok }} Unit)</span>
                    @endif
                </div>
                <div>
                    <span style="font-size: 0.75rem; color: var(--text-muted); display: block; text-transform: uppercase;">Harga Beli</span>
                    <strong style="color: #cbd5e1;">Rp {{ number_format((double)$product->harga_beli, 0, ',', '.') }}</strong>
                </div>
                <div>
                    <span style="font-size: 0.75rem; color: var(--text-muted); display: block; text-transform: uppercase;">Harga Jual</span>
                    <strong style="color: #38bdf8;">Rp {{ number_format((double)$product->harga_jual, 0, ',', '.') }}</strong>
                </div>
                @if(auth()->user()->role === 'owner')
                <div>
                    <span style="font-size: 0.75rem; color: var(--text-muted); display: block; text-transform: uppercase;">Estimasi Keuntungan</span>
                    @php
                        $keuntungan = (double)$product->harga_jual - (double)$product->harga_beli;
                        $margin = (double)$product->harga_jual > 0 ? ($keuntungan / (double)$product->harga_jual) * 100 : 0;
                    @endphp
                    @if($keuntungan > 0)
                        <strong style="color: var(--accent-green);">
                            Rp {{ number_format($keuntungan, 0, ',', '.') }} ({{ round($margin, 1) }}% Margin)
                        </strong>
                    @elseif($keuntungan < 0)
                        <strong style="color: var(--accent-red);">
                            Rp {{ number_format($keuntungan, 0, ',', '.') }} ({{ round($margin, 1) }}% Margin)
                        </strong>
                    @else
                        <span style="color: var(--text-muted);">Rp 0 (0%)</span>
                    @endif
                </div>
                @endif
            </div>
        </div>

        <!-- Right Side: Active Batches in Warehouse -->
        <div class="glass-card">
            <h3 style="margin-bottom: 1.25rem; font-weight: 700; color: #cbd5e1;">Sebaran Stok Aktif per-Batch</h3>
            <div class="table-responsive">
                <table class="table-premium">
                    <thead>
                        <tr>
                            <th>Nomor Batch</th>
                            <th>Kode Rak</th>
                            <th>Tanggal Masuk</th>
                            <th>Tanggal Kadaluwarsa</th>
                            <th>Sisa Stok</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($product->batchInbounds->where('stok_sisa_batch', '>', 0) as $batch)
                            <tr>
                                <td><code>{{ $batch->batch_number }}</code></td>
                                <td><span class="badge badge-blue">Rak {{ $batch->rak_id }}</span></td>
                                <td>{{ $batch->created_at->format('d M Y') }}</td>
                                <td>
                                    @if($batch->expired_date)
                                        @if($batch->expired_date->isPast())
                                            <span style="color: var(--accent-red); font-weight: 700;">
                                                ⚠️ {{ $batch->expired_date->format('d M Y') }} (Expired)
                                            </span>
                                        @elseif($batch->expired_date->diffInDays(now()) < 30)
                                            <span style="color: var(--accent-yellow); font-weight: 700;">
                                                ⏳ {{ $batch->expired_date->format('d M Y') }} (Mepet)
                                            </span>
                                        @else
                                            <span style="color: var(--accent-green);">
                                                {{ $batch->expired_date->format('d M Y') }}
                                            </span>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                                <td><strong>{{ $batch->stok_sisa_batch }}</strong></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 2rem; color: var(--text-muted);">Tidak ada stok aktif untuk produk ini di gudang.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-app-layout>
