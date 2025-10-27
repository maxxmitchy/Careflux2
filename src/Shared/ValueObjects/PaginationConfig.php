<?php

declare(strict_types=1);

namespace Src\Shared\ValueObjects;

use Illuminate\Support\Str;
use Src\Store\Domain\Models\Store;

final readonly class PaginationConfig
{
    public function __construct(
        public string $type,
        public string $keyword,
        public int $maxPages,
        public int $pageSize = 24
    ) {}

    public static function fromStore(Store $store): ?self
    {
        if (blank($store->paginate_type) || blank($store->paginate_keyword)) {
            return null;
        }

        return new self(
            type: $store->paginate_type,
            keyword: $store->paginate_keyword,
            maxPages: config('scraping.max_pagination_pages', 100),
            pageSize: (int) ($store->page_size ?? config('scraping.default_page_size', 24)),
        );
    }

    public function apply(string $baseUrl, int $page): string
    {
        $separator = Str::contains($baseUrl, '?') ? '&' : '?';

        if (Str::contains($baseUrl, 'indigo.ca')) {
            $offset = ($page - 1) * $this->pageSize;

            $queryString = http_build_query([
                'start' => $offset,
                'sz' => $this->pageSize,
                'page' => $page,
            ]);

            return "{$baseUrl}{$separator}{$queryString}";
        }

        // Handle Hale & Hearty (path-based search & pagination)
        if (Str::contains($baseUrl, 'haleandhearty.ng')) {
            // Example: https://haleandhearty.ng/store/soap
            $path = rtrim($baseUrl, '/');

            if ($page > 1) {
                $path .= "/page/{$page}";
            }

            return "{$path}/";
        }

        // Handle other path-based pagination sites
        if (Str::contains($baseUrl, [
            'teeka4.com',
            'mediglow.ng',
            'tosnigeria.com',
            'deoset.com',
            'mophethonline.com',
            'assetpharmacy.com',
            'unitedwellnesspharmacy.com',
            'medicvillepharmacy.com',
            'troopharm.com',
            'carefortepharm.com',
        ])) {
            [$path, $query] = array_pad(explode('?', $baseUrl, 2), 2, null);

            $extraParams = ['post_type' => 'product'];
            if (Str::contains($baseUrl, 'teeka4.com')) {
                $extraParams['products-per-page'] = $this->pageSize;
            }
            $extraQuery = http_build_query($extraParams);

            $finalQuery = $query ? "{$query}&{$extraQuery}" : $extraQuery;

            if ($page > 1) {
                $path = rtrim($path, '/')."/page/{$page}/";
            }

            return "{$path}?{$finalQuery}";
        }

        return "{$baseUrl}{$separator}{$this->keyword}={$page}";
    }
}
