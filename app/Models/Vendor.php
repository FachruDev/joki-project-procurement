<?php

namespace App\Models;

use App\VendorStatus;
use Database\Factories\VendorFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vendor extends Model
{
    /** @use HasFactory<VendorFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'company_name',
        'address',
        'phone',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => VendorStatus::class,
        ];
    }

    /**
     * Get the user that owns the vendor profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the vendor documents.
     */
    public function vendorDocuments(): HasMany
    {
        return $this->hasMany(VendorDocument::class);
    }

    /**
     * Alias for vendor documents.
     */
    public function documents(): HasMany
    {
        return $this->vendorDocuments();
    }

    /**
     * Get the RFQ responses submitted by the vendor.
     */
    public function rfqResponses(): HasMany
    {
        return $this->hasMany(RfqResponse::class);
    }

    /**
     * Get RFQs assigned to this vendor.
     */
    public function rfqs(): BelongsToMany
    {
        return $this->belongsToMany(Rfq::class, 'rfq_vendor');
    }

    /**
     * Get purchase orders for this vendor.
     */
    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    /**
     * Get invoices for this vendor.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
