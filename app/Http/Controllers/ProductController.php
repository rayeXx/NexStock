<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category');

        // Search filter
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_produk', 'like', "%{$search}%")
                  ->orWhere('kode_produk', 'like', "%{$search}%")
                  ->orWhereHas('category', function ($c) use ($search) {
                      $c->where('nama_kategori', 'like', "%{$search}%");
                  });
            });
        }

        $products = $query->get();

        // Status filter (applied in PHP because total_stok is an accessor)
        if ($status = $request->input('status')) {
            $products = $products->filter(function ($product) use ($status) {
                return strtolower($product->stock_status) === strtolower($status);
            })->values();
        }

        if ($request->ajax()) {
            return response()->json(['html' => view('product._table_body', compact('products'))->render()]);
        }

        return view('product.index', compact('products'));
    }

    public function create()
    {
        abort_if(auth()->user()->role === 'owner', 403, 'Akses Ditolak: Owner hanya dapat melihat data Master Produk.');
        $categories = Category::all();
        return view('product.create', compact('categories'));
    }

    public function store(Request $request)
    {
        abort_if(auth()->user()->role === 'owner', 403, 'Akses Ditolak: Owner hanya dapat melihat data Master Produk.');
        $rules = [
            'kode_produk' => 'required|string|unique:m_products,kode_produk',
            'nama_produk' => 'required|string|max:255',
            'harga_beli' => 'required|numeric|min:0',
            'stok_minimum' => 'required|integer|min:0',
            'uom' => 'required|in:Pcs,Dus,Pack',
        ];

        if ($request->input('kategori_id') === 'lainnya') {
            $rules['nama_kategori_baru'] = 'required|string|max:255';
            $rules['catatan'] = 'nullable|string|max:500';
        } else {
            $rules['kategori_id'] = 'required|exists:m_categories,id';
        }

        $request->validate($rules);

        $kategoriId = $request->input('kategori_id');

        if ($kategoriId === 'lainnya') {
            $newCategory = Category::create([
                'nama_kategori' => $request->input('nama_kategori_baru'),
                'catatan' => $request->input('catatan'),
            ]);
            $kategoriId = $newCategory->id;
        }

        Product::create([
            'kode_produk' => $request->input('kode_produk'),
            'nama_produk' => $request->input('nama_produk'),
            'kategori_id' => $kategoriId,
            'harga_beli' => $request->input('harga_beli'),
            'stok_minimum' => $request->input('stok_minimum'),
            'uom' => $request->input('uom'),
        ]);

        return redirect()->route('product.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit($kode_produk)
    {
        abort_if(auth()->user()->role === 'owner', 403, 'Akses Ditolak: Owner hanya dapat melihat data Master Produk.');
        $product = Product::findOrFail($kode_produk);
        $categories = Category::all();
        return view('product.edit', compact('product', 'categories'));
    }

    public function update(Request $request, $kode_produk)
    {
        abort_if(auth()->user()->role === 'owner', 403, 'Akses Ditolak: Owner hanya dapat melihat data Master Produk.');
        $product = Product::findOrFail($kode_produk);

        $rules = [
            'nama_produk' => 'required|string|max:255',
            'harga_beli' => 'required|numeric|min:0',
            'stok_minimum' => 'required|integer|min:0',
            'uom' => 'required|in:Pcs,Dus,Pack',
        ];

        if ($request->input('kategori_id') === 'lainnya') {
            $rules['nama_kategori_baru'] = 'required|string|max:255';
            $rules['catatan'] = 'nullable|string|max:500';
        } else {
            $rules['kategori_id'] = 'required|exists:m_categories,id';
        }

        $request->validate($rules);

        $kategoriId = $request->input('kategori_id');

        if ($kategoriId === 'lainnya') {
            $newCategory = Category::create([
                'nama_kategori' => $request->input('nama_kategori_baru'),
                'catatan' => $request->input('catatan'),
            ]);
            $kategoriId = $newCategory->id;
        }

        $product->update([
            'nama_produk' => $request->input('nama_produk'),
            'kategori_id' => $kategoriId,
            'harga_beli' => $request->input('harga_beli'),
            'stok_minimum' => $request->input('stok_minimum'),
            'uom' => $request->input('uom'),
        ]);

        return redirect()->route('product.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy($kode_produk)
    {
        abort_if(auth()->user()->role === 'owner', 403, 'Akses Ditolak: Owner hanya dapat melihat data Master Produk.');
        $product = Product::findOrFail($kode_produk);
        $product->delete();

        return redirect()->route('product.index')->with('success', 'Produk berhasil dihapus.');
    }
}
