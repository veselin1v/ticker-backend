<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dividend extends Model
{
    protected $fillable = [
        'ticker_id',
        'ex_dividend_date',
        'pay_date',
        'cash_amount'
    ];
}
