<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    // 指定可以批量賦值的欄位
    protected $fillable = [
        'amount',
        'account_id',
        'type',
        'is_other_account',
    ];

    // 定義和 Account 模型的關聯（假設你有一個 Account 模型）
    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
