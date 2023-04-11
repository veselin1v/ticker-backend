<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    protected $fillable = [
        'portfolio_id',
        'ticker_id',
        'quantity',
        'invested',
        'average_price',
        'position_worth',
        'profit',
        'roi',
        'annual_dividend'
    ];

    public function trades()
    {
        return $this->hasMany(Trade::class)->orderByDesc('created_at');
    }

    public function ticker()
    {
        return $this->hasOne(Ticker::class, 'id', 'ticker_id');
    }
}
