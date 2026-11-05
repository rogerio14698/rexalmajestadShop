<?php

namespace App\Http\Controllers;

use App\Models\Artwork;
use App\Models\Order;
use App\Support\CsvWriter;
use Illuminate\Http\Request;

class AdminExportController extends Controller
{
    public function orders(Request $request)
    {
        abort_unless($request->user()?->isAdmin() && $request->user()->hasVerifiedEmail(), 403);

        return response()->streamDownload(function (): void {
            $stream = fopen('php://output', 'w');
            CsvWriter::write($stream, ['Pedido', 'Correo', 'Estado', 'Moneda', 'Total céntimos', 'Fecha']);

            Order::query()->orderBy('id')->chunkById(500, function ($orders) use ($stream): void {
                foreach ($orders as $order) {
                    CsvWriter::write($stream, [
                        $order->number,
                        $order->email,
                        $order->status->value,
                        $order->currency,
                        $order->total_cents,
                        $order->created_at->toIso8601String(),
                    ]);
                }
            });

            fclose($stream);
        }, 'pedidos.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function artworks(Request $request)
    {
        abort_unless($request->user()?->isAdmin() && $request->user()->hasVerifiedEmail(), 403);

        return response()->streamDownload(function (): void {
            $stream = fopen('php://output', 'w');
            CsvWriter::write($stream, ['Título', 'URL', 'Categoría', 'Año', 'Precio céntimos', 'Stock', 'A la venta']);

            Artwork::query()->orderBy('id')->chunkById(500, function ($artworks) use ($stream): void {
                foreach ($artworks as $artwork) {
                    CsvWriter::write($stream, [
                        $artwork->title,
                        $artwork->slug,
                        $artwork->category?->slug,
                        $artwork->year,
                        $artwork->price_cents,
                        $artwork->stock,
                        $artwork->is_for_sale ? 'sí' : 'no',
                    ]);
                }
            });

            fclose($stream);
        }, 'obras.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
