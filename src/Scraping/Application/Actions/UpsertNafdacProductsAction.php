<?php
namespace Src\Scraping\Application\Actions;
use App\Models\NafdacProduct;
use Illuminate\Support\Collection;
use Src\Shared\Domain\Contracts\UpsertActionInterface;

class UpsertNafdacProductsAction implements UpsertActionInterface
{
    public function execute(Collection $dtos): int
    {
        if ($dtos->isEmpty()) {
            return 0;
        }
        $values = $dtos->map(fn($dto) => $dto->toArray())->all();
        return NafdacProduct::upsert(
            $values,
            uniqueBy: ['nafdac_number'],
            update: ['name', 'manufacturer']
        );
    }
}