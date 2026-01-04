<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Merchant;
use App\Models\Kasir;
use App\Models\Mahasiswa;
use App\Models\WalletTransaction;
use App\Models\DetailPengeluaran;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth');
        // $this->middleware('admin');
    }

    public function index()
    {
        // Get basic statistics
        $stats = [
            'total_users' => User::count(),
            'total_merchants' => Merchant::count(),
            'total_kasir' => Kasir::count(),
            'total_mahasiswa' => Mahasiswa::count(),
        ];

        // Get transaction statistics for today
        $today = Carbon::today();
        $transactionStats = [
            'total_transactions_today' => WalletTransaction::whereDate('created_at', $today)->whereIn('tipe_transaksi', ['credit', 'debit'])->count(),
            // 'total_revenue_today' => DetailPengeluaran::whereDate('created_at', $today)->sum('total_harga'),
            'pending_transactions' => WalletTransaction::where('status_transaksi', 'pending')->whereIn('tipe_transaksi', ['credit', 'debit'])->count(),
            'completed_transactions' => WalletTransaction::whereIn('tipe_transaksi', ['credit', 'debit'])->where('status_transaksi', 'completed')->count(),
        ];

        // Get recent transactions
        $recentTransactions = WalletTransaction::with([
            'wallet.mahasiswa.user',
            'detailPengeluaran',
            'detailPemasukan'
        ])->whereIn('tipe_transaksi', ['credit', 'debit'])
        ->latest()
        ->limit(10)
        ->get();
        // Get monthly transaction data for chart
        $monthlyData = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthlyData[] = [
                'month' => $date->format('M Y'),
                'transactions' => WalletTransaction::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)->whereIn('tipe_transaksi', ['credit', 'debit'])
                    ->count()
                // 'revenue' => DetailPengeluaran::whereYear('created_at', $date->year)
                //     ->whereMonth('created_at', $date->month)
                //     ->sum('total_harga'),
            ];
        }

        return Inertia::render('admin/dashboard', [
            'stats' => $stats,
            'transactionStats' => $transactionStats,
            'recentTransactions' => $recentTransactions,
            'monthlyData' => $monthlyData,
        ]);
    }
}
