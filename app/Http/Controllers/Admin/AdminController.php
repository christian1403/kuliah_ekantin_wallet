<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserRequest;
use App\Http\Requests\Admin\UserUpdateRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    public function index()
    {
        $users = User::with('roles')
            ->paginate(20);

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
        ]);
    }

    public function create()
    {
        $roles = Role::all();

        return Inertia::render('Admin/Users/Create', [
            'roles' => $roles,
        ]);
    }

    public function store(UserRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'email_verified_at' => now(),
        ]);

        if ($request->role) {
            $user->assignRole($request->role);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    public function show(User $user)
    {
        $user->load('roles', 'permissions');

        return Inertia::render('Admin/Users/Show', [
            'user' => $user,
        ]);
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        $user->load('roles');

        return Inertia::render('Admin/Users/Edit', [
            'user' => $user,
            'roles' => $roles,
        ]);
    }

    public function update(UserUpdateRequest $request, User $user)
    {
        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        if ($request->password) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        if ($request->role) {
            $user->syncRoles($request->role);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }
}
