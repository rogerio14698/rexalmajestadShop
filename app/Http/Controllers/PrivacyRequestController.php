<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PrivacyRequestController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', 'in:access,rectification,erasure'],
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        $request->user()->privacyRequests()->create($data + ['status' => 'pending']);

        return back()->with('status', 'Solicitud recibida. Revisaremos las obligaciones de conservación antes de responder.');
    }
}
