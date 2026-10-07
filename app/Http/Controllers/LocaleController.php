<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LocaleController extends Controller
{
    /** Switch interface language (en|sw) and persist the preference. */
    public function switch(Request $request, string $locale)
    {
        abort_unless(in_array($locale, ['en', 'sw'], true), 404);

        $request->session()->put('locale', $locale);

        if ($request->user()) {
            $request->user()->forceFill(['locale' => $locale])->saveQuietly();
        }

        app()->setLocale($locale);

        return back()->with('status', __('common.language_switched'));
    }
}
