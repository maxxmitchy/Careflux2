<?php

namespace Src\Order\Domain\Contracts;

use Illuminate\Support\Collection;
use Src\User\Domain\DTOs\CustomerDataDTO;
use Src\User\Domain\DTOs\ShippingDataDTO;

interface PlaceOrderActionInterface
{
    /**
     * @param  Collection  $cartItems  // Collection of CartItemDTOs
     * @return Collection // Collection of created Invoice models
     */
    public function execute(
        CustomerDataDTO $customerData,
        ?ShippingDataDTO $shippingData,
        Collection $cartItems
    ): Collection;
}
