<?php

namespace App\Models;

use Database\Factories\RfqResponseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RfqResponse extends Model
{
    /** @use HasFactory<RfqResponseFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'rfq_id',
        'vendor_id',
        'price',
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
            'price' => 'decimal:2',
        ];
    }

    /**
     * Get the associated RFQ.
     */
    public function rfq(): BelongsTo
    {
        return $this->belongsTo(Rfq::class);
    }

    /**
     * Get the responding vendor.
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}
