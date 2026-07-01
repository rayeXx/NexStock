<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('category')->get();
        return view('product.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('product.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_produk' => 'required|string|unique:m_products,kode_produk',
            'nama_produk' => 'required|string|max:255',
            'kategori_id' => 'required|exists:m_categories,id',
            'harga_beli' => 'required|numeric|min:0',
            'stok_minimum' => 'required|integer|min:0',
            'uom' => 'required|in:Pcs,Dus,Pack',
        ]);

        Product::create($request->all());

        return redirect()->route('product.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit($kode_produk)
    {
        $product = Product::findOrFail($kode_produk);
        $categories = Category::all();
        return view('product.edit', compact('product', 'categories'));
    }

    public function update(Request $request, $kode_produk)
    {
        $product = Product::findOrFail($kode_produk);

        $request->validate([
            'nama_produk' => 'required|string|max:255',
            'kategori_id' => 'required|exists:m_categories,id',
            'harga_beli' => 'required|numeric|min:0',
            'stok_minimum' => 'required|integer|min:0',
            'uom' => 'required|in:Pcs,Dus,Pack',
        ]);

        $product->update($request->all());

        return redirect()->route('product.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy($kode_produk)
    {
        $product = Product::findOrFail($kode_produk);
        $product->delete();

        return redirect()->route('product.index')->with('success', 'Produk berhasil dihapus.');
    }
}
