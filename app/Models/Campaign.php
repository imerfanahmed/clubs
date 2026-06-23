<?php

namespace App\Models;

use Database\Factories\CampaignFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Number;
use Illuminate\Support\Str;

class Campaign extends Model
{
    /** @use HasFactory<CampaignFactory> */
    use HasFactory;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'title',
        'slug',
        'summary',
        'description',
        'image_path',
        'goal_amount',
        'currency',
        'status',
        'starts_at',
        'ends_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'goal_amount' => 'integer',
            'starts_at' => 'date',
            'ends_at' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Campaign $campaign): void {
            if (empty($campaign->slug)) {
                $campaign->slug = static::uniqueSlug($campaign->title);
            }
        });
    }

    public static function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'campaign';
        $slug = $base;
        $suffix = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function pledgeItems(): HasMany
    {
        return $this->hasMany(CampaignPledgeItem::class)->orderBy('sort_order');
    }

    public function donations(): HasMany
    {
        return $this->hasMany(CampaignDonation::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function raisedAmount(): int
    {
        return (int) $this->donations()
            ->where('type', CampaignDonation::TYPE_MONEY)
            ->where('status', CampaignDonation::STATUS_COMPLETED)
            ->sum('amount');
    }

    public function goalFormatted(): string
    {
        return Number::currency($this->goal_amount / 100, $this->currency);
    }

    public function raisedFormatted(): string
    {
        return Number::currency($this->raisedAmount() / 100, $this->currency);
    }

    public function progressPercent(): int
    {
        if ($this->goal_amount <= 0) {
            return 0;
        }

        return (int) min(100, round(($this->raisedAmount() / $this->goal_amount) * 100));
    }

    public function hasPledgeItems(): bool
    {
        return $this->pledgeItems()->exists();
    }
}
