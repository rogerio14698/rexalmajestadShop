<?php

namespace App\Http\Controllers;

use App\Models\Exhibition;
use Illuminate\Contracts\View\View;

class ExhibitionController extends Controller
{
    public function index(): View
    {
        $exhibitions = Exhibition::query()->published()->orderByDesc('starts_at')->get();

        return view('public.exhibitions', compact('exhibitions'));
    }

    public function show(string $slug): View
    {
        $exhibition = Exhibition::query()->published()->where('slug', $slug)->firstOrFail();

        return view('public.exhibition', compact('exhibition'));
    }
}
