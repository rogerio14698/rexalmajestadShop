<?php

namespace App\Http\Controllers;

use App\Models\PortfolioSection;
use Illuminate\Contracts\View\View;

/**
 * Página /bio (antigua /portfolio): biografía, statement y currículum de la artista
 * (editable desde el admin), con galerías y obras destacadas.
 */
class PortfolioController extends Controller
{
    public function __invoke(): View
    {
        $sections = PortfolioSection::query()
            ->published()
            ->with(['artwork.media', 'artwork.category', 'media'])
            ->orderBy('position')
            ->get();

        return view('public.portfolio', compact('sections'));
    }
}

