<?php

namespace Src\Order\Application\Actions;

use FPDF;
use Src\Order\Domain\Models\Invoice;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadInvoiceAsFpdfAction
{
    public function execute(Invoice $invoice): StreamedResponse
    {
        // Define FPDF_FONTPATH to point to our storage directory
        if (! defined('FPDF_FONTPATH')) {
            define('FPDF_FONTPATH', storage_path('app/fpdf/font/'));
        }

        $pdf = new FPDF('P', 'mm', 'A4');
        $pdf->AddPage();

        // --- Header ---
        // Logo (assuming a logo exists at public/images/logo.jpg)
        // Note: FPDF requires a file path, not a URL.
        $logoPath = public_path('images/logo.jpg');
        if (file_exists($logoPath)) {
            $pdf->Image($logoPath, 10, 8, 30);
        }
        $pdf->SetFont('Arial', 'B', 20);
        $pdf->Cell(0, 10, 'INVOICE', 0, 1, 'R');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 5, 'Invoice #: '.$invoice->invoice_number, 0, 1, 'R');
        $pdf->Cell(0, 5, 'Date: '.$invoice->created_at->format('M d, Y'), 0, 1, 'R');
        $pdf->Ln(15);

        // --- From/To Info ---
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(95, 5, 'FROM:', 0, 0, 'L');
        $pdf->Cell(95, 5, 'BILL TO:', 0, 1, 'L');
        $pdf->SetFont('Arial', '', 9);
        // $pdf->Cell(95, 5, $invoice->pharmacy->name, 0, 0, 'L');
        // $pdf->Cell(95, 5, $invoice->patient->full_name, 0, 1, 'L');
        // $pdf->Cell(95, 5, $invoice->pharmacy->address, 0, 0, 'L');
        // $pdf->Cell(95, 5, $invoice->shipping_location_area ?? $invoice->patient->location_area, 0, 1, 'L');
        // $pdf->Cell(95, 5, $invoice->pharmacy->phone, 0, 0, 'L');
        // $pdf->Cell(95, 5, $invoice->shipping_phone ?? $invoice->patient->phone, 0, 1, 'L');
        // $pdf->Ln(10);
        $pdf->SetFont('Arial', '', 9);
        // "From" is now Careflux
        $pdf->Cell(95, 5, 'Careflux', 0, 0, 'L');
        $pdf->Cell(95, 5, $invoice->patient->full_name, 0, 1, 'L');

        // Add Careflux's address
        $pdf->Cell(95, 5, '26B Jasmine Ikota GRA, Lekki, Lagos', 0, 0, 'L');
        $pdf->Cell(95, 5, $invoice->shipping_location_area ?? $invoice->patient->location_area, 0, 1, 'L');

        // Add Careflux's contact
        $pdf->Cell(95, 5, 'support@careflux.com', 0, 0, 'L');
        $pdf->Cell(95, 5, $invoice->shipping_phone ?? $invoice->patient->phone ?? '', 0, 1, 'L');
        $pdf->Ln(5);

        // Add a new section for the fulfillment partner
        $pdf->SetFont('Arial', 'I', 8); // Italic and smaller font
        $pdf->SetTextColor(100, 100, 100);
        $pdf->Cell(0, 5, 'Fulfilled by Partner Pharmacy: '.$invoice->pharmacy->name, 0, 1, 'L');
        $pdf->SetTextColor(0, 0, 0); // Reset text color
        $pdf->Ln(5);

        // --- Items Table ---
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor(240, 240, 240);
        $pdf->Cell(110, 8, 'Description', 1, 0, 'L', true);
        $pdf->Cell(20, 8, 'Qty', 1, 0, 'C', true);
        $pdf->Cell(30, 8, 'Unit Price', 1, 0, 'R', true);
        $pdf->Cell(30, 8, 'Total', 1, 1, 'R', true);

        $pdf->SetFont('Arial', '', 9);
        foreach ($invoice->items as $item) {
            $pdf->Cell(110, 7, $item->description, 'LR');
            $pdf->Cell(20, 7, $item->quantity, 'R', 0, 'C');
            $pdf->Cell(30, 7, 'N'.number_format($item->price / 100, 2), 'R', 0, 'R');
            $pdf->Cell(30, 7, 'N'.number_format($item->total / 100, 2), 'R', 1, 'R');
        }
        $pdf->Cell(190, 0, '', 'T'); // Closing table line
        $pdf->Ln(5);

        // --- Totals ---
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(130, 6, '', 0, 0);
        $pdf->Cell(30, 6, 'Subtotal', 0, 0, 'R');
        $pdf->Cell(30, 6, 'N'.number_format($invoice->subtotal / 100, 2), 0, 1, 'R');
        $pdf->Cell(130, 6, '', 0, 0);
        $pdf->Cell(30, 6, 'Delivery Fee', 0, 0, 'R');
        $pdf->Cell(30, 6, 'N'.number_format($invoice->delivery_fee / 100, 2), 0, 1, 'R');

        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(130, 8, '', 0, 0);
        $pdf->Cell(30, 8, 'Total Due', 0, 0, 'R');
        $pdf->Cell(30, 8, 'N'.number_format($invoice->total / 100, 2), 0, 1, 'R');

        $pdfContent = $pdf->Output('S');
        $fileName = "Invoice-{$invoice->invoice_number}.pdf";

        return response()->streamDownload(fn () => print ($pdfContent), $fileName);
    }
}
