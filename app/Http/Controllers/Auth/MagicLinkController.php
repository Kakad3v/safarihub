<?php

namespace App\Http\Controllers\Auth;

use App\Services\LoginCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class MagicLinkController
{
    public function __invoke(string $token, LoginCodeService $codes): RedirectResponse
    {
        try {
            $user = $codes->verifyLink($token);
        } catch (ValidationException $e) {
            return redirect()->route('login')->with('link_error', $e->errors()['link'][0]);
        }

        Auth::login($user, remember: true);
        
        session()->regenerate();

        return redirect()->route('login');
    }
}
