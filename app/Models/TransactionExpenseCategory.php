<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionExpenseCategory extends Model
{
    protected $fillable = ['expense_id', 'chart_of_account_id', 'amount'];

    public function expense(): BelongsTo
    {
        return $this->belongsTo(TransactionExpense::class, 'expense_id');
    }

    public function chartOfAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class);
    }
}
