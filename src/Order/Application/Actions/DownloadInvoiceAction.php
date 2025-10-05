<?php

namespace Src\Order\Application\Actions;

use Illuminate\Support\Facades\Vite;
use Spatie\Browsershot\Browsershot;
use Src\Order\Domain\Models\Invoice;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadInvoiceAction
{
    public function execute(Invoice $invoice): StreamedResponse
    {
        $html = view('pdf.invoice', ['invoice' => $invoice])->render();

        $fileName = "Invoice-{$invoice->invoice_number}.pdf";

        // Vite::asset gives us the raw content of the compiled CSS file.
        $cssContent = file_get_contents(Vite::asset('resources/css/app.css'));

        $fileContents = Browsershot::html($html)
            ->noSandbox() // Required for most server environments
            ->format('A4')
            ->showBackground()
            // The most reliable method is to inject the styles directly into the head.
            ->setExtraHttpHeaders(['Content-Type' => 'text/html; charset=utf-8'])
            ->newHeadless() // Use the new headless mode
            ->setNodeBinary(config('browsershot.node_binary', '/usr/bin/node')) // Use configurable paths
            ->setNpmBinary(config('browsershot.npm_binary', '/usr/bin/npm'))
            ->pdf([
                'extraHtmlHead' => "<style>{$cssContent}</style>",
            ]);

        return response()->streamDownload(fn () => print ($fileContents), $fileName);
    }
}
