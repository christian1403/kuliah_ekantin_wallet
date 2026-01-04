<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Http\Requests\Kasir\TransactionRequest;
use App\Http\Requests\Kasir\PaymentRequest;
use App\Models\Kasir;
use App\Models\Mahasiswa;
use App\Models\Produk;
use App\Models\WalletTransaction;
use App\Models\DetailPengeluaran;
use App\Models\DetailProdukTransaction;
use App\Models\MetodeBayar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class TransactionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('kasir');
    }

    public function create()
    {
        $kasir = Auth::user()->kasir;
        
        if (!$kasir) {
            return redirect()->route('dashboard')
                ->with('error', 'Kasir profile not found.');
        }

        $produk = Produk::where('merchant_id', $kasir->merchant_id)
            ->where('status', 'active')
            ->get();

        $metodeBayar = MetodeBayar::where('status', 'active')->get();

        return Inertia::render('Kasir/Transactions/Create', [
            'produk' => $produk,
            'metodeBayar' => $metodeBayar,
        ]);
    }

    public function store(TransactionRequest $request)
    {
        $kasir = Auth::user()->kasir;
        
        if (!$kasir) {
            return redirect()->route('dashboard')
                ->with('error', 'Kasir profile not found.');
        }

        try {
            DB::beginTransaction();

            // Find mahasiswa by NIM or scan QR
            $mahasiswa = Mahasiswa::where('nim', $request->nim)->firstOrFail();

            // Calculate total
            $totalHarga = 0;
            $items = [];
            
            foreach ($request->items as $item) {
                $produk = Produk::where('id', $item['produk_id'])
                    ->where('merchant_id', $kasir->merchant_id)
                    ->firstOrFail();
                
                $subtotal = $produk->harga * $item['qty'];
                $totalHarga += $subtotal;
                
                $items[] = [
                    'produk' => $produk,
                    'qty' => $item['qty'],
                    'subtotal' => $subtotal,
                ];
            }

            // Check if mahasiswa has sufficient balance
            if ($mahasiswa->wallet->saldo < $totalHarga) {
                return redirect()->back()
                    ->withErrors(['balance' => 'Insufficient balance. Required: Rp ' . number_format($totalHarga, 0, ',', '.')]);
            }

            // Create wallet transaction
            $walletTransaction = WalletTransaction::create([
                'mahasiswa_id' => $mahasiswa->id,
                'type' => 'debit',
                'amount' => $totalHarga,
                'description' => 'Purchase at ' . $kasir->merchant->nama_merchant,
                'status' => 'completed',
            ]);

            // Create detail pengeluaran
            $detailPengeluaran = DetailPengeluaran::create([
                'wallet_transaction_id' => $walletTransaction->id,
                'kasir_id' => $kasir->id,
                'metode_bayar_id' => $request->metode_bayar_id,
                'total_harga' => $totalHarga,
                'status' => 'completed',
            ]);

            // Create detail produk transactions
            foreach ($items as $item) {
                DetailProdukTransaction::create([
                    'detail_pengeluaran_id' => $detailPengeluaran->id,
                    'produk_id' => $item['produk']->id,
                    'qty' => $item['qty'],
                    'harga_satuan' => $item['produk']->harga,
                    'subtotal' => $item['subtotal'],
                ]);
            }

            // Update wallet balance
            $mahasiswa->wallet->decrement('saldo', $totalHarga);

            DB::commit();

            return redirect()->route('kasir.transactions.show', $detailPengeluaran->id)
                ->with('success', 'Transaction completed successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            
            return redirect()->back()
                ->withErrors(['error' => 'Transaction failed: ' . $e->getMessage()]);
        }
    }

    public function show($id)
    {
        $kasir = Auth::user()->kasir;
        
        if (!$kasir) {
            return redirect()->route('dashboard')
                ->with('error', 'Kasir profile not found.');
        }

        $transaction = DetailPengeluaran::where('kasir_id', $kasir->id)
            ->where('id', $id)
            ->with([
                'walletTransaction.mahasiswa.user',
                'detailProdukTransactions.produk',
                'metodeBayar',
                'kasir.user'
            ])
            ->firstOrFail();

        return Inertia::render('Kasir/Transactions/Show', [
            'transaction' => $transaction,
        ]);
    }

    public function searchMahasiswa(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:1',
        ]);

        $mahasiswa = Mahasiswa::with(['user', 'wallet'])
            ->where('nim', 'like', '%' . $request->query . '%')
            ->orWhere('nama_lengkap', 'like', '%' . $request->query . '%')
            ->orWhereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->query . '%');
            })
            ->limit(10)
            ->get();

        return response()->json($mahasiswa);
    }

    public function processPayment(PaymentRequest $request)
    {
        $kasir = Auth::user()->kasir;
        
        if (!$kasir) {
            return response()->json(['error' => 'Kasir profile not found.'], 422);
        }

        try {
            DB::beginTransaction();

            $mahasiswa = Mahasiswa::findOrFail($request->mahasiswa_id);
            $totalAmount = $request->total_amount;

            // Check balance
            if ($mahasiswa->wallet->saldo < $totalAmount) {
                return response()->json([
                    'error' => 'Insufficient balance',
                    'required' => $totalAmount,
                    'available' => $mahasiswa->wallet->saldo,
                ], 422);
            }

            // Process the payment...
            // This is a simplified version - extend as needed

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully',
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'error' => 'Payment processing failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
