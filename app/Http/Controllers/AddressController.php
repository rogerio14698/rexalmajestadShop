<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AddressController extends Controller
{
    public function index(Request $request): View
    {
        $addresses = $request->user()->addresses()->orderByDesc('is_default')->latest()->get();

        return view('users.addresses', compact('addresses'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['is_default'] = $request->boolean('is_default');
        $this->saveDefault($request, $data);
        $request->user()->addresses()->create($data);

        return back()->with('status', 'Dirección guardada.');
    }

    public function update(Request $request, Address $address): RedirectResponse
    {
        abort_unless($address->user_id === $request->user()->getKey(), 404);
        $data = $this->validated($request);
        $data['is_default'] = $request->boolean('is_default');
        $this->saveDefault($request, $data, $address->getKey());
        $address->update($data);

        return back()->with('status', 'Dirección actualizada.');
    }

    public function destroy(Request $request, Address $address): RedirectResponse
    {
        abort_unless($address->user_id === $request->user()->getKey(), 404);
        $address->delete();

        return back()->with('status', 'Dirección eliminada.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'type' => ['required', 'in:shipping,billing'],
            'recipient' => ['required', 'string', 'max:255'],
            'line_1' => ['required', 'string', 'max:255'],
            'line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:120'],
            'region_code' => ['required', 'string', 'max:32'],
            'postal_code' => ['required', 'string', 'max:20'],
            'country_code' => ['required', 'string', 'size:2'],
            'tax_id' => ['nullable', 'string', 'max:40'],
            'is_default' => ['nullable', 'boolean'],
        ]);
    }

    private function saveDefault(Request $request, array $data, ?int $except = null): void
    {
        if (! $data['is_default']) {
            return;
        }

        $query = $request->user()->addresses()->where('type', $data['type']);

        if ($except) {
            $query->whereKeyNot($except);
        }

        $query->update(['is_default' => false]);
    }
}
