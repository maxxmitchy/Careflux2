<?php

namespace Src\Order\Application\Actions;

use App\Events\QuoteRequestSubmitted;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Src\Order\Domain\Models\QuoteRequest;
use Src\Patient\Domain\Models\Patient;
use Src\Scraping\Domain\Models\ScrapedProduct;
use Src\Shared\Domain\Models\User;

class SubmitQuoteRequestAction
{
    public function execute(?User $user, array $quoteData, Collection $quoteItems): QuoteRequest
    {
        return DB::transaction(function () use ($user, $quoteData, $quoteItems) {
            // Patient is nullable, only exists for authenticated users
            $patient = $user?->patientProfile;

            $quoteRequest = QuoteRequest::create([
                'user_id' => $user?->id,
                'patient_id' => $patient?->id,
                'patient_name' => $quoteData['quote_name'],
                'patient_phone' => $quoteData['quote_phone'],
                'patient_email' => $quoteData['quote_email'],
                'status' => 'pending',
            ]);

            foreach ($quoteItems as $item) {
                if (! isset($item->uniqueId)) {
                    continue;
                }

                [$type, $id] = explode('::', $item->uniqueId, 2);
                if ($type !== 'scraped') {
                    continue;
                }

                $productable = ScrapedProduct::find($id);
                if ($productable) {
                    $quoteRequest->items()->create([
                        'productable_id' => $productable->id,
                        'productable_type' => $productable->getMorphClass(),
                        'status' => 'pending',
                    ]);
                }
            }

            QuoteRequestSubmitted::dispatch($quoteRequest);

            return $quoteRequest;
        });
    }
}
