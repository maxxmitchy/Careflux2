<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\DatabaseManager;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\SimpleExcel\SimpleExcelReader;
use Src\Pharmacovigilance\Domain\Models\MasterBatchList;
use Src\Pharmacovigilance\Domain\Models\MasterBatchListEntry;
use Throwable;

class ProcessMasterBatchListImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(
        public MasterBatchList $masterList,
        public string $filePath
    ) {}

    public function handle(DatabaseManager $db): void
    {
        $startTime = microtime(true);

        // Check file existence before starting
        if (! Storage::disk('local')->exists($this->filePath)) {
            Log::error('Master batch list file missing.', [
                'list_id' => $this->masterList->id,
                'file' => $this->filePath,
            ]);
            $this->masterList->update(['status' => 'failed']);

            return;
        }

        $this->masterList->update(['status' => 'processing']);
        $entries = [];
        $insertedCount = 0;
        $chunkSize = 500;
        $path = Storage::disk('local')->path($this->filePath);

        try {
            $rows = SimpleExcelReader::create($path)->getRows();

            $db->beginTransaction();

            foreach ($rows as $index => $row) {
                $batchNumber = trim($row['batch_number'] ?? '');
                $expiryDate = trim($row['expiry_date'] ?? '');

                // Validate presence
                if (empty($batchNumber) || empty($expiryDate)) {
                    Log::warning('Invalid or missing data in row — skipped.', [
                        'list_id' => $this->masterList->id,
                        'row_index' => $index,
                        'row_data' => $row,
                    ]);

                    continue;
                }

                try {
                    $parsedDate = Carbon::parse($expiryDate)->toDateString();
                } catch (Throwable $e) {
                    Log::warning('Invalid expiry_date format — skipped.', [
                        'list_id' => $this->masterList->id,
                        'row_index' => $index,
                        'raw_date' => $expiryDate,
                        'error' => $e->getMessage(),
                    ]);

                    continue;
                }

                $entries[] = [
                    'master_batch_list_id' => $this->masterList->id,
                    'batch_number' => $batchNumber,
                    'expiry_date' => $parsedDate,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                if (count($entries) >= $chunkSize) {
                    MasterBatchListEntry::insert($entries);
                    $insertedCount += count($entries);
                    $entries = [];
                }
            }

            // Insert leftovers
            if (! empty($entries)) {
                MasterBatchListEntry::insert($entries);
                $insertedCount += count($entries);
            }

            $db->commit();

            $this->masterList->update([
                'status' => 'active',
                'processed_at' => now(),
            ]);

            // Clean up file after success
            Storage::disk('local')->delete($this->filePath);

            Log::info('Master batch list import completed successfully.', [
                'list_id' => $this->masterList->id,
                'inserted_count' => $insertedCount,
                'duration_seconds' => round(microtime(true) - $startTime, 2),
            ]);
        } catch (Throwable $e) {
            $db->rollBack();

            // Update status once, even if job retried
            $this->masterList->update(['status' => 'failed']);

            // Attempt cleanup to avoid leftover corrupted files
            if (Storage::disk('local')->exists($this->filePath)) {
                Storage::disk('local')->delete($this->filePath);
            }

            Log::critical('Master batch list import failed.', [
                'list_id' => $this->masterList->id,
                'file' => $this->filePath,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Re-throw to allow retry
            throw $e;
        }
    }
}
