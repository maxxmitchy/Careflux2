<?php

namespace Src\Order\Application\Actions;

use App\Events\TransactionCompleted;
use App\Jobs\NotifyPharmacistOfNewOrderJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Src\Order\Domain\Models\Invoice;
use Src\Order\Domain\Models\PaymentAttempt;
use Throwable;

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
            return true; // Idempotency: Already processed successfully.
        }

        try {
            // --- REAL TRANSACTPAY API INTEGRATION ---
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->secretKey,
                'Accept' => 'application/json',
            ])->get("{$this->baseUrl}/api/v1/transaction/query", [
                'reference' => $attemptReference,
            ]);

            $response->throw(); // Throw an exception for 4xx/5xx responses
            $data = $response->json('data');
            // --- END OF API INTEGRATION ---

            if ($response->successful() && isset($data['status']) && $data['status'] === 'successful') {
                return $this->handleSuccessfulPayment($attempt, $parentTransaction, $data);
            }

            // If payment was not successful according to the API
            $this->handleFailedPayment($attempt, $parentTransaction, $data ?? ['message' => 'Payment not successful']);

            return false;

        } catch (Throwable $e) {
            Log::critical('Transactpay verification API call failed.', [
                'reference' => $attemptReference,
                'error' => $e->getMessage(),
            ]);
            $this->handleFailedPayment($attempt, $parentTransaction, ['error' => $e->getMessage()]);

            return false;
        }
    }

    private function handleSuccessfulPayment(PaymentAttempt $attempt, $parentTransaction, array $gatewayResponse): bool
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

            TransactionCompleted::dispatch($parentTransaction);

            // Dispatch jobs to notify pharmacists
            foreach (Invoice::findMany($invoiceIds) as $invoice) {
                NotifyPharmacistOfNewOrderJob::dispatch($invoice);
            }
        });

        return true;
    }

    private function handleFailedPayment(PaymentAttempt $attempt, $parentTransaction, array $gatewayResponse): void
    {
        $attempt->update(['status' => 'failed', 'gateway_response' => $gatewayResponse]);

        // Only mark the parent transaction as failed if there are no other successful attempts.
        if (! $parentTransaction->paymentAttempts()->where('status', 'successful')->exists()) {
            $parentTransaction->update(['status' => 'failed', 'failed_at' => now()]);
        }
    }
}
