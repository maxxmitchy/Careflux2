<?php

namespace App\Livewire;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Src\Medication\Domain\Models\MedicationVariant;
use Src\Order\Domain\Contracts\CartServiceInterface;
use Src\Pharmacy\Application\Actions\ConfirmVerificationAction;
use Src\Pharmacy\Application\Actions\InitiateVerificationAction;
use Src\Pharmacy\Domain\Models\PharmacyProduct;
use Src\Pharmacy\Domain\Models\PrescriptionVerification;

#[Layout('components.layouts.guest')]
class PrescriptionVerificationPage extends Component
{
    public PharmacyProduct $product;

    public ?MedicationVariant $selectedVariant = null;

    public ?int $selectedVariantId = null;

    public string $step = 'select_options';

    public ?PrescriptionVerification $verification = null;

    public string $finalCodeInput = '';

    public function mount(PharmacyProduct $pharmacyProduct)
    {
        // --- THIS IS THE DEFINITIVE FIX ---
        // 1. Eager-load the entire relationship chain in one efficient query.
        $this->product = $pharmacyProduct->load(['medicationVariant.medication', 'pharmacy.users']);

        // 2. Access the relationship correctly through the variant.
        if (! $this->product->medicationVariant->medication->is_prescription) {
            // This is not a prescription product, redirect to the standard detail page.
            return redirect()->route('public.product.detail', ['uniqueId' => 'pharmacy::'.$this->product->id]);
        }

        $this->selectedVariantId = $this->product->medication_variant_id;
        $this->updateSelectedVariant();
    }

    #[Computed]
    public function allVariantsForThisMedication(): Collection
    {
        $parentMedicationId = $this->product->medicationVariant->medication_id;
        $variantIds = MedicationVariant::where('medication_id', $parentMedicationId)->pluck('id');

        return PharmacyProduct::where('pharmacy_id', $this->product->pharmacy_id)
            ->whereIn('medication_variant_id', $variantIds)
            ->with('medicationVariant')
            ->get();
    }

    public function updatedSelectedVariantId(): void
    {
        $this->updateSelectedVariant();
    }

    private function updateSelectedVariant(): void
    {
        $this->selectedVariant = MedicationVariant::find($this->selectedVariantId);
        if ($this->selectedVariant) {
            $this->product = $this->allVariantsForThisMedication()->firstWhere('medication_variant_id', $this->selectedVariantId);
        }
    }

    public function startVerification(InitiateVerificationAction $action)
    {
        // --- THIS IS THE DEFINITIVE FIX ---
        $user = Auth::user();

        // Guard Clause 1: Check for authenticated users with the WRONG role first.
        if ($user && ($user->is_pharmacist || $user->is_technician)) {
            $this->dispatch('toast',
                type: 'error',
                message: 'This action is for patients only. Please log in with a patient account.'
            );

            return;
        }

        // Guard Clause 2: Check for guests or users without a patient profile.
        if (! $user || ! $user->patientProfile) {
            session()->flash('info_message', 'Please log in or create an account to verify a prescription.');

            return redirect(route('filament.patient.auth.login'));
        }

        // Guard Clause 3: Check that a variant has been selected.
        if (! $this->product) {
            $this->dispatch('toast', type: 'error', message: 'Please select a product variant to continue.');

            return;
        }

        $this->verification = $action->execute(Auth::user()->patientProfile, $this->product);
        $this->step = 'contact_pharmacist';
    }

    public function proceedToCodeEntry(): void
    {
        $this->step = 'enter_code';
    }

    public function confirmCode(ConfirmVerificationAction $action, CartServiceInterface $cartService)
    {
        $this->validate(['finalCodeInput' => 'required|string|min:4']);
        try {
            if ($action->execute($this->verification, $this->finalCodeInput)) {
                $cartItemData = [
                    'uniqueId' => 'pharmacy::'.$this->product->id,
                    'type' => 'pharmacy',
                    'productName' => $this->product->name,
                    'sourceName' => $this->product->pharmacy->name,
                    'imageUrl' => $this->product->image,
                    'price' => $this->product->price,
                    'pharmacyId' => $this->product->pharmacy_id,
                    'pharmacistId' => $this->product->pharmacy->users->first()?->id,
                    'isPrescription' => true,
                    'verificationId' => $this->verification->id,
                ];
                $cartService->add($cartItemData, 'ready_to_pay');
                $this->verification->update(['status' => 'completed']);
                $this->dispatch('cart-updated');
                $this->step = 'success';
            } else {
                $this->addError('finalCodeInput', 'The verification code is invalid.');
            }
        } catch (\InvalidArgumentException $e) {
            $this->addError('finalCodeInput', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.prescription-verification-page');
    }
}
