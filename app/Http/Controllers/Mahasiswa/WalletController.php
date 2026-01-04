<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mahasiswa\TopUpRequest;
use App\Models\Mahasiswa;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\DetailPemasukan;
use App\Models\MetodeBayar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;

class WalletController extends Controller
{
    public function __construct()
    {
        // Middleware is applied in routes/mahasiswa.php
    }

    public function index()
    {
        $mahasiswa = Auth::user()->mahasiswa;
        
        if (!$mahasiswa) {
            return redirect()->route('dashboard')
                ->with('error', 'Student profile not found.');
        }

        $wallet = $mahasiswa->wallet;
        
        if (!$wallet) {
            // Create wallet if not exists
            $wallet = \App\Models\Wallet::create([
                'mahasiswa_id' => $mahasiswa->id,
                'saldo' => 0,
            ]);
        }

        // Get recent wallet transactions
        $recentTransactions = WalletTransaction::where('wallet_id', $wallet->wallet_id)
            ->with([
                'detailPengeluaran.kasir.merchant',
                'detailPengeluaran.detailProdukTransactions.produk',
                'detailPemasukan'
            ])
            ->latest()
            ->paginate(15);

        return Inertia::render('Mahasiswa/Wallet/Index', [
            'wallet' => $wallet,
            'transactions' => $recentTransactions,
        ]);
    }

    public function createWallet()
    {
        $mahasiswa = Auth::user()->mahasiswa;
        if (!$mahasiswa) {
            return redirect()->route('mahasiswa.dashboard')
                ->with('error', 'Student profile not found.');
        }

        // Check if wallet already exists
        if ($mahasiswa->wallet) {
            return redirect()->route('mahasiswa.dashboard')
                ->with('info', 'Wallet already exists.');
        }

        try {
            DB::beginTransaction();

            $defaultPin = config('wallet.default_pin');
            $hashedPin = Hash::make($defaultPin . config('wallet.pin_salt'));

            // Create wallet for mahasiswa
            $wallet = Wallet::create([
                'mahasiswa_id' => $mahasiswa->mahasiswa_id,
                'balance' => 0,
                'balance_settled' => 0,
                'pin' => $hashedPin,
            ]);

            $invoicePrefix = config('wallet.list_invoice_prefix.create_wallet');
            $typeTransaction = config('wallet.transaction_types.initial');
            $statusTransaction = config('wallet.status_types.completed');
            // Create initial transaction record
            WalletTransaction::create([
                'wallet_id' => $wallet->wallet_id,
                'invoice' => $invoicePrefix . uniqid(),
                'amount' => 0,
                'tipe_transaksi' => $typeTransaction,
                'status_transaksi' => $statusTransaction,
                'deskripsi' => 'Wallet created',
                'waktu_transaksi' => now(),
                'curr_balance' => 0,
                'after_balance' => 0,
            ]);

            DB::commit();

            return redirect()->route('mahasiswa.dashboard')
                ->with('success', 'Wallet created successfully! You can now start using your digital wallet.');

        } catch (\Exception $e) {
            DB::rollback();
            dd($e->getMessage());
            return redirect()->route('mahasiswa.dashboard')
                ->with('error', 'Failed to create wallet: ' . $e->getMessage());
        }
    }

    public function topUp()
    {
        $mahasiswa = Auth::user()->mahasiswa;
        
        if (!$mahasiswa) {
            return redirect()->route('dashboard')
                ->with('error', 'Student profile not found.');
        }
        if(!$mahasiswa->wallet) {
            return redirect()->route('dashboard')
                ->with('error', 'Wallet not found. Please create a wallet first.');
        }

        $metodeBayar = MetodeBayar::get();

        return Inertia::render('mahasiswa/wallet/top-up', [
            'mahasiswa' => $mahasiswa->load('wallet'),
            'metodeBayar' => $metodeBayar,
        ]);
    }

    public function processTopUp(TopUpRequest $request)
    {
        $mahasiswa = Auth::user()->mahasiswa;
        $wallet = $mahasiswa ? $mahasiswa->wallet : null;
        
        if (!$mahasiswa) {
            return redirect()->route('dashboard')
                ->with('error', 'Student profile not found.');
        }

        try {
            DB::beginTransaction();

            $amount = $request->amount;
            $metodeBayar = MetodeBayar::findOrFail($request->metode_id);
            $description = $request->keterangan ?? 'Top up wallet';

            $invoicePrefix = config('wallet.list_invoice_prefix.top_up');
            $typeTransaction = config('wallet.transaction_types.credit');
            $statusTransaction = config('wallet.status_types.pending');
            // Create wallet transaction
            $walletTransaction = WalletTransaction::create([
                'wallet_id' => $wallet->wallet_id,
                'invoice' => $invoicePrefix . strtoupper(uniqid()),
                'tipe_transaksi' => $typeTransaction,
                'status_transaksi' => $statusTransaction,
                'amount' => $amount,
                'deskripsi' => $description,
                'waktu_transaksi' => now(),
                'curr_balance' => $wallet->balance,
                'after_balance' => $wallet->balance + $amount,
            ]);

            // Create detail pemasukan
            $detailPemasukan = DetailPemasukan::create([
                'transaction_id' => $walletTransaction->transaction_id,
                'metode_id' => $metodeBayar->metode_id,
            ]);

            DB::commit();

            return redirect()->route('mahasiswa.transactions.index')
                ->with('success', 'Top-up request submitted successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            
            return redirect()->back()
                ->withErrors(['error' => 'Top-up failed: ' . $e->getMessage()]);
        }
    }

    public function transfer()
    {
        $mahasiswa = Auth::user()->mahasiswa;
        
        if (!$mahasiswa) {
            return redirect()->route('dashboard')
                ->with('error', 'Student profile not found.');
        }

        return Inertia::render('Mahasiswa/Wallet/Transfer', [
            'mahasiswa' => $mahasiswa->load('wallet'),
        ]);
    }

    public function processTransfer(Request $request)
    {
        $request->validate([
            'recipient_nim' => 'required|string|exists:mahasiswa,nim',
            'amount' => 'required|numeric|min:1000|max:1000000',
            'description' => 'nullable|string|max:255',
        ]);

        $mahasiswa = Auth::user()->mahasiswa;
        
        if (!$mahasiswa) {
            return redirect()->route('dashboard')
                ->with('error', 'Student profile not found.');
        }

        $recipient = Mahasiswa::where('nim', $request->recipient_nim)->first();
        
        if (!$recipient) {
            return redirect()->back()
                ->withErrors(['recipient_nim' => 'Student not found.']);
        }

        if ($recipient->id === $mahasiswa->id) {
            return redirect()->back()
                ->withErrors(['recipient_nim' => 'Cannot transfer to yourself.']);
        }

        $amount = $request->amount;

        // Check sufficient balance
        if ($mahasiswa->wallet->saldo < $amount) {
            return redirect()->back()
                ->withErrors(['amount' => 'Insufficient balance.']);
        }

        try {
            DB::beginTransaction();

            // Create debit transaction for sender
            $debitTransaction = WalletTransaction::create([
                'mahasiswa_id' => $mahasiswa->id,
                'type' => 'debit',
                'amount' => $amount,
                'description' => 'Transfer to ' . $recipient->nama_lengkap . ' (' . $recipient->nim . ')',
                'status' => 'completed',
            ]);

            // Create credit transaction for recipient
            $creditTransaction = WalletTransaction::create([
                'mahasiswa_id' => $recipient->id,
                'type' => 'credit',
                'amount' => $amount,
                'description' => 'Transfer from ' . $mahasiswa->nama_lengkap . ' (' . $mahasiswa->nim . ')',
                'status' => 'completed',
            ]);

            // Update balances
            $mahasiswa->wallet->decrement('saldo', $amount);
            $recipient->wallet->increment('saldo', $amount);

            DB::commit();

            return redirect()->route('mahasiswa.wallet.index')
                ->with('success', 'Transfer completed successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            
            return redirect()->back()
                ->withErrors(['error' => 'Transfer failed: ' . $e->getMessage()]);
        }
    }

    public function searchMahasiswa(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:1',
        ]);

        $currentMahasiswa = Auth::user()->mahasiswa;
        
        $mahasiswa = Mahasiswa::with(['user', 'wallet'])
            ->where('id', '!=', $currentMahasiswa->id) // Exclude current user
            ->where(function ($q) use ($request) {
                $q->where('nim', 'like', '%' . $request->query . '%')
                  ->orWhere('nama_lengkap', 'like', '%' . $request->query . '%');
            })
            ->limit(10)
            ->get();

        return response()->json($mahasiswa);
    }

    public function transactionDetail($id)
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

        return Inertia::render('Mahasiswa/Wallet/TransactionDetail', [
            'transaction' => $transaction,
        ]);
    }

    public function verifyPin(Request $request)
    {
        $mahasiswa = Auth::user()->mahasiswa;
        
        if (!$mahasiswa) {
            return response()->json(['error' => 'Student profile not found'], 404);
        }

        $wallet = $mahasiswa->wallet;
        
        if (!$wallet) {
            return response()->json(['error' => 'Wallet not found'], 404);
        }

        $request->validate([
            'pin' => 'required|string|size:6|regex:/^[0-9]{6}$/',
        ], [
            'pin.required' => 'PIN is required.',
            'pin.size' => 'PIN must be exactly 6 digits.',
            'pin.regex' => 'PIN must contain only numbers.',
        ]);

        try {
            $pinToVerify = $request->pin . config('wallet.pin_salt');
            
            if (!Hash::check($pinToVerify, $wallet->pin)) {
                return redirect()->back()
                    ->withErrors(['pin' => 'Invalid PIN.']);
            }

            return redirect()->back()
                ->with('success', 'PIN verified successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'PIN verification failed: ' . $e->getMessage());
            // return response()->json(['error' => 'PIN verification failed'], 500);
        }
    }

    public function updatePin(Request $request)
    {
        $mahasiswa = Auth::user()->mahasiswa;
        
        if (!$mahasiswa) {
            return redirect()->route('dashboard')
                ->with('error', 'Student profile not found.');
        }

        $wallet = $mahasiswa->wallet;
        
        if (!$wallet) {
            return redirect()->route('mahasiswa.dashboard')
                ->with('error', 'Wallet not found.');
        }

        $request->validate([
            'old_pin' => 'required|string|size:6|regex:/^[0-9]{6}$/',
            'new_pin' => 'required|string|size:6|regex:/^[0-9]{6}$/',
        ], [
            'old_pin.required' => 'Current PIN is required.',
            'old_pin.size' => 'Current PIN must be exactly 6 digits.',
            'old_pin.regex' => 'Current PIN must contain only numbers.',
            'new_pin.required' => 'New PIN is required.',
            'new_pin.size' => 'New PIN must be exactly 6 digits.',
            'new_pin.regex' => 'New PIN must contain only numbers.',
        ]);

        try {
            // Verify old PIN first
            $oldPinToVerify = $request->old_pin . config('wallet.pin_salt');
            
            if (!Hash::check($oldPinToVerify, $wallet->pin)) {
                return redirect()->back()
                    ->with('error', 'Invalid current PIN.');
            }

            // Update with new PIN
            $wallet->update([
                'pin' => Hash::make($request->new_pin . config('wallet.pin_salt')),
            ]);

            return redirect()->back()
                ->with('success', 'Wallet PIN updated successfully.');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update PIN: ' . $e->getMessage());
        }
    }
}
