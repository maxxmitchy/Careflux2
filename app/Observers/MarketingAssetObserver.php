<?php

namespace App\Observers;

use Illuminate\Support\Str;
use Src\Marketing\Domain\Models\MarketingAsset;

class MarketingAssetObserver
{
    /**
     * Handle the MarketingAsset "creating" event.
     * This is fired just before a new asset is saved to the database.
     */
    public function creating(MarketingAsset $marketingAsset): void
    {
        // If a slug wasn't manually set, generate one from the title.
        if (empty($marketingAsset->slug)) {
            $marketingAsset->slug = Str::slug($marketingAsset->title);
        }

        $baseSlug = $marketingAsset->slug;
        $count = 1;

        // Ensure the final slug is 100% unique.
        while (MarketingAsset::where('slug', $marketingAsset->slug)->exists()) {
            $marketingAsset->slug = $baseSlug.'-'.++$count;
        }
    }
}
