<?php

namespace App\Models;

use App\RfqStatus;
use Database\Factories\RfqFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rfq extends Model
{
    /** @use HasFactory<RfqFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'description',
        'deadline',
        'created_by',
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
            'deadline' => 'datetime',
            'status' => RfqStatus::class,
        ];
    }

    /**
     * Get the creator of the RFQ.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get vendors assigned to this RFQ.
     */
    public function vendors(): BelongsToMany
    {
        return $this->belongsToMany(Vendor::class, 'rfq_vendor');
    }

    /**
     * Get responses submitted for this RFQ.
     */
    public function responses(): HasMany
    {
        return $this->hasMany(RfqResponse::class);
    }

    /**
     * Alias for RFQ responses.
     */
    public function rfqResponses(): HasMany
    {
        return $this->responses();
    }

    /**
     * Get purchase orders created from this RFQ.
     */
    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }
}
