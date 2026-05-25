<?php

namespace App\Models;

use App\Enums\AccountBalanceMovementOperation;
use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountBalanceMovement extends Model
{
    protected $fillable = [
        'user_id',
        'account_id',
        'financial_transaction_id',
        'operation',
        'transaction_type',
        'amount',
        'impact_amount',
        'balance_before',
        'balance_after',
    ];

    protected $casts = [
        'operation' => AccountBalanceMovementOperation::class,
        'transaction_type' => TransactionType::class,
        'amount' => 'decimal:2',
        'impact_amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function financialTransaction(): BelongsTo
    {
        return $this->belongsTo(FinancialTransaction::class);
    }
}
