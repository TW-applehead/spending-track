<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'amount',
        'payer_id',
        'consumer_id',
        'account_id',
        'is_expense',
        'other_account',
        'split_group_id',
        'expense_time',
        'notes',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
