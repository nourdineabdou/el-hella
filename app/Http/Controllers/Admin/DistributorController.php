<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Distributor;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DistributorController extends Controller
{
    public function index(): View
    {
        $distributors = Distributor::with('user')
            ->orderByDesc('id')
            ->paginate(10);

        return view('admin.distributors.index', [
            'distributors' => $distributors,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30', 'unique:users,phone', 'regex:/^[0-9+\s\-()]+$/'],
            'password' => ['required', 'string', 'min:4', 'confirmed'],
        ]);

        $email = $this->generateEmail($validated['phone']);

        DB::transaction(function () use ($validated, $email) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $email,
                'phone' => $validated['phone'],
                'password' => Hash::make($validated['password']),
                'role' => 'distributor',
                'language' => app()->getLocale(),
                'is_active' => true,
                'email_verified_at' => now(),
            ]);

            $user->assignRole('distributor');

            Distributor::create([
                'user_id' => $user->id,
                'code' => 'DIST-'.str_pad((string) $user->id, 3, '0', STR_PAD_LEFT),
                'phone' => $validated['phone'],
                'is_active' => true,
            ]);
        });

        return redirect()->route('admin.distributors.index')->with('success', __('admin.distributor_created'));
    }

    public function resetPassword(Request $request, Distributor $distributor): RedirectResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'string', 'min:4', 'confirmed'],
        ]);

        $distributor->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('admin.distributors.index')->with('success', __('admin.password_reset'));
    }

    public function toggleActive(Distributor $distributor): RedirectResponse
    {
        $newStatus = ! $distributor->is_active;

        $distributor->update(['is_active' => $newStatus]);
        $distributor->user()->update(['is_active' => $newStatus]);

        return redirect()->route('admin.distributors.index')->with(
            'success',
            $newStatus ? __('admin.distributor_activated') : __('admin.distributor_deactivated')
        );
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