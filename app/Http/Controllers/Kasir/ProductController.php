<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Http\Requests\Kasir\ProductRequest;
use App\Models\Kasir;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth');
        // $this->middleware('kasir');
    }

    public function index(Request $request)
    {
        $kasir = Auth::user()->kasir;
        
        if (!$kasir) {
            return redirect()->route('dashboard')
                ->with('error', 'Kasir profile not found.');
        }

        $query = Produk::where('merchant_id', $kasir->merchant_id);

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('kode_produk', 'like', "%{$search}%");
                //   ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        // Apply category filter
        if ($request->filled('kategori') && $request->get('kategori') !== 'all') {
            $query->where('kategori', $request->get('kategori'));
        }

        // Apply status filter
        if ($request->filled('status') && $request->get('status') !== 'all') {
            $query->where('status', $request->get('status'));
        }

        // Apply stock filter
        if ($request->filled('stock') && $request->get('stock') !== 'all') {
            $stockFilter = $request->get('stock');
            switch ($stockFilter) {
                case 'in_stock':
                    $query->where('stok', '>', 0);
                    break;
                case 'low_stock':
                    $query->where('stok', '>', 0)->where('stok', '<=', 10);
                    break;
                case 'out_of_stock':
                    $query->where('stok', '=', 0);
                    break;
            }
        }

        // Apply price sorting
        if ($request->filled('sort_price')) {
            $sortPrice = $request->get('sort_price');
            if ($sortPrice === 'low_to_high') {
                $query->orderBy('harga', 'asc');
            } elseif ($sortPrice === 'high_to_low') {
                $query->orderBy('harga', 'desc');
            }
        } else {
            $query->latest();
        }

        $produk = $query->paginate(20)->withQueryString();

        // Get categories for filter dropdown
        $categories = Produk::where('merchant_id', $kasir->merchant_id)
            ->distinct()
            ->pluck('kategori')
            ->filter()
            ->values();

        return Inertia::render('kasir/products/index', [
            'produk' => $produk,
            'merchant' => $kasir->merchant,
            'categories' => $categories,
            'filters' => $request->only(['search', 'kategori', 'status', 'stock', 'sort_price']),
        ]);
    }

    public function create()
    {
        $kasir = Auth::user()->kasir;
        
        if (!$kasir) {
            return redirect()->route('dashboard')
                ->with('error', 'Kasir profile not found.');
        }

        return Inertia::render('kasir/products/create', [
            'merchant' => $kasir->merchant,
        ]);
    }

    public function store(ProductRequest $request)
    {
        $kasir = Auth::user()->kasir;
        
        if (!$kasir) {
            return redirect()->route('dashboard')
                ->with('error', 'Kasir profile not found.');
        }

        $data = [
            'merchant_id' => $kasir->merchant_id,
            'nama' => $request->nama,
            'harga' => $request->harga,
            'kategori' => $request->kategori,
            'stok' => $request->stok ?? 0,
            'kode_produk' => $request->kode_produk,
            'gambar' => $request->gambar,
            // 'status' => $request->status ?? 'active',
            // 'deskripsi' => $request->deskripsi,
        ];

        // Handle image upload
        // if ($request->hasFile('image')) {
        //     $data['image'] = $request->file('image')->store('products', 'public');
        // }

        Produk::create($data);

        return redirect()->route('kasir.products.index')
            ->with('success', 'Product created successfully.');
    }

    public function show(Produk $produk)
    {
        $kasir = Auth::user()->kasir;
        
        if (!$kasir || $produk->merchant_id !== $kasir->merchant_id) {
            return redirect()->route('kasir.products.index')
                ->with('error', 'Product not found or access denied.');
        }

        $produk->load('merchant');

        return Inertia::render('Kasir/Products/Show', [
            'produk' => $produk,
        ]);
    }

    public function edit(Produk $product)
    {
        $kasir = Auth::user()->kasir;
        
        if (!$kasir) {
            return redirect()->route('kasir.products.index')
                ->with('error', 'Kasir profile not found.');
        }
        if (!$product || $product->merchant_id !== $kasir->merchant_id) {
            return redirect()->route('kasir.products.index')
                ->with('error', 'Product not found or access denied.');
        }

        return Inertia::render('kasir/products/edit', [
            'product' => $product,
            'merchant' => $kasir->merchant,
        ]);
    }

    public function update(ProductRequest $request, Produk $product)
    {
        $kasir = Auth::user()->kasir;
        
        if (!$kasir || $product->merchant_id !== $kasir->merchant_id) {
            return redirect()->route('kasir.products.index')
                ->with('error', 'Product not found or access denied.');
        }

        $data = [
            'nama' => $request->nama,
            // 'deskripsi' => $request->deskripsi,
            'harga' => (int) $request->harga,
            'kategori' => $request->kategori,
            'stok' => $request->stok ?? $product->stok,
            'gambar' => $request->gambar,
            // 'status' => $request->status ?? $product->status,
        ];

        // Handle image upload
        // if ($request->hasFile('image')) {
        //     // Delete old image
        //     if ($product->image) {
        //         Storage::disk('public')->delete($product->image);
        //     }
            
        //     $data['image'] = $request->file('image')->store('products', 'public');
        // }

        $product->update($data);

        return redirect()->route('kasir.products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Produk $produk)
    {
        $kasir = Auth::user()->kasir;
        
        if (!$kasir || $produk->merchant_id !== $kasir->merchant_id) {
            return redirect()->route('kasir.products.index')
                ->with('error', 'Product not found or access denied.');
        }

        // Delete image if exists
        if ($produk->image) {
            Storage::disk('public')->delete($produk->image);
        }

        $produk->delete();

        return redirect()->route('kasir.products.index')
            ->with('success', 'Product deleted successfully.');
    }

    public function toggleStatus(Produk $produk)
    {
        $kasir = Auth::user()->kasir;
        
        if (!$kasir || $produk->merchant_id !== $kasir->merchant_id) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $produk->update([
            'status' => $produk->status === 'active' ? 'inactive' : 'active'
        ]);

        return response()->json([
            'success' => true,
            'status' => $produk->status,
            'message' => 'Product status updated successfully.'
        ]);
    }

    public function search(Request $request)
    {
        $kasir = Auth::user()->kasir;
        
        if (!$kasir) {
            return response()->json(['error' => 'Kasir profile not found.'], 422);
        }

        $query = $request->get('q');
        
        $produk = Produk::where('merchant_id', $kasir->merchant_id)
            ->where('status', 'active')
            ->where(function ($q) use ($query) {
                $q->where('nama_produk', 'like', '%' . $query . '%')
                  ->orWhere('kategori', 'like', '%' . $query . '%');
            })
            ->limit(10)
            ->get();

        return response()->json($produk);
    }
}
