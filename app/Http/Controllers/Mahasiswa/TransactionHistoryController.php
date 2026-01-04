<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;

class TransactionHistoryController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth');
        // $this->middleware('mahasiswa');
    }

    public function index(Request $request)
    {
        $mahasiswa = Auth::user()->mahasiswa;
        $wallet = $mahasiswa->wallet;
        if (!$mahasiswa) {
            return redirect()->route('dashboard')
                ->with('error', 'Student profile not found.');
        }
        if(!$wallet) {
            return redirect()->route('dashboard')
                ->with('error', 'Wallet not found. Please create a wallet first.');
        }

        $query = WalletTransaction::where('wallet_id', $wallet->wallet_id)
            ->with([
                'detailPengeluaran.kasir.merchant',
                'detailProdukTransactions.produk',
                'detailPengeluaran.kasir',
                'detailPemasukan.metodeBayar'
            ])->whereIn('tipe_transaksi', ['credit', 'debit']);

        // Filter by type
        if ($request->type && in_array($request->type, ['credit', 'debit'])) {
            $query->where('tipe_transaksi', $request->type);
        }

        // Filter by status
        if ($request->status && in_array($request->status, ['pending', 'completed', 'failed'])) {
            $query->where('status_transaksi', $request->status);
        }

        // Filter by date range
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', Carbon::parse($request->date_from));
        }

        if ($request->date_to) {
            $query->whereDate('created_at', '<=', Carbon::parse($request->date_to));
        }

        // Filter by amount range
        if ($request->amount_min) {
            $query->where('amount', '>=', $request->amount_min);
        }

        if ($request->amount_max) {
            $query->where('amount', '<=', $request->amount_max);
        }

        // Search in description
        if ($request->search) {
            $query->where('deskripsi', 'like', '%' . $request->search . '%');
        }

        $transactions = $query->latest()->paginate(20);

        // Calculate summary statistics
        $summary = [
            'total_credit' => WalletTransaction::where('wallet_id', $wallet->wallet_id)
                ->where('tipe_transaksi', 'credit')
                ->where('status_transaksi', 'completed')
                ->sum('amount'),
            'total_debit' => WalletTransaction::where('wallet_id', $wallet->wallet_id)
                ->where('tipe_transaksi', 'debit')
                ->where('status_transaksi', 'completed')
                ->sum('amount'),
            'total_transactions' => WalletTransaction::where('wallet_id', $wallet->wallet_id)->whereIn('tipe_transaksi', ['credit', 'debit'])->count(),
            'current_balance' => $wallet->balance ?? 0,
        ];

        return Inertia::render('mahasiswa/transactionHistory/index', [
            'transactions' => $transactions,
            'summary' => $summary,
            'filters' => $request->only(['type', 'status', 'date_from', 'date_to', 'amount_min', 'amount_max', 'search']),
        ]);
    }

    public function show($id)
    {
        $mahasiswa = Auth::user()->mahasiswa;
        
        if (!$mahasiswa) {
            return redirect()->route('dashboard')
                ->with('error', 'Student profile not found.');
        }

        $transaction = WalletTransaction::where('mahasiswa_id', $mahasiswa->id)
            ->where('id', $id)
            ->with([
                'detailPengeluaran.kasir.merchant',
                'detailPengeluaran.detailProdukTransactions.produk',
                'detailPengeluaran.metodeBayar',
                'detailPemasukan.metodeBayar'
            ])
            ->firstOrFail();

        return Inertia::render('Mahasiswa/TransactionHistory/Show', [
            'transaction' => $transaction,
        ]);
    }

    public function export(Request $request)
    {
        $mahasiswa = Auth::user()->mahasiswa;
        
        if (!$mahasiswa) {
            return redirect()->route('dashboard')
                ->with('error', 'Student profile not found.');
        }

        $query = WalletTransaction::where('mahasiswa_id', $mahasiswa->id)
            ->with([
                'detailPengeluaran.kasir.merchant',
                'detailPengeluaran.detailProdukTransactions.produk',
                'detailPemasukan'
            ]);

        // Apply same filters as index
        if ($request->type && in_array($request->type, ['credit', 'debit'])) {
            $query->where('type', $request->type);
        }

        if ($request->status && in_array($request->status, ['pending', 'completed', 'failed'])) {
            $query->where('status', $request->status);
        }

        if ($request->date_from) {
            $query->whereDate('created_at', '>=', Carbon::parse($request->date_from));
        }

        if ($request->date_to) {
            $query->whereDate('created_at', '<=', Carbon::parse($request->date_to));
        }

        $transactions = $query->latest()->get();

        // Generate CSV
        $filename = 'transaction_history_' . $mahasiswa->nim . '_' . now()->format('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($transactions) {
            $file = fopen('php://output', 'w');
            
            // CSV Header
            fputcsv($file, [
                'Date',
                'Type',
                'Amount',
                'Description',
                'Status',
                'Merchant',
                'Balance After',
            ]);

            // CSV Data
            foreach ($transactions as $transaction) {
                $merchantName = '';
                if ($transaction->detailPengeluaran && $transaction->detailPengeluaran->kasir) {
                    $merchantName = $transaction->detailPengeluaran->kasir->merchant->nama_merchant ?? '';
                }

                fputcsv($file, [
                    $transaction->created_at->format('Y-m-d H:i:s'),
                    ucfirst($transaction->type),
                    number_format($transaction->amount, 0),
                    $transaction->description,
                    ucfirst($transaction->status),
                    $merchantName,
                    // Note: Balance after would need to be calculated based on transaction order
                    '',
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function analytics()
    {
        $mahasiswa = Auth::user()->mahasiswa;
        
        if (!$mahasiswa) {
            return redirect()->route('dashboard')
                ->with('error', 'Student profile not found.');
        }

        // Monthly spending for the last 12 months
        $monthlySpending = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $spending = WalletTransaction::where('mahasiswa_id', $mahasiswa->id)
                ->where('type', 'debit')
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->sum('amount');
            
            $monthlySpending[] = [
                'month' => $date->format('M Y'),
                'spending' => $spending,
            ];
        }

        // Top merchants by spending
        $topMerchants = WalletTransaction::where('mahasiswa_id', $mahasiswa->id)
            ->where('type', 'debit')
            ->where('created_at', '>=', Carbon::now()->subMonth())
            ->with(['detailPengeluaran.kasir.merchant'])
            ->get()
            ->groupBy(function ($transaction) {
                return $transaction->detailPengeluaran?->kasir?->merchant?->nama_merchant ?? 'Unknown';
            })
            ->map(function ($group) {
                return [
                    'total' => $group->sum('amount'),
                    'count' => $group->count(),
                ];
            })
            ->sortByDesc('total')
            ->take(10);

        // Daily average spending
        $totalDays = WalletTransaction::where('mahasiswa_id', $mahasiswa->id)
            ->where('type', 'debit')
            ->selectRaw('COUNT(DISTINCT DATE(created_at)) as days')
            ->first()
            ->days ?? 1;

        $totalSpent = WalletTransaction::where('mahasiswa_id', $mahasiswa->id)
            ->where('type', 'debit')
            ->sum('amount');

        $dailyAverage = $totalSpent / $totalDays;

        // Category breakdown
        $categorySpending = WalletTransaction::where('mahasiswa_id', $mahasiswa->id)
            ->where('type', 'debit')
            ->where('created_at', '>=', Carbon::now()->subMonth())
            ->with(['detailPengeluaran.detailProdukTransactions.produk'])
            ->get()
            ->groupBy(function ($transaction) {
                $kategori = 'Other';
                if ($transaction->detailPengeluaran) {
                    $firstProduct = $transaction->detailPengeluaran->detailProdukTransactions->first();
                    if ($firstProduct && $firstProduct->produk) {
                        $kategori = $firstProduct->produk->kategori;
                    }
                }
                return $kategori;
            })
            ->map(function ($group) {
                return $group->sum('amount');
            });

        return Inertia::render('Mahasiswa/TransactionHistory/Analytics', [
            'monthlySpending' => $monthlySpending,
            'topMerchants' => $topMerchants,
            'dailyAverage' => $dailyAverage,
            'categorySpending' => $categorySpending,
            'currentBalance' => $mahasiswa->wallet->saldo ?? 0,
        ]);
    }
}
