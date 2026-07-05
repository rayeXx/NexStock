<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        return view('user.index', compact('users'));
    }

    public function create()
    {
        return view('user.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:staff_gudang,admin_gudang,owner',
        ]);

        if (auth()->user()->role === 'admin_gudang' && $request->role === 'owner') {
            abort(403, 'Akses Ditolak: Admin tidak diizinkan membuat user dengan role Owner.');
        }

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return redirect()->route('user.index')->with('success', 'User operator berhasil didaftarkan.');
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);

        if (auth()->user()->role === 'admin_gudang' && $user->role === 'owner') {
            abort(403, 'Akses Ditolak: Admin tidak diizinkan memodifikasi user dengan role Owner.');
        }

        return view('user.edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if (auth()->user()->role === 'admin_gudang') {
            if ($user->role === 'owner' || $request->role === 'owner') {
                abort(403, 'Akses Ditolak: Admin tidak diizinkan memodifikasi user dengan role Owner.');
            }
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|in:staff_gudang,admin_gudang,owner',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ];

        if ($request->filled('password')) {
            $request->validate([
                'password' => 'required|string|min:8|confirmed',
            ]);
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('user.index')->with('success', 'User berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if (auth()->user()->role === 'admin_gudang' && $user->role === 'owner') {
            abort(403, 'Akses Ditolak: Admin tidak diizinkan menghapus user dengan role Owner.');
        }

        if ($user->id === auth()->id()) {
            return redirect()->route('user.index')->with('error', 'Gagal: Anda tidak bisa menghapus akun Anda sendiri.');
        }

        $user->delete();
        return redirect()->route('user.index')->with('success', 'User berhasil dihapus.');
    }
}
