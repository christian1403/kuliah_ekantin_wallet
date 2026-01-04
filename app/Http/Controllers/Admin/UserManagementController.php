<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MerchantRequest;
use App\Models\User;
use App\Models\Merchant;
use App\Models\Kasir;
use App\Models\Mahasiswa;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class UserManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    // Merchant Management
    public function merchants()
    {
        $merchants = Merchant::with('user')->paginate(20);

        return Inertia::render('Admin/Merchants/Index', [
            'merchants' => $merchants,
        ]);
    }

    public function createMerchant()
    {
        return Inertia::render('Admin/Merchants/Create');
    }

    public function storeMerchant(MerchantRequest $request)
    {
        // Create user first
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password ?? 'password123'),
            'email_verified_at' => now(),
        ]);

        $user->assignRole('admin'); // Merchants can have admin role or create separate merchant role

        // Create merchant
        $merchant = Merchant::create([
            'user_id' => $user->id,
            'nama_merchant' => $request->nama_merchant,
            'alamat' => $request->alamat,
            'no_telepon' => $request->no_telepon,
        ]);

        return redirect()->route('admin.merchants.index')
            ->with('success', 'Merchant created successfully.');
    }

    public function editMerchant(Merchant $merchant)
    {
        $merchant->load('user');

        return Inertia::render('Admin/Merchants/Edit', [
            'merchant' => $merchant,
        ]);
    }

    public function updateMerchant(MerchantRequest $request, Merchant $merchant)
    {
        // Update user
        $userData = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        if ($request->password) {
            $userData['password'] = Hash::make($request->password);
        }

        $merchant->user->update($userData);

        // Update merchant
        $merchant->update([
            'nama_merchant' => $request->nama_merchant,
            'alamat' => $request->alamat,
            'no_telepon' => $request->no_telepon,
        ]);

        return redirect()->route('admin.merchants.index')
            ->with('success', 'Merchant updated successfully.');
    }

    public function destroyMerchant(Merchant $merchant)
    {
        $user = $merchant->user;
        $merchant->delete();
        $user->delete();

        return redirect()->route('admin.merchants.index')
            ->with('success', 'Merchant deleted successfully.');
    }

    // Kasir Management
    public function kasir()
    {
        $kasir = Kasir::with('user', 'merchant')->paginate(20);

        return Inertia::render('Admin/Kasir/Index', [
            'kasir' => $kasir,
        ]);
    }

    public function createKasir()
    {
        $merchants = Merchant::with('user')->get();

        return Inertia::render('Admin/Kasir/Create', [
            'merchants' => $merchants,
        ]);
    }

    public function storeKasir(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'merchant_id' => 'required|exists:merchants,id',
            'nama_kasir' => 'required|string|max:255',
            'no_telepon' => 'required|string|max:20',
        ]);

        // Create user first
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password ?? 'password123'),
            'email_verified_at' => now(),
        ]);

        $user->assignRole('kasir');

        // Create kasir
        $kasir = Kasir::create([
            'user_id' => $user->id,
            'merchant_id' => $request->merchant_id,
            'nama_kasir' => $request->nama_kasir,
            'no_telepon' => $request->no_telepon,
        ]);

        return redirect()->route('admin.kasir.index')
            ->with('success', 'Kasir created successfully.');
    }

    // Mahasiswa Management
    public function mahasiswa()
    {
        $mahasiswa = Mahasiswa::with('user', 'wallet')->paginate(20);

        return Inertia::render('Admin/Mahasiswa/Index', [
            'mahasiswa' => $mahasiswa,
        ]);
    }

    public function createMahasiswa()
    {
        return Inertia::render('Admin/Mahasiswa/Create');
    }

    public function storeMahasiswa(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'nim' => 'required|string|max:20|unique:mahasiswa',
            'nama_lengkap' => 'required|string|max:255',
            'jurusan' => 'required|string|max:100',
            'angkatan' => 'required|integer|min:2000|max:' . (date('Y') + 10),
        ]);

        // Create user first
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password ?? 'password123'),
            'email_verified_at' => now(),
        ]);

        $user->assignRole('mahasiswa');

        // Create mahasiswa
        $mahasiswa = Mahasiswa::create([
            'user_id' => $user->id,
            'nim' => $request->nim,
            'nama_lengkap' => $request->nama_lengkap,
            'jurusan' => $request->jurusan,
            'angkatan' => $request->angkatan,
        ]);

        // Create wallet for mahasiswa
        Wallet::create([
            'mahasiswa_id' => $mahasiswa->id,
            'saldo' => 0,
        ]);

        return redirect()->route('admin.mahasiswa.index')
            ->with('success', 'Mahasiswa created successfully.');
    }
}
