<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Kasir;
use App\Models\DetailPengeluaran;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;

class KasirController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth');
        // $this->middleware('kasir');
    }

    public function dashboard()
    {
        $kasir = Auth::user()->kasir;
        $merchant = $kasir ? $kasir->merchant : null;
        
        if (!$kasir) {
            return redirect()->route('dashboard')
                ->with('error', 'Kasir profile not found.');
        }

        $today = Carbon::today();
        
        // Get today's statistics
        $stats = [
            'total_transactions_today' => WalletTransaction::whereHas('detailPengeluaran', function ($query) use ($merchant) {
                    $query->where('merchant_id', $merchant->merchant_id);
            })
                ->whereDate('created_at', $today)
                ->count(),
            'total_revenue_today' => WalletTransaction::whereHas('detailPengeluaran', function ($query) use ($merchant) {
                    $query->where('merchant_id', $merchant->merchant_id);
            })
                ->whereDate('created_at', $today)
                ->sum('amount'),
            'pending_orders' => WalletTransaction::whereHas('detailPengeluaran', function ($query) use ($merchant) {
                    $query->where('merchant_id', $merchant->merchant_id);
            })
                ->where('status_transaksi', 'pending')
                ->count(),
            'completed_orders' => WalletTransaction::whereHas('detailPengeluaran', function ($query) use ($merchant) {
                    $query->where('merchant_id', $merchant->merchant_id);
            })
                ->where('status_transaksi', 'completed')
                ->count(),
        ];

        // Get recent transactions
        $recentTransactions = WalletTransaction::whereHas('detailPengeluaran', function ($query) use ($merchant) {
                $query->where('merchant_id', $merchant->merchant_id);
        })
            ->with([
                'wallet.mahasiswa.user',
                'detailProdukTransactions.produk',
                'detailPengeluaran',
            ])
            ->latest()
            ->limit(10)
            ->get();

        // Get weekly revenue data for chart
        $weeklyData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $weeklyData[] = [
                'date' => $date->format('M d'),
                'revenue' => WalletTransaction::whereHas('detailPengeluaran', function ($query) use ($merchant) {
                    $query->where('merchant_id', $merchant->merchant_id);
                })
                    ->whereDate('created_at', $date)
                    ->sum('amount'),
                'transactions' => WalletTransaction::whereHas('detailPengeluaran', function ($query) use ($merchant) {
                    $query->where('merchant_id', $merchant->merchant_id);
                })
                    ->whereDate('created_at', $date)
                    ->count(),
            ];
        }

        return Inertia::render('kasir/dashboard', [
            'kasir' => $kasir->load('user', 'merchant'),
            'stats' => $stats,
            'recentTransactions' => $recentTransactions,
            'weeklyData' => $weeklyData,
        ]);
    }

    public function profile()
    {
        $kasir = Auth::user()->kasir;
        
        if (!$kasir) {
            return redirect()->route('dashboard')
                ->with('error', 'Kasir profile not found.');
        }

        return Inertia::render('Kasir/Profile', [
            'kasir' => $kasir->load('user', 'merchant'),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'nama_kasir' => 'required|string|max:255',
            'no_telepon' => 'required|string|max:20',
        ]);

        $kasir = Auth::user()->kasir;
        
        if (!$kasir) {
            return redirect()->route('dashboard')
                ->with('error', 'Kasir profile not found.');
        }

        $kasir->update([
            'nama_kasir' => $request->nama_kasir,
            'no_telepon' => $request->no_telepon,
        ]);

        return redirect()->back()
            ->with('success', 'Profile updated successfully.');
    }

    public function transactions(Request $request)
    {
        $kasir = Auth::user()->kasir;
        $merchant = $kasir ? $kasir->merchant : null;

        if (!$kasir) {
            return redirect()->route('dashboard')
                ->with('error', 'Kasir profile not found.');
        }

        $query = WalletTransaction::whereHas('detailPengeluaran', function ($q) use ($merchant) {
                $q->where('merchant_id', $merchant->merchant_id);
        })
        ->with(['wallet.mahasiswa.user', 'detailProdukTransactions.produk']);

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('invoice', 'like', "%{$search}%")
                  ->orWhereHas('wallet.mahasiswa', function ($mahasiswaQuery) use ($search) {
                      $mahasiswaQuery->where('nama', 'like', "%{$search}%")
                                   ->orWhere('npm', 'like', "%{$search}%");
                  });
            });
        }

        // Apply status filter
        if ($request->filled('status') && $request->get('status') !== 'all') {
            $query->where('status_transaksi', $request->get('status'));
        }

        // Apply date filter
        if ($request->filled('date') && $request->get('date') !== 'all') {
            $dateFilter = $request->get('date');
            $now = Carbon::now();
            
            switch ($dateFilter) {
                case 'today':
                    $query->whereDate('created_at', $now->toDateString());
                    break;
                case 'yesterday':
                    $query->whereDate('created_at', $now->subDay()->toDateString());
                    break;
                case 'week':
                    $query->whereBetween('created_at', [
                        $now->startOfWeek()->toDateString(),
                        $now->endOfWeek()->toDateString()
                    ]);
                    break;
                case 'month':
                    $query->whereBetween('created_at', [
                        $now->startOfMonth()->toDateString(),
                        $now->endOfMonth()->toDateString()
                    ]);
                    break;
            }
        }
        
        $transactions = $query->latest()->paginate(20)->withQueryString();
        
        return Inertia::render('kasir/transactions/index', [
            'transactions' => $transactions,
            'filters' => $request->only(['search', 'status', 'date']),
        ]);
    }

    public function confirmTransaction(Request $request, $id)
    {
        $kasir = Auth::user()->kasir;
        $merchant = $kasir ? $kasir->merchant : null;
        
        if (!$kasir) {
            return redirect()->route('dashboard')
                ->with('error', 'Kasir profile not found.');
        }
        // Find the transaction that belongs to this merchant
        $transaction = WalletTransaction::whereHas('detailPengeluaran', function ($query) use ($merchant) {
                $query->where('merchant_id', $merchant->merchant_id);
        })
        ->with(['wallet', 'detailProdukTransactions.produk'])
        ->where('transaction_id', $id)
        ->where('status_transaksi', 'pending')
        ->firstOrFail();
        try {
            \DB::transaction(function () use ($transaction, $kasir) {
                $wallet = $transaction->wallet;
                $currentBalance = $wallet->balance;
                // Check if wallet has sufficient balance
                if ($currentBalance < $transaction->amount) {
                    throw new \Exception('Insufficient wallet balance');
                }
                
                // Update wallet balance
                $newBalance = $currentBalance - $transaction->amount;
                $wallet->update(['balance' => $newBalance]);
                
                // Update transaction status and balance info
                $transaction->update([
                    'status_transaksi' => 'completed',
                    'curr_balance' => $currentBalance,
                    'after_balance' => $newBalance,
                ]);
                
                // Update product stock
                foreach ($transaction->detailProdukTransactions as $detail) {
                    $produk = $detail->produk;
                    $newStock = $produk->stok - $detail->qty;
                    
                    // Ensure stock doesn't go negative
                    if ($newStock < 0) {
                        throw new \Exception("Insufficient stock for product: {$produk->nama}");
                    }
                    
                    $produk->update(['stok' => $newStock]);
                }

                DetailPengeluaran::where('transaction_id', $transaction->transaction_id)
                    ->update(['kasir_id' => $kasir->kasir_id]);
            });
            
            return redirect()->back()
                ->with('success', 'Transaction confirmed successfully.');
                
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to confirm transaction: ' . $e->getMessage());
        }
    }

    public function transactionDetail($id)
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
                'metodeBayar'
            ])
            ->firstOrFail();

        return Inertia::render('Kasir/Transactions/Show', [
            'transaction' => $transaction,
        ]);
    }
}
