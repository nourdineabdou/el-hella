<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    /**
     * Switch the application language and remember the choice.
     */
    public function switch(Request $request, string $locale): RedirectResponse
    {
        if (! in_array($locale, ['ar', 'fr'], true)) {
            abort(404);
        }

        $request->session()->put('locale', $locale);

        if ($request->user()) {
            $request->user()->update(['language' => $locale]);
        }

        return redirect()->back();
    }
}
