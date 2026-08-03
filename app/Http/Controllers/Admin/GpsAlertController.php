<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GpsAlert;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GpsAlertController extends Controller
{
    public function index(Request $request): View
    {
        $request->validate([
            'status' => ['nullable', 'in:pending,justified,rejected'],
        ]);

        $status = $request->input('status', 'pending');

        $query = GpsAlert::with(['visit', 'distributor.user', 'shop'])
            ->latest('created_at');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $alerts = $query->paginate(10)->withQueryString();

        return view('admin.gps-alerts.index', [
            'alerts' => $alerts,
            'status' => $status,
        ]);
    }

    public function review(Request $request, GpsAlert $gpsAlert): RedirectResponse
    {
        $validated = $request->validate([
            'decision' => ['required', 'in:justified,rejected'],
            'admin_comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $gpsAlert->update([
            'status' => $validated['decision'],
            'admin_comment' => $validated['admin_comment'] ?? null,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        return redirect()->route('admin.gps-alerts.index')->with(
            'success',
            $validated['decision'] === 'justified' ? __('admin.gps_alert_justified_success') : __('admin.gps_alert_rejected_success')
        );
    }
}
