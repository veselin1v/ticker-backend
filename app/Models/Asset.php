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
        'positon_worth',
        'profit',
        'roi'
    ];

    public function shares()
    {
        return $this->hasMany(Share::class);
    }

    public function ticker()
    {
        return $this->hasOne(Ticker::class, 'id', 'ticker_id');
    }
}
