<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionIncomeCategory extends Model
{
    protected $fillable = ['income_id', 'chart_of_account_id', 'amount'];

    public function income(): BelongsTo
    {
        return $this->belongsTo(TransactionIncome::class, 'income_id');
    }

    public function chartOfAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class);
    }
}
