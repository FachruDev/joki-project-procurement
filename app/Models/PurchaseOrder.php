<?php

namespace App\Models;

use App\PurchaseOrderStatus;
use Database\Factories\PurchaseOrderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PurchaseOrder extends Model
{
    /** @use HasFactory<PurchaseOrderFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'rfq_id',
        'vendor_id',
        'total_price',
        'status',
        'created_by',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => PurchaseOrderStatus::class,
            'total_price' => 'decimal:2',
        ];
    }

    /**
     * Get the linked RFQ.
     */
    public function rfq(): BelongsTo
    {
        return $this->belongsTo(Rfq::class);
    }

    /**
     * Get the selected vendor.
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Get the creator of the purchase order.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get line items for this purchase order.
     */
    public function items(): HasMany
    {
        return $this->hasMany(PoItem::class, 'po_id');
    }

    /**
     * Get goods receipt for this purchase order.
     */
    public function delivery(): HasOne
    {
        return $this->hasOne(Delivery::class, 'po_id');
    }

    /**
     * Get invoice for this purchase order.
     */
    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class, 'po_id');
    }
}
