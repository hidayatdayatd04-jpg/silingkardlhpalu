<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AccessGateController extends Controller
{
    private string $accessCode = 'DLH-483921';

    public function show()
    {
        return view('public.access-gate');
    }

    public function verify(Request $request)
    {
        $request->validate([
            'access_code' => 'required|string',
        ]);

        if ($request->input('access_code') === $this->accessCode) {
            session(['site_access_granted' => true]);
            return redirect('/');
        }

        return back()->withErrors([
            'access_code' => 'Kode akses tidak valid. Silakan coba lagi.',
        ])->withInput();
    }

    public function logout()
    {
        session()->forget('site_access_granted');
        return redirect()->route('access-gate.show');
    }
}
