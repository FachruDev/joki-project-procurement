<?php

namespace App\Models;

use Database\Factories\DeliveryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Delivery extends Model
{
    /** @use HasFactory<DeliveryFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'po_id',
        'received_date',
        'notes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'received_date' => 'datetime',
        ];
    }

    /**
     * Get the purchase order for this delivery.
     */
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'po_id');
    }
}
