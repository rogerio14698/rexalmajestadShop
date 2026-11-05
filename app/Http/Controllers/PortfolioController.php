<?php

namespace App\Http\Controllers;

use App\Models\PortfolioSection;
use Illuminate\Contracts\View\View;

/**
 * Página /portfolio: la historia de la artista contada en secciones
 * (editable desde el admin), con unas pocas obras destacadas.
 */
class PortfolioController extends Controller
{
    public function __invoke(): View
    {
        $sections = PortfolioSection::query()
            ->published()
            ->with(['artwork.media', 'artwork.category'])
            ->orderBy('position')
            ->get();

        return view('public.portfolio', compact('sections'));
    }
}
