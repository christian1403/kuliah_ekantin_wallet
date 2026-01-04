<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mahasiswa\ProfileUpdateRequest;
use App\Models\Mahasiswa;
use App\Models\Kasir;
use App\Models\Produk;
use App\Models\DetailProdukTransaction;
use App\Models\DetailPengeluaran;
use App\Models\Merchant;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;

class MahasiswaController extends Controller
{
    public function __construct()
    {
        // Middleware is applied in routes/mahasiswa.php
    }

    public function dashboard()
    {
        $mahasiswa = Auth::user()->mahasiswa;
        
        if (!$mahasiswa) {
            return redirect()->route('dashboard')
                ->with('error', 'Student profile not found.');
        }

        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();

        // Get statistics
        $stats = [
            'current_balance' => $mahasiswa->wallet->balance ?? 0,
        ];
        $completeStatus = config('wallet.status_types.completed');
        $pendingStatus = config('wallet.status_types.pending');
        $recentTransactions = [];
        $dailySpending = [];
        $categorySpending = [];
        if($mahasiswa->wallet) {
            $stats += [
            'total_transactions_today' => WalletTransaction::where('wallet_id', $mahasiswa->wallet->wallet_id)
                ->whereDate('created_at', $today)
                ->whereIn('tipe_transaksi', ['credit', 'debit'])
                ->count(),
            'total_spent_today' => WalletTransaction::where('wallet_id', $mahasiswa->wallet->wallet_id)
                ->where('tipe_transaksi', 'debit')
                ->whereDate('created_at', $today)
                ->where('status_transaksi', $completeStatus)
                ->sum('amount'),
            'total_spent_month' => WalletTransaction::where('wallet_id', $mahasiswa->wallet->wallet_id)
                ->where('tipe_transaksi', 'debit')
                ->where('created_at', '>=', $thisMonth)
                ->where('status_transaksi', $completeStatus)
                ->sum('amount'),
            ];

            
            // Get recent transactions
            $recentTransactions = WalletTransaction::where('wallet_id', $mahasiswa->wallet->wallet_id)
                ->with(['detailPengeluaran.kasir.merchant', 'detailPemasukan', 'detailPengeluaran.merchant'])
                ->whereIn('tipe_transaksi', ['credit', 'debit'])
                ->whereIn('status_transaksi', [$completeStatus, $pendingStatus])
                ->latest()
                ->limit(10)
                ->get();

            // Get daily spending data for chart (last 7 days)
            $dailySpending = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i);
                $dailySpending[] = [
                    'date' => $date->format('M d'),
                    'amount' => WalletTransaction::where('wallet_id', $mahasiswa->wallet->wallet_id)
                        ->where('tipe_transaksi', 'debit')
                        ->where('status_transaksi', $completeStatus)
                        ->whereDate('created_at', $date)
                        ->sum('amount'),
                ];
            }

            // Get spending by category
            $categorySpending = WalletTransaction::where('wallet_id', $mahasiswa->wallet->wallet_id)
                ->where('tipe_transaksi', 'debit')
                ->where('status_transaksi', $completeStatus)
                ->where('created_at', '>=', $thisMonth)
                ->with(['detailProdukTransactions.produk'])
                ->get()
                ->groupBy(function ($transaction) {
                    return $transaction->detailPengeluaran
                        ? $transaction->detailProdukTransactions->first()?->produk?->kategori ?? 'Other'
                        : 'Other';
                })
                ->map(function ($group) {
                    return $group->sum('amount');
                });
        }
        
        return Inertia::render('mahasiswa/dashboard', [
            'mahasiswa' => $mahasiswa->load('user', 'wallet'),
            'stats' => $stats,
            'recentTransactions' => $recentTransactions,
            'dailySpending' => $dailySpending,
            'categorySpending' => $categorySpending,
            'hasWallet' => $mahasiswa->wallet !== null,
        ]);
    }

    public function profile()
    {
        $mahasiswa = Auth::user()->mahasiswa;
        
        if (!$mahasiswa) {
            return redirect()->route('dashboard')
                ->with('error', 'Student profile not found.');
        }

        return Inertia::render('Mahasiswa/Profile', [
            'mahasiswa' => $mahasiswa->load('user'),
        ]);
    }

    public function updateProfile(ProfileUpdateRequest $request)
    {
        $mahasiswa = Auth::user()->mahasiswa;
        
        if (!$mahasiswa) {
            return redirect()->route('dashboard')
                ->with('error', 'Student profile not found.');
        }

        // Update user information
        $mahasiswa->user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        // Update mahasiswa information
        $mahasiswa->update([
            'nama_lengkap' => $request->nama_lengkap,
            'jurusan' => $request->jurusan,
            'angkatan' => $request->angkatan,
        ]);

        return redirect()->back()
            ->with('success', 'Profile updated successfully.');
    }

    public function qrCode()
    {
        $mahasiswa = Auth::user()->mahasiswa;
        
        if (!$mahasiswa) {
            return redirect()->route('dashboard')
                ->with('error', 'Student profile not found.');
        }

        // Generate QR code data (you can customize this format)
        $qrData = [
            'type' => 'mahasiswa',
            'nim' => $mahasiswa->nim,
            'id' => $mahasiswa->id,
            'name' => $mahasiswa->nama_lengkap,
            'balance' => $mahasiswa->wallet->saldo ?? 0,
            'generated_at' => now()->toISOString(),
        ];

        return Inertia::render('Mahasiswa/QrCode', [
            'mahasiswa' => $mahasiswa->load('user', 'wallet'),
            'qrData' => base64_encode(json_encode($qrData)),
        ]);
    }

    public function merchants()
    {
        $mahasiswa = Auth::user()->mahasiswa;
        
        if (!$mahasiswa) {
            return redirect()->route('dashboard')
                ->with('error', 'Student profile not found.');
        }

        // Get wallet or redirect to create wallet
        $wallet = $mahasiswa->wallet;
        if (!$wallet) {
            return redirect()->route('mahasiswa.dashboard')
                ->with('error', 'Please create a wallet first.');
        }

        // Get merchants where this mahasiswa has made transactions
        $visitedMerchants = WalletTransaction::where('wallet_id', $wallet->wallet_id)
            ->where('tipe_transaksi', 'debit')
            ->with(['detailPengeluaran.kasir.merchant'])
            ->get()
            ->pluck('detailPengeluaran.kasir.merchant')
            ->filter()
            ->unique('merchant_id')
            ->values();

        // Get all merchants for discovery
        $allMerchants = Merchant::with(['kasir', 'produks' => function($query) {
            $query->where('stok', '>', 0)->orderBy('nama');
        }])->get();

        return Inertia::render('mahasiswa/merchants', [
            'visitedMerchants' => $visitedMerchants,
            'allMerchants' => $allMerchants,
            'currentBalance' => $wallet->balance,
        ]);
    }

    public function merchantDetail($merchantId)
    {
        $mahasiswa = Auth::user()->mahasiswa;
        
        if (!$mahasiswa) {
            return redirect()->route('dashboard')
                ->with('error', 'Student profile not found.');
        }

        $merchant = \App\Models\Merchant::with(['kasir', 'produk' => function ($query) {
            $query->where('status', 'active');
        }])->findOrFail($merchantId);

        // Get transaction history with this merchant
        $transactionHistory = WalletTransaction::where('mahasiswa_id', $mahasiswa->id)
            ->where('type', 'debit')
            ->whereHas('detailPengeluaran.kasir', function ($query) use ($merchantId) {
                $query->where('merchant_id', $merchantId);
            })
            ->with(['detailPengeluaran.kasir', 'detailPengeluaran.detailProdukTransactions.produk'])
            ->latest()
            ->limit(10)
            ->get();

        return Inertia::render('Mahasiswa/MerchantDetail', [
            'merchant' => $merchant,
            'transactionHistory' => $transactionHistory,
            'currentBalance' => $mahasiswa->wallet->saldo ?? 0,
        ]);
    }

    public function getMerchantProducts(Request $request, $merchantId)
    {
        $merchant = Merchant::with(['produks' => function($query) {
            $query->where('stok', '>', 0)->orderBy('nama');
        }])->findOrFail($merchantId);

        return response()->json([
            'merchant' => $merchant,
            'products' => $merchant->produks,
        ]);
    }

    public function createOrder(Request $request)
    {
        $mahasiswa = Auth::user()->mahasiswa;
        $wallet = $mahasiswa->wallet;
        if (!$wallet) {
            return response()->json(['error' => 'Wallet not found'], 404);
        }
        $request->validate([
            'merchant_id' => 'required|exists:merchants,merchant_id',
            'items' => 'required|array|min:1',
            'items.*.produk_id' => 'required|exists:produks,produk_id',
            'items.*.quantity' => 'required|integer|min:1',
            'total_amount' => 'required|numeric|min:0',
        ]);

        $totalAmount = $request->total_amount;
        // Check wallet balance
        if ($wallet->balance < $totalAmount) {
            return response()->json(['error' => 'Insufficient balance'], 400);
        }

        try {
            \DB::transaction(function () use ($request, $wallet, $totalAmount, $mahasiswa) {
                // Create wallet transaction
                $prefixInvoice = config('wallet.list_invoice_prefix.merchant_payment');
                $pendingStatus = config('wallet.status_types.pending');
                $walletTransaction = WalletTransaction::create([
                    'wallet_id' => $wallet->wallet_id,
                    'invoice' => $prefixInvoice . strtoupper(uniqid()),
                    'tipe_transaksi' => 'debit',
                    'status_transaksi' => $pendingStatus,
                    'amount' => $totalAmount,
                    'curr_balance' => $wallet->balance,
                    'after_balance' => $wallet->balance, // Will be updated when confirmed
                    'deskripsi' => 'Payment to merchant ID ' . $request->merchant_id,
                ]);

                // Create detail pengeluaran
                // $kasir = \App\Models\Kasir::where('merchant_id', $request->merchant_id)->first();
                // if (!$kasir) {
                //     throw new \Exception('No cashier found for this merchant');
                // }

                $detailPengeluaran = DetailPengeluaran::create([
                    'transaction_id' => $walletTransaction->transaction_id,
                    'kasir_id' => null,
                    'merchant_id' => $request->merchant_id,
                ]);

                // Create detail produk transactions
                foreach ($request->items as $item) {
                    $produk = Produk::findOrFail($item['produk_id']);
                    
                    // Check stock availability
                    if ($produk->stok < $item['quantity']) {
                        throw new \Exception("Insufficient stock for {$produk->nama}");
                    }

                    DetailProdukTransaction::create([
                        'transaction_id' => $walletTransaction->transaction_id,
                        'produk_id' => $item['produk_id'],
                        'qty' => $item['quantity'],
                        'harga' => $produk->harga,
                    ]);
                }
            });

            return redirect()->route('mahasiswa.transactions.index')
                ->with('success', 'Order created successfully and is pending confirmation');
            
        } catch (\Exception $e) {
            dd($e->getMessage());

            return redirect()->back()
                ->with('error', 'Order creation failed: ' . $e->getMessage());
        }
    }
}
