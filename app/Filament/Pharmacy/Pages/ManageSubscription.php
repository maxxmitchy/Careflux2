<?php

namespace App\Filament\Pharmacy\Pages;

use BackedEnum;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Src\Order\Application\Actions\VerifyTransactionAction;
use Src\Subscription\Application\Actions\LogTransactionAction;
use Src\Subscription\Domain\Models\Plan;
use UnitEnum;

class ManageSubscription extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-credit-card';

    protected string $view = 'filament.pharmacy.pages.manage-subscription';

    protected static ?int $navigationSort = 3; // Place it lower in the sidebar

    protected static string|UnitEnum|null $navigationGroup = 'Management & Reporting';

    public Collection $plans;

    public ?\Src\Subscription\Domain\Models\Subscription $currentSubscription;

    public bool $isOnTrial = false;

    public bool $isExpired = false;

    public function mount(): void
    {
        $this->plans = Plan::where('user_type', 'pharmacy')
            ->where('is_active', true)
            ->where('price_monthly', '>', 0) // Don't show trial plans as a purchase option
            ->get();

        $this->currentSubscription = Filament::auth()->user()->pharmacy?->subscription;

        if ($this->currentSubscription) {
            $this->isOnTrial = $this->currentSubscription->payment_gateway === 'trial';
            $this->isExpired = ! $this->currentSubscription->isActive();
        }
    }

    public function beginPayment(int $planId, LogTransactionAction $logAction): void
    {
        $user = Filament::auth()->user();
        $pharmacy = $user->pharmacy;
        $plan = Plan::find($planId);
        if (! $user || ! $plan) {
            return;
        }

        $transaction = $logAction->execute(
            actingUser: $user,
            customer: $pharmacy,
            transactionable: $plan,
            amountInKobo: $plan->price_monthly,
            gateway: 'transactpay'
        );

        $checkoutData = [
            'firstName' => $user->name,
            'lastName' => '',
            'email' => $user->email,
            'currency' => 'NGN',
            'amount' => $plan->price_monthly / 100, // Convert from kobo to Naira
            'mobile' => $user->phone ?? '',
            'reference' => $transaction->reference,
            'description' => "Subscription to {$plan->name} plan",
            'apiKey' => config('services.transactpay.public_key'),
        ];

        $this->dispatch('start-checkout', data: $checkoutData);
    }

    public function handlePaymentCallback(string $reference, VerifyTransactionAction $verifyAction): void
    {
        if ($verifyAction->execute($reference)) {
            Notification::make()->title('Payment Successful!')->body('Your subscription has been activated.')->success()->send();
            $this->redirect(static::getUrl());
        } else {
            Notification::make()->title('Payment Verification Failed')->body('Please contact support if you were debited.')->danger()->send();
        }
    }
}
