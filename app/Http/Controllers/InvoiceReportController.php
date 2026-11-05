<?php

namespace App\Http\Controllers;

use App\Services\InvoiceService;
use App\Support\CsvWriter;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class InvoiceReportController extends Controller
{
    public function __invoke(Request $request, InvoiceService $invoiceService)
    {
        abort_unless($request->user()?->isAdmin() && $request->user()->hasVerifiedEmail(), 403);
        $dates = $request->validate([
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after_or_equal:from'],
        ]);

        if (Carbon::parse($dates['from'])->diffInDays(Carbon::parse($dates['to'])) > 366) {
            throw ValidationException::withMessages(['to' => 'El informe no puede abarcar más de 367 días.']);
        }

        $rows = $invoiceService->vatReport($dates['from'], $dates['to']);

        return response()->streamDownload(function () use ($rows): void {
            $stream = fopen('php://output', 'w');
            CsvWriter::write($stream, ['País', 'Clase fiscal', 'Tipo (puntos base)', 'Base céntimos', 'IVA céntimos', 'Total céntimos']);

            foreach ($rows as $row) {
                CsvWriter::write($stream, [
                    $row['country'],
                    $row['tax_class'],
                    $row['rate_basis_points'],
                    $row['net_cents'],
                    $row['tax_cents'],
                    $row['total_cents'],
                ]);
            }

            fclose($stream);
        }, 'informe-iva.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
