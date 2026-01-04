<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class TransactionController extends Controller
{
    /**
     * Display a listing of transactions.
     */
    public function index(Request $request)
    {
        // Get filter parameters
        $search = $request->input('search');
        $status = $request->input('status');
        $type = $request->input('type');
        $date_from = $request->input('date_from');
        $date_to = $request->input('date_to');
        $per_page = $request->input('per_page', 10);

        // Build query
        $query = WalletTransaction::with([
            'wallet.mahasiswa.user',
            'detailPemasukan.metodeBayar',
            'detailPengeluaran'
        ])->orderBy('created_at', 'desc')->whereIn('tipe_transaksi', ['credit', 'debit']);

        // Apply filters
        if ($search) {
            $query->whereHas('wallet.mahasiswa.user', function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            })->orWhere('transaction_id', 'like', '%' . $search . '%');
        }

        if ($status && $status != 'all') {
            $query->where('status_transaksi', $status);
        }

        if ($type) {
            if ($type === 'top_up') {
                $query->whereHas('detailPemasukan');
            } elseif ($type === 'payment') {
                $query->whereHas('detailPengeluaran');
            }
        }

        if ($date_from) {
            $query->whereDate('created_at', '>=', $date_from);
        }

        if ($date_to) {
            $query->whereDate('created_at', '<=', $date_to);
        }

        $transactions = $query->paginate($per_page);

        // Get summary statistics
        $stats = [
            'total_transactions' => WalletTransaction::whereIn('tipe_transaksi', ['credit', 'debit'])->count(),
            'pending_transactions' => WalletTransaction::whereIn('tipe_transaksi', ['credit', 'debit'])->where('status_transaksi', 'pending')->count(),
            'completed_transactions' => WalletTransaction::whereIn('tipe_transaksi', ['credit', 'debit'])->where('status_transaksi', 'completed')->count(),
            'failed_transactions' => WalletTransaction::where('status_transaksi', 'failed')->count(),
            'total_amount_today' => WalletTransaction::whereDate('created_at', today())
                ->where('status_transaksi', 'completed')
                ->sum('amount'),
            'top_up_count' => WalletTransaction::whereHas('detailPemasukan')->count(),
            'payment_count' => WalletTransaction::whereHas('detailPengeluaran')->count(),
        ];

        return Inertia::render('admin/transactions/index', [
            'transactions' => $transactions,
            'stats' => $stats,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'type' => $type,
                'date_from' => $date_from,
                'date_to' => $date_to,
                'per_page' => $per_page,
            ],
        ]);
    }

    /**
     * Display the specified transaction.
     */
    public function show(WalletTransaction $transaction)
    {
        $transaction->load([
            'wallet.mahasiswa.user',
            'detail_pemasukan.metodeBayar',
            'detail_pengeluaran'
        ]);

        return Inertia::render('Admin/Transactions/Show', [
            'transaction' => $transaction,
        ]);
    }

    /**
     * Confirm a pending transaction.
     */
    public function confirm(WalletTransaction $transaction)
    {
        // Check if transaction is pending
        if ($transaction->status_transaksi !== 'pending') {
            return back()->with('error', 'Only pending transactions can be confirmed.');
        }

        try {
            \DB::transaction(function () use ($transaction) {
                // Load wallet relationship
                $transaction->load('wallet');
                $wallet = $transaction->wallet;

                // Store current balance
                $currentBalance = $wallet->balance;
                
                // Update transaction status and balances
                $transaction->update([
                    'status_transaksi' => 'completed',
                    'curr_balance' => $currentBalance,
                    'after_balance' => $currentBalance + $transaction->amount,
                ]);

                // Update wallet balance
                $wallet->update([
                    'balance' => $currentBalance + $transaction->amount,
                ]);
            });

            return back()->with('success', 'Transaction confirmed successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to confirm transaction: ' . $e->getMessage());
        }
    }

    /**
     * Export transactions to CSV.
     */
    public function export(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');
        $type = $request->input('type');
        $date_from = $request->input('date_from');
        $date_to = $request->input('date_to');

        // Build query for export (without pagination)
        $query = WalletTransaction::with([
            'wallet.mahasiswa.user',
            'detail_pemasukan.metodeBayar',
            'detail_pengeluaran'
        ])->orderBy('created_at', 'desc');

        // Apply same filters as index
        if ($search) {
            $query->whereHas('wallet.mahasiswa.user', function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            })->orWhere('transaction_id', 'like', '%' . $search . '%');
        }

        if ($status) {
            $query->where('status_transaksi', $status);
        }

        if ($type) {
            if ($type === 'top_up') {
                $query->whereNotNull('detail_pemasukan_id');
            } elseif ($type === 'payment') {
                $query->whereNotNull('detail_pengeluaran_id');
            }
        }

        if ($date_from) {
            $query->whereDate('created_at', '>=', $date_from);
        }

        if ($date_to) {
            $query->whereDate('created_at', '<=', $date_to);
        }

        $transactions = $query->get();

        // Prepare CSV data
        $csvData = $transactions->map(function ($transaction) {
            return [
                'Transaction ID' => $transaction->transaction_id,
                'User Name' => $transaction->wallet->mahasiswa->user->name ?? 'N/A',
                'User Email' => $transaction->wallet->mahasiswa->user->email ?? 'N/A',
                'Amount' => $transaction->amount,
                'Type' => $transaction->detail_pemasukan ? 'Top Up' : 'Payment',
                'Status' => ucfirst($transaction->status_transaksi),
                'Payment Method' => $transaction->detail_pemasukan?->metodeBayar?->nama_metode ?? 'N/A',
                'Created At' => $transaction->created_at->format('Y-m-d H:i:s'),
            ];
        });

        // Generate CSV content
        $filename = 'transactions_' . date('Y-m-d_H-i-s') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $handle = fopen('php://output', 'w');
        
        // Add CSV headers
        if ($csvData->isNotEmpty()) {
            fputcsv($handle, array_keys($csvData->first()));
        }
        
        // Add CSV data
        foreach ($csvData as $row) {
            fputcsv($handle, $row);
        }
        
        fclose($handle);

        return response()->stream(function () use ($csvData) {
            $handle = fopen('php://output', 'w');
            
            if ($csvData->isNotEmpty()) {
                fputcsv($handle, array_keys($csvData->first()));
            }
            
            foreach ($csvData as $row) {
                fputcsv($handle, $row);
            }
            
            fclose($handle);
        }, 200, $headers);
    }
}