<?php

namespace App\Http\Controllers;

use App\Models\Artwork;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function toggle(Request $request, Artwork $artwork): RedirectResponse
    {
        abort_unless($artwork->published_at !== null && $artwork->published_at->lessThanOrEqualTo(now()), 404);

        $favorite = $request->user()->favorites()->where('artwork_id', $artwork->getKey());

        if ($favorite->exists()) {
            $favorite->detach($artwork->getKey());
        } else {
            $favorite->syncWithoutDetaching([$artwork->getKey()]);
        }

        return back()->with('status', 'Favoritos actualizados.');
    }
}
