<?php

namespace Src\Order\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Src\Patient\Domain\Models\Patient;
use Src\Pharmacy\Domain\Models\Pharmacy;
use Src\Shared\Domain\Models\User;

class Invoice extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['total' => 'integer', 'subtotal' => 'integer', 'delivery_fee' => 'integer'];

    /**
     * The patient this invoice was issued to.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * The pharmacist (user) who created this invoice.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The pharmacy this invoice belongs to.
     */
    public function pharmacy(): BelongsTo
    {
        return $this->belongsTo(Pharmacy::class);
    }

    /**
     * The individual line items on this invoice.
     */
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(OrderEvent::class)->latest();
    }

    /**
     * Get the parent transactions that include this invoice.
     * In our current logic, an invoice will only ever belong to one transaction.
     */
    public function transactions()
    {
        // This is a more complex relationship. We query the transactions table
        // where the metadata->invoice_ids array contains this invoice's ID.
        return $this->belongsToMany(Transaction::class, 'transaction_invoice_pivot_table_placeholder')
            ->using(json_contains('metadata->invoice_ids', $this->id));
        // A simpler, more direct query method is better here.
    }

    // public function transactions()
    // {
    //     return Transaction::whereJsonContains('metadata->invoice_ids', $this->id)->get();
    // }

    /**
     * A simple, direct method to find the parent pending transaction.
     */
    public function getPendingTransaction(): ?Transaction
    {
        return Transaction::where('status', 'pending')
            ->whereJsonContains('metadata->invoice_ids', $this->id)
            ->first();
    }
}
