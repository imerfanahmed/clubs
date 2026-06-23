<?php

namespace App\Models;

use Database\Factories\CampaignPledgeItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CampaignPledgeItem extends Model
{
    /** @use HasFactory<CampaignPledgeItemFactory> */
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'name',
        'unit',
        'target_quantity',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'target_quantity' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function donations(): HasMany
    {
        return $this->hasMany(CampaignDonation::class, 'pledge_item_id');
    }

    public function achievedQuantity(): int
    {
        return (int) $this->donations()
            ->where('status', CampaignDonation::STATUS_COMPLETED)
            ->sum('pledge_quantity');
    }

    public function progressPercent(): int
    {
        if ($this->target_quantity <= 0) {
            return 0;
        }

        return (int) min(100, round(($this->achievedQuantity() / $this->target_quantity) * 100));
    }
}
