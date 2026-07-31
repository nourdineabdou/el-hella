<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

class DashboardController extends Controller
{
    /**
     * Redirect the authenticated user to the dashboard matching their role.
     */
    public function __invoke(): RedirectResponse
    {
        $user = auth()->user();

        return redirect()->route($user->isAdmin() ? 'admin.dashboard' : 'distributor.dashboard');
    }
}
