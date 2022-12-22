<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticker extends Model
{
    protected $fillable = [
        'ticker',
        'name',
        'price_per_share'
    ];
}
