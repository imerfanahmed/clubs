<?php

namespace App\Models;

use Database\Factories\CampaignDonationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Number;
use Illuminate\Support\Str;

class CampaignDonation extends Model
{
    /** @use HasFactory<CampaignDonationFactory> */
    use HasFactory;

    public const TYPE_MONEY = 'money';

    public const TYPE_PLEDGE = 'pledge';

    public const METHOD_CARD = 'card';

    public const METHOD_OFFLINE = 'offline';

    public const STATUS_PENDING = 'pending';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'campaign_id',
        'user_id',
        'type',
        'amount',
        'currency',
        'pledge_item_id',
        'pledge_quantity',
        'payment_method',
        'status',
        'reference',
        'donor_name',
        'donor_email',
        'donor_phone',
        'message',
        'is_anonymous',
        'stripe_session_id',
        'stripe_payment_intent_id',
        'paid_at',
        'approved_at',
        'approved_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'pledge_quantity' => 'integer',
            'is_anonymous' => 'boolean',
            'paid_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (CampaignDonation $donation): void {
            if (empty($donation->reference)) {
                $donation->reference = static::uniqueReference();
            }
        });
    }

    public static function uniqueReference(): string
    {
        do {
            $reference = 'DN-'.strtoupper(Str::random(8));
        } while (static::where('reference', $reference)->exists());

        return $reference;
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pledgeItem(): BelongsTo
    {
        return $this->belongsTo(CampaignPledgeItem::class, 'pledge_item_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function displayName(): string
    {
        if ($this->is_anonymous) {
            return 'Anonymous';
        }

        return $this->user?->name ?? $this->donor_name ?? 'Anonymous';
    }

    public function recipientEmail(): ?string
    {
        return $this->user?->email ?? $this->donor_email;
    }

    public function amountFormatted(): string
    {
        return Number::currency(($this->amount ?? 0) / 100, $this->currency);
    }
}
