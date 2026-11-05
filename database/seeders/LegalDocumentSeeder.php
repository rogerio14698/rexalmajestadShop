<?php

namespace Database\Seeders;

use App\Models\LegalDocument;
use Illuminate\Database\Seeder;

class LegalDocumentSeeder extends Seeder
{
    public function run(): void
    {
        $documents = [
            'aviso-legal' => [
                'title' => 'Aviso legal',
                'body' => "Contenido de muestra del aviso legal.\n\nTitular del sitio: Rexalmajestad. Sustituye este texto por el aviso legal definitivo (datos identificativos, condiciones de uso y responsabilidad).",
            ],
            'privacidad' => [
                'title' => 'Política de privacidad',
                'body' => "Contenido de muestra de la política de privacidad.\n\nDescribe aquí qué datos personales se recogen (cuenta, pedidos, formulario de contacto), con qué finalidad, su base legal y cómo ejercer los derechos de acceso, rectificación y supresión.",
            ],
            'cookies' => [
                'title' => 'Política de cookies',
                'body' => "Contenido de muestra de la política de cookies.\n\nEste sitio solo utiliza cookies técnicas necesarias para la sesión y el carrito. No se usan cookies de analítica ni publicidad.",
            ],
            'compra-y-devoluciones' => [
                'title' => 'Condiciones de compra y devoluciones',
                'body' => "Contenido de muestra de las condiciones de compra.\n\nDetalla el proceso de compra, los medios de pago aceptados (Stripe y PayPal), los plazos de desistimiento y el procedimiento de devolución de obra original.",
            ],
            'envios' => [
                'title' => 'Condiciones de envío',
                'body' => "Contenido de muestra de las condiciones de envío.\n\nIndica zonas de envío, plazos estimados, embalaje de obra original y qué piezas requieren presupuesto de transporte específico.",
            ],
        ];

        foreach ($documents as $slug => $document) {
            LegalDocument::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'title' => $document['title'],
                    'body' => $document['body'],
                    'published_at' => now(),
                ],
            );
        }
    }
}
