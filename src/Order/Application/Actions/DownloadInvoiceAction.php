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
        $paymentDetails = "Please make payment to:\nCareflux LCO\nProvidus - 9648958313";
        // --- END OF FIX ---

        $html = view('pdf.invoice', [
            'invoice' => $invoice,
            'paymentDetails' => $paymentDetails,
        ])->render();

        $fileName = "Invoice-{$invoice->invoice_number}.pdf";

        $nodePath = '/home/ubuntu/.config/nvm/versions/node/v22.18.0/bin/node';
        $npmPath = '/home/ubuntu/.config/nvm/versions/node/v22.18.0/bin/npm';

        $fileContents = Browsershot::html($html)
            ->setNodeBinary($nodePath) // Explicitly set the Node binary path
            ->setNpmBinary($npmPath)   // Explicitly set the NPM binary path
            ->noSandbox()
            ->format('A4')
            ->printBackground()
            // 2. We inject the styles directly. This is the most reliable method.
            ->setExtraHttpHeaders(['Content-Type' => 'text/html; charset=utf-8'])
            ->pdf([
                'extraHtmlHead' => '<style>'.Vite::asset('resources/css/app.css').'</style>',
            ]);

        return response()->streamDownload(fn () => print ($fileContents), $fileName);
    }
}
