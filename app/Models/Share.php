<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Share extends Model
{
    protected $fillable = [
        'asset_id',
        'trade',
        'quantity',
        'price_per_share',
        'total_price'
    ];
}
