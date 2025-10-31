<?php

namespace Src\Order\Application\Actions;

use App\Events\OrderCompleted;
use App\Events\TransactionCompleted;
use App\Jobs\NotifyPharmacistOfNewOrderJob;
use App\Mail\OrderConfirmationMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Src\Order\Domain\Models\Invoice;
use Src\Order\Domain\Models\PaymentAttempt; // <-- Import the Mailable
use Src\Order\Domain\Models\Transaction; // <-- Import the Mail facade
use Throwable; // <-- Import Transaction

class VerifyTransactionAction
{
    protected string $baseUrl;

    protected string $secretKey;

    public function __construct()
    {
        $this->baseUrl = config('services.transactpay.base_url');
        $this->secretKey = config('services.transactpay.secret_key');
    }

    public function execute(string $attemptReference): bool
    {
        $attempt = PaymentAttempt::where('reference', $attemptReference)->first();
        if (! $attempt) {
            Log::error('Payment attempt not found for verification.', ['reference' => $attemptReference]);

            return false;
        }

        $parentTransaction = $attempt->transaction;
        if ($parentTransaction->status === 'completed') {
            return true;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->secretKey,
                'Accept' => 'application/json',
            ])->get("{$this->baseUrl}/api/v1/transaction/query", [
                'reference' => $attemptReference,
            ]);

            $response->throw();
            $data = $response->json('data');

            if ($response->successful() && isset($data['status']) && $data['status'] === 'successful') {
                return $this->handleSuccessfulPayment($attempt, $parentTransaction, $data);
            }

            $this->handleFailedPayment($attempt, $parentTransaction, $data ?? ['message' => 'Payment not successful']);

            return false;
        } catch (Throwable $e) {
            Log::critical('Transactpay verification API call failed.', ['reference' => $attemptReference, 'error' => $e->getMessage()]);
            $this->handleFailedPayment($attempt, $parentTransaction, ['error' => $e->getMessage()]);

            return false;
        }
    }

    private function handleSuccessfulPayment(PaymentAttempt $attempt, Transaction $parentTransaction, array $gatewayResponse): bool
    {
        DB::transaction(function () use ($attempt, $parentTransaction, $gatewayResponse) {
            $attempt->update(['status' => 'successful', 'gateway_response' => $gatewayResponse]);
            $parentTransaction->update([
                'status' => 'completed',
                'processed_at' => now(),
                'gateway_response' => $gatewayResponse,
            ]);

            $invoiceIds = $parentTransaction->metadata['invoice_ids'] ?? [];
            Invoice::whereIn('id', $invoiceIds)->update(['status' => 'paid']);

            $invoices = Invoice::with(['pharmacy', 'patient', 'items'])->findMany($invoiceIds);

            if ($invoices->isNotEmpty()) {
                // 1. Send email confirmation to the patient.
                Mail::to($parentTransaction->user)->queue(new OrderConfirmationMail($parentTransaction->user, $invoices));

                // 2. Dispatch jobs to notify each relevant pharmacist.
                foreach ($invoices as $invoice) {
                    OrderCompleted::dispatch($invoice);

                    NotifyPharmacistOfNewOrderJob::dispatch($invoice);
                }
            }

            // Dispatch event for other listeners (e.g., coupon redemption, subscription activation).
            TransactionCompleted::dispatch($parentTransaction);
        });

        return true;
    }

    private function handleFailedPayment(PaymentAttempt $attempt, Transaction $parentTransaction, array $gatewayResponse): void
    {
        $attempt->update(['status' => 'failed', 'gateway_response' => $gatewayResponse]);
        if (! $parentTransaction->paymentAttempts()->where('status', 'successful')->exists()) {
            $parentTransaction->update(['status' => 'failed', 'failed_at' => now()]);
        }
    }
}
