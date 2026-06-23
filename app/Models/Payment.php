<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'package_id',
        'stripe_invoice_id',
        'stripe_payment_intent_id',
        'amount',
        'currency',
        'status',
        'period_start',
        'period_end',
        'paid_at',
        'paid_to_id',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'datetime',
            'period_end' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function paidTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_to_id');
    }
}
