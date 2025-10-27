<?php

namespace Src\Marketing\Application\Actions;

use Illuminate\Support\Str;
use Src\Marketing\Domain\DTOs\MarketingAssetData;
use Src\Marketing\Domain\Models\MarketingAsset;
use Src\Shared\Domain\Models\User;

class CreateMarketingAssetAction
{
    public function execute(User $user, MarketingAssetData $data): MarketingAsset
    {
        return MarketingAsset::create([
            'pharmacy_id' => $user->pharmacy_id,
            'user_id' => $user->id,
            'title' => $data->asset_title,
            'slug' => Str::slug($data->asset_title), // Will be made unique by an observer
            'type' => $data->type,
            'product_data' => $data->products,
            'package_price' => $data->package_price,
            'is_active' => false, // New assets require admin approval
        ]);
    }
}
