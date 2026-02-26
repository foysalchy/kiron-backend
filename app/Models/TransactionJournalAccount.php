<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionJournalAccount extends Model
{
      protected $fillable = [
        'transaction_journal_id',
        'chart_of_account_id',
        'debit',
        'credit',
    ];
    public function journal(): BelongsTo
    {
        return $this->belongsTo(TransactionJournal::class, 'transaction_journal_id');
    }

    public function chartOfAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class);
    }
}
