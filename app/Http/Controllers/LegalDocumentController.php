<?php

namespace App\Http\Controllers;

use App\Models\LegalDocument;
use Illuminate\Contracts\View\View;

class LegalDocumentController extends Controller
{
    private const DOCUMENTS = [
        'aviso-legal' => 'Aviso legal',
        'privacidad' => 'Política de privacidad',
        'cookies' => 'Política de cookies',
        'compra-y-devoluciones' => 'Condiciones de compra y devoluciones',
        'envios' => 'Condiciones de envío',
    ];

    public function show(string $slug): View
    {
        abort_unless(array_key_exists($slug, self::DOCUMENTS), 404);
        $document = LegalDocument::query()->published()->where('slug', $slug)->first();
        $title = $document?->title ?? self::DOCUMENTS[$slug];

        return view('public.legal', [
            'document' => $document,
            'title' => $title,
            'isPublished' => $document !== null,
        ]);
    }
}
