<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(): View
    {
        $admins = User::where('role', 'admin')
            ->orderByDesc('id')
            ->paginate(10);

        return view('admin.admins.index', [
            'admins' => $admins,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30', 'unique:users,phone', 'regex:/^[0-9+\s\-()]+$/'],
            'password' => ['required', 'string', 'min:4', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $this->generateEmail($validated['phone']),
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'role' => 'admin',
            'language' => app()->getLocale(),
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $user->assignRole('admin');

        return redirect()->route('admin.admins.index')->with('success', __('admin.admin_created'));
    }

    private function generateEmail(string $phone): string
    {
        $digits = preg_replace('/[^0-9]/', '', $phone);
        $email = $digits.'@elhella.local';

        if (User::where('email', $email)->exists()) {
            $email = $digits.'-'.Str::random(4).'@elhella.local';
        }

        return $email;
    }
}
